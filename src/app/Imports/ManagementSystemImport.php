<?php

namespace App\Imports;

use App\Exceptions\ExcelValidation;
use App\Mail\NewUserRegister;
use App\Models\Role;
use App\Models\Folder;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\DivisionRepository;
use App\Repositories\OfficeRepository;
use App\Repositories\UserRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\PositionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\FolderRepository;
use App\Helper\Constant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ManagementSystemImport implements ToCollection, WithStartRow
{
    public $response;
    public function startRow(): int
    {
        return 2;
    }

    private static function customerMessage()
    {
        return [
            'branch_name.required_with_all' => __('message.imports.branches.name.required_with_all'),
            'branch_name.max' => __('message.imports.branches.name.max'),
            'branch_name.required' => __('message.imports.branches.name.required'),
            'branch_code.required_with_all' => __('message.imports.branches.code.required_with_all'),
            'branch_code.max' => __('message.imports.branches.code.max'),
            'branch_code.required' => __('message.imports.branches.code.required'),
            'division_name.required_with_all' => __('message.imports.divisions.name.required_with_all'),
            'division_name.max' => __('message.imports.divisions.name.max'),
            'division_name.required' => __('message.imports.divisions.name.required'),
            'division_code.required_with_all' => __('message.imports.divisions.code.required_with_all'),
            'division_code.max' => __('message.imports.divisions.code.max'),
            'division_code.required' => __('message.imports.divisions.code.required'),
            'office_name.required_with_all' => __('message.imports.offices.name.required_with_all'),
            'office_name.max' => __('message.imports.offices.name.max'),
            'office_name.required' => __('message.imports.offices.name.required'),
            'office_code.required_with_all' => __('message.imports.offices.code.required_with_all'),
            'office_code.max' => __('message.imports.offices.code.max'),
            'office_code.required' => __('message.imports.offices.code.required'),
            'office_email.required_with_all' => __('message.imports.offices.email.required_with_all'),
            'office_email.max' => __('message.imports.offices.email.max'),
            'office_email.email' => __('message.imports.offices.email.email'),
            'office_email.required' => __('message.imports.offices.email.required'),
            'employee_id.required' => __('message.imports.users.employee_id.required'),
            'employee_id.max' => __('message.imports.users.employee_id.max'),
            'name.required' => __('message.imports.users.name.required'),
            'name.max' => __('message.imports.users.name.max'),
            'email.required' => __('message.imports.users.email.required'),
            'email.max' => __('message.imports.users.email.max'),
            'email.email' => __('message.imports.users.email.email'),
            'position_name.required_with' => __('message.imports.positions.name.required_with'),
            'position_name.max' => __('message.imports.positions.name.max'),
            'position_name.required' => __('message.imports.positions.name.required'),
            'position_code.required_with' => __('message.imports.positions.code.required_with'),
            'position_code.max' => __('message.imports.positions.code.max'),
            'position_code.required' => __('message.imports.positions.code.required'),
            'position_code.exists' => __('message.imports.positions.code.exists'),
            'full_name.max' => __('message.imports.profiles.full_name.max'),
            'katakana_name.max' => __('message.imports.profiles.katakana_name.max'),
            'phone.max' => __('message.imports.profiles.phone.max'),
            'phone.regex' => __('message.imports.profiles.phone.numeric'),
        ];
    }

    private function validateExistUser($row)
    {
        return Validator::make($row, [
            'branch_name' => 'required_with_all:branch_code|max:50',
            'branch_code' => 'required_with_all:branch_name|max:20',
            'division_name' => 'required_with_all:division_code|max:50',
            'division_code' => 'required_with_all:division_name|max:20',
            'office_name' => 'required_with_all:office_code,office_email|max:50',
            'office_code' => 'required_with_all:office_name,office_email|max:20',
            'office_email' => 'required_with_all:office_code,office_name|email|max:255',
            'employee_id' => 'required|max:20',
            'email' => 'nullable|email|max:255',
            'name' => 'nullable|max:50',
            'full_name' => 'nullable|max:50',
            'katakana_name' => 'nullable|max:50',
            'position_name' => 'required_with:position_code|max:50',
            'position_code' => 'required_with:position_name|max:20|exists:positions,code',
            'phone' => 'nullable|regex:/^[0-9]+$/|max:13',
        ], $this->customerMessage());
    }

    private function validateNotExistUser($row)
    {
        return Validator::make($row, [
            'branch_name' => 'required|max:50',
            'branch_code' => 'required|max:20',
            'division_name' => 'required|max:50',
            'division_code' => 'required|max:20',
            'office_name' => 'required|max:50',
            'office_code' => 'required|max:20',
            'office_email' => 'required|email|max:255',
            'employee_id' => 'required|max:20',
            'email' => 'required|email|max:255',
            'name' => 'required|max:50',
            'full_name' => 'nullable|max:50',
            'katakana_name' => 'nullable|max:50',
            'position_name' => 'required|max:50',
            'position_code' => 'required|max:20|exists:positions,code',
            'phone' => 'nullable|regex:/^[0-9]+$/|max:13',
        ], $this->customerMessage());
    }

    private function convertArrayKey($row)
    {
        $headers = Config::get('constants.import_headers');
        foreach ($headers as $key => $header) {
            if (array_key_exists($key, $row)) {
                $row[$header] = multibyteTrim($row[$key]);
                unset($row[$key]);
            }
        }
        return $row;
    }

    private function folder($branchRepository, $divisionRepository, $officeRepository, $folderRepository)
    {
        //get folder
        $folderBranch = $folderRepository->getFolderByBranch();
        $folderDivision = $folderRepository->getFolderByDivision();
        $folderOffice = $folderRepository->getFolderByOffice();

        $branchAll = $branchRepository->getAll();
        $divisionAll = $divisionRepository->getAll();
        $officeAll = $officeRepository->getAll();

        //insert or update folder branch
        $folderInfos = [];
        foreach ($branchAll as $item) {
            if (isset($folderBranch[$item->id])) {
                $folderInfos['update'][$folderBranch[$item->id]] = [
                    'name' => $item->name,
                    'is_system' => Folder::IS_SYSTEM,
                ];
            } else {
                $folderInfos['insert'][] = [
                    'branch_id' => $item->id,
                    'name' => $item->name,
                    'owner_id' => auth()->user()->id,
                    'is_system' => Folder::IS_SYSTEM,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }
        if (isset($folderInfos['update'])) {
            foreach ($folderInfos['update'] as $key => $item) {
                $folderRepository->update($key, $item);
            }
        }
        if (isset($folderInfos['insert'])) {
            $folderRepository->insertMany($folderInfos['insert']);
        }
        $folderBranch = $folderRepository->getFolderByBranch();

        //insert or update folder division
        $folderInfos = [];
        foreach ($divisionAll as $item) {
            if (isset($folderDivision[$item->id])) {
                $folderInfos['update'][$folderDivision[$item->id]] = [
                    'name' => $item->name,
                    'is_system' => Folder::IS_SYSTEM,
                    'parent_id' => $folderBranch[$item->branch_id]
                ];
            } else {
                $folderInfos['insert'][] = [
                    'division_id' => $item->id,
                    'name' => $item->name,
                    'owner_id' => auth()->user()->id,
                    'parent_id' => $folderBranch[$item->branch_id],
                    'is_system' => Folder::IS_SYSTEM,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }
        if (isset($folderInfos['update'])) {
            foreach ($folderInfos['update'] as $key => $item) {
                $folderRepository->update($key, $item);
            }
        }
        if (isset($folderInfos['insert'])) {
            $folderRepository->insertMany($folderInfos['insert']);
        }
        $folderDivision = $folderRepository->getFolderByDivision();

        //insert or update folder office
        $folderInfos = [];
        foreach ($officeAll as $item) {
            if (isset($folderOffice[$item->id])) {
                $folderInfos['update'][$folderOffice[$item->id]] = [
                    'name' => $item->name,
                    'is_system' => Folder::IS_SYSTEM,
                    'parent_id' => $folderDivision[$item->division_id]
                ];
            } else {
                $folderInfos['insert'][] = [
                    'office_id' => $item->id,
                    'name' => $item->name,
                    'owner_id' => auth()->user()->id,
                    'parent_id' => $folderDivision[$item->division_id],
                    'is_system' => Folder::IS_SYSTEM,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }
        if (isset($folderInfos['update'])) {
            foreach ($folderInfos['update'] as $key => $item) {
                $folderRepository->update($key, $item);
            }
        }
        if (isset($folderInfos['insert'])) {
            $folderRepository->insertMany($folderInfos['insert']);
        }

        return true;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        $rowsArr = $rows->toArray();
        //validation file
        if (empty($rowsArr)) {
            $errors = [
                'file' => [
                    __('message.imports.file_empty')
                ]
            ];
            throw new ExcelValidation(__('message.imports.data_invalid'), 422, null, $errors);
        }
        if (count($rowsArr) > Constant::ROW_OF_IMPORT) {
            $errors = [
                'file' => [
                    __('message.imports.max_row_import', ['max_row' => Constant::ROW_OF_IMPORT])
                ]
            ];
            throw new ExcelValidation(__('message.imports.data_invalid'), 422, null, $errors);
        }
        if (count($rowsArr[0]) < Constant::COLUMN_IMPORT) {
            $errors = [
                'file' => [
                    __('message.imports.min_column_import')
                ]
            ];
            throw new ExcelValidation(__('message.imports.data_invalid'), 422, null, $errors);
        }

        // init repository
        $branchRepository = new BranchRepository();
        $divisionRepository = new DivisionRepository();
        $officeRepository = new OfficeRepository();
        $userRepository = new UserRepository();
        $profileRepository = new ProfileRepository();
        $positionRepository = new PositionRepository();
        $roleRepository = new RoleRepository();
        $folderRepository = new FolderRepository();

        //get info in database
        $branchs = $branchRepository->getCode();
        $divisions = $divisionRepository->getCode();
        $offices = $officeRepository->getCode();
        $roles = $roleRepository->getAllRole();
        $employeeIDs = $userRepository->getEmployeeID();
        $positions = $positionRepository->getCode();
        $profiles = $profileRepository->getAllProfile();
        $userSystemAdmin = $userRepository->getUserSystemAdmin();

        $notifyUsers = $allEmployeeIdImport = [];
        $branchInfos = $officeInfos = $divisionInfos = $employeeInfos = $positionInfos = $profileInfos = [
            'update' => [],
            'insert' => []
        ];
        foreach ($rowsArr as $key => $item) {
            $item = array_map('trim', $item);
            //validation
            $rowConvert = $this->convertArrayKey($item);
            if (!$rowConvert['employee_id']) {
                $allEmployeeIdImport[] = $rowConvert['employee_id'];
            }
            if (in_array($rowConvert['employee_id'], $employeeIDs)) {
                $validation = $this->validateExistUser($rowConvert);
            } else {
                $validation = $this->validateNotExistUser($rowConvert);
            }

            if ($validation->fails()) {
                $this->response[($key + 2)] = $validation->messages()->get('*');
                continue;
            }

            if (in_array($rowConvert['employee_id'], $userSystemAdmin)) {
                $this->response[($key + 2)] = [
                    'employee_id' => [
                        __('message.imports.permission_edit_account')
                    ]
                ];
                continue;
            }

            $this->response[($key + 2)] = __('message.imports.import_success');
            //branch info
            if ($rowConvert['branch_code']) {
                $branchData = [
                    'name' => $rowConvert['branch_name'],
                ];
                if (isset($branchs[$rowConvert['branch_code']])) {
                    $branchInfos['update'][$branchs[$rowConvert['branch_code']]] = $branchData;
                } else {
                    $branchKey = array_search(
                        $rowConvert['branch_code'],
                        array_column($branchInfos['insert'], 'code')
                    );
                    if ($branchKey !== false) {
                        $branchInfos['insert'][$branchKey]['name'] = $rowConvert['branch_name'];
                    } else {
                        $branchData['code'] = $rowConvert['branch_code'];
                        $branchData['created_at'] = Carbon::now();
                        $branchData['updated_at'] = Carbon::now();
                        $branchInfos['insert'][] = $branchData;
                    }
                }
            }

            //division info
            if ($rowConvert['division_code']) {
                $divisionData = [
                    'name' => $rowConvert['division_name']
                ];
                if ($rowConvert['branch_code']) {
                    $divisionData['branch_id'] = $rowConvert['branch_code'];
                }

                if (isset($divisions[$rowConvert['division_code']])) {
                    $divisionInfos['update'][$divisions[$rowConvert['division_code']]] = $divisionData;
                } else {
                    $divisionKey = array_search(
                        $rowConvert['division_code'],
                        array_column($divisionInfos['insert'], 'code')
                    );
                    if ($divisionKey !== false) {
                        $divisionInfos['insert'][$divisionKey]['name'] = $rowConvert['division_name'];
                    } else {
                        $divisionData['code'] = $rowConvert['division_code'];
                        $divisionData['created_at'] = Carbon::now();
                        $divisionData['updated_at'] = Carbon::now();
                        $divisionInfos['insert'][] = $divisionData;
                    }
                }
            }

            //office info
            if ($rowConvert['office_code']) {
                $officeData = [
                    'name' => $rowConvert['office_name'],
                ];
                if ($rowConvert['division_code']) {
                    $officeData['division_id'] = $rowConvert['division_code'];
                }
                if ($rowConvert['office_email']) {
                    $officeData['email'] = $rowConvert['office_email'];
                }

                if (isset($offices[$rowConvert['office_code']])) {
                    $officeInfos['update'][$offices[$rowConvert['office_code']]] = $officeData;
                } else {
                    $officeKey = array_search(
                        $rowConvert['office_code'],
                        array_column($officeInfos['insert'], 'code')
                    );
                    if ($officeKey !== false) {
                        $officeInfos['insert'][$officeKey]['name'] = $rowConvert['office_name'];
                        $officeInfos['insert'][$officeKey]['email'] = $rowConvert['office_email'];
                    } else {
                        $officeData['code'] = $rowConvert['office_code'];
                        $officeData['created_at'] = Carbon::now();
                        $officeData['updated_at'] = Carbon::now();
                        $officeInfos['insert'][] = $officeData;
                    }
                }
            }

            if ($rowConvert['position_code']) {
                if (isset($positions[$rowConvert['position_code']])) {
                    $positionID = $positions[$rowConvert['position_code']];
                    $positionID = explode('__', $positionID)[0];
                    $positionInfos['update'][$positionID] = [
                        'name' => $rowConvert['position_name']
                    ];
                }
            }

            //user info
            $allEmployeeIdImport[] = $rowConvert['employee_id'];
            $employeeData = [
                'email' => $rowConvert['email'] ?? '',
                'name' => $rowConvert['name'] ?? '',
                'office_id' => $rowConvert['office_code'] ?? '',
            ];
            if ($rowConvert['position_code'] && isset($positions[$rowConvert['position_code']])) {
                $positionInfo = explode('__', $positions[$rowConvert['position_code']]);
                $positionID = $positionInfo[0];
                $roleID = $positionInfo[1];
                $employeeData['role_id'] = $roleID;
                $employeeData['position_id'] = $positionID;
            }
            $employeeData = array_filter($employeeData);
            if (!empty($employeeData)) {
                if (isset($employeeIDs[$rowConvert['employee_id']])) {
                    $employeeInfos['update'][$employeeIDs[$rowConvert['employee_id']]] = $employeeData;
                } else {
                    $employeeData['employee_id'] = $rowConvert['employee_id'];
                    $employeeData['created_at'] = Carbon::now();
                    $employeeData['updated_at'] = Carbon::now();
                    $employeeData['password'] = bcrypt('yst' . $rowConvert['employee_id']);
                    $employeeInfos['insert'][] = $employeeData;

                    //build model user
                    $notifyUsers[] = [
                        'password' => 'yst' . $rowConvert['employee_id'],
                        'user' => [
                            'role_id' => $employeeData['role_id'],
                            'office_id' => $employeeData['office_id'],
                            'position_id' => $employeeData['position_id'],
                            'employee_id' => $employeeData['employee_id'],
                            'email' => $employeeData['email'],
                            'user_name' => $employeeData['name'],
                            'password' => 'yst' . $rowConvert['employee_id'],
                            'full_name' => $rowConvert['full_name'] ?? ''
                        ]
                    ];
                }
            }

            //profile info
            $profileData = [
                'full_name' => $rowConvert['full_name'] ?? '',
                'katakana_name' => $rowConvert['katakana_name'] ?? '',
                'phone' => $rowConvert['phone'] ?? '',
            ];
            $profileData = array_filter($profileData);
            if (!empty($profileData)) {
                if (isset($employeeIDs[$rowConvert['employee_id']])) {
                    if (isset($profiles[$employeeIDs[$rowConvert['employee_id']]])) {
                        $profileInfos['update'][$profiles[$employeeIDs[$rowConvert['employee_id']]]] = $profileData;
                    } else {
                        $profileData['user_id'] = $rowConvert['employee_id'];
                        $profileData['created_at'] = Carbon::now();
                        $profileData['updated_at'] = Carbon::now();
                        $profileInfos['insert'][] = $profileData;
                    }
                } else {
                    $profileData['phone'] = $rowConvert['phone'] ?? null;
                    $profileData['full_name'] = $rowConvert['full_name'] ?? null;
                    $profileData['katakana_name'] = $rowConvert['katakana_name'] ?? null;
                    $profileData['user_id'] = $rowConvert['employee_id'];
                    $profileData['created_at'] = Carbon::now();
                    $profileData['updated_at'] = Carbon::now();
                    $profileInfos['insert'][] = $profileData;
                }

            }
        }

        //insert or update branch
        if (!empty($branchInfos['update'])) {
            foreach ($branchInfos['update'] as $key => $item) {
                $branchRepository->update($key, $item);
            }
        }
        if (!empty($branchInfos['insert'])) {
            $branchRepository->insertMany($branchInfos['insert']);
        }
        $branchs = $branchRepository->getCode();

        //insert or update division
        if (!empty($divisionInfos['update'])) {
            foreach ($divisionInfos['update'] as $key => $item) {
                if (isset($item['branch_id'])) {
                    $item['branch_id'] = $branchs[$item['branch_id']];
                }
                $divisionRepository->update($key, $item);
            }
        }
        if (!empty($divisionInfos['insert'])) {
            foreach ($divisionInfos['insert'] as $key => $item) {
                $divisionInfos['insert'][$key]['branch_id'] = $branchs[$item['branch_id']];
            }
            $divisionRepository->insertMany($divisionInfos['insert']);
        }
        $divisions = $divisionRepository->getCode();

        //insert or update office
        if (!empty($officeInfos['update'])) {
            foreach ($officeInfos['update'] as $key => $item) {
                if (isset($item['division_id'])) {
                    $item['division_id'] = $divisions[$item['division_id']];
                }
                $officeRepository->update($key, $item);
            }
        }
        if (!empty($officeInfos['insert'])) {
            foreach ($officeInfos['insert'] as $key => $item) {
                $officeInfos['insert'][$key]['division_id'] = $divisions[$item['division_id']];
            }
            $officeRepository->insertMany($officeInfos['insert']);
        }
        $offices = $officeRepository->getCode();

        //insert or update position
        if (!empty($positionInfos['update'])) {
            foreach ($positionInfos['update'] as $key => $item) {
                $positionRepository->update($key, $item);
            }
        }

        //insert or update user
        if (!empty($employeeInfos['update'])) {
            foreach ($employeeInfos['update'] as $key => $item) {
                if (isset($item['office_id'])) {
                    $item['office_id'] = $offices[$item['office_id']];
                }
                $userRepository->rollbackAndUpdate($key, $item);
            }
        }
        if (!empty($employeeInfos['insert'])) {
            foreach ($employeeInfos['insert'] as $key => $item) {
                $employeeInfos['insert'][$key]['office_id'] = $offices[$item['office_id']];
            }
            $userRepository->insertMany($employeeInfos['insert']);
        }
        $employeeIDs = $userRepository->getEmployeeID();

        //insert or update profile
        if (isset($profileInfos['update'])) {
            foreach ($profileInfos['update'] as $key => $item) {
                $profileRepository->rollbackAndUpdate($key, $item);
            }
        }
        if (!empty($profileInfos['insert'])) {
            foreach ($profileInfos['insert'] as $key => $item) {
                $profileInfos['insert'][$key]['user_id'] = $employeeIDs[$item['user_id']];
            }
            $profileRepository->insertMany($profileInfos['insert']);
        }

        //insert or update folder
        $this->folder($branchRepository, $divisionRepository, $officeRepository, $folderRepository);

        //delete user and profile
        $userRepository->deleteUserImport(array_values($allEmployeeIdImport), $roles[Role::SYSTEM_ADMIN]);
        $profileRepository->deleteByUser(array_values($allEmployeeIdImport), $roles[Role::SYSTEM_ADMIN]);

        //send mail to new user
        // if (!empty($notifyUsers)) {
        //     foreach ($notifyUsers as $item) {
        //         Mail::to($item['user']['email'])->send(new NewUserRegister($item['user'], $item['password']));
        //     }
        // }
    }
}
