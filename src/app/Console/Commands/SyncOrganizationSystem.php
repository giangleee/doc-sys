<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Repositories\BranchRepository;
use App\Repositories\DivisionRepository;
use App\Repositories\OfficeRepository;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\PositionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\FolderRepository;
use App\Helper\Constant;
use App\Models\Folder;
use App\Models\Role;

class SyncOrganizationSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:organization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync organization and user';

    protected $branchRepo;
    protected $divisionRepo;
    protected $officeRepo;
    protected $storeRepo;
    protected $userRepo;
    protected $profileRepo;
    protected $positionRepo;
    protected $roleRepo;
    protected $folderRepo;
    protected $infoUser = [];
    protected $infoBranchs = [];
    protected $infoDivisions = [];
    protected $infoOffices = [];
    protected $infoStores = [];
    // protected $userErrors = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        BranchRepository $branchRepo,
        DivisionRepository $divisionRepo,
        OfficeRepository $officeRepo,
        StoreRepository $storeRepo,
        UserRepository $userRepo,
        ProfileRepository $profileRepo,
        PositionRepository $positionRepo,
        RoleRepository $roleRepo,
        FolderRepository $folderRepo
        )
    {
        parent::__construct();
        $this->branchRepo = $branchRepo;
        $this->divisionRepo = $divisionRepo;
        $this->officeRepo = $officeRepo;
        $this->storeRepo = $storeRepo;
        $this->userRepo = $userRepo;
        $this->profileRepo = $profileRepo;
        $this->positionRepo = $positionRepo;
        $this->roleRepo = $roleRepo;
        $this->folderRepo = $folderRepo;
    }

    private function branch($header)
    {
        $param = [
            'query' => [
                'number_layer' => 2,
            ]
        ];
        $url = config('app.endpoint_organization') . '/api/vendor/departments';
        $branchs = get($url, $param, $header);
        if ($branchs['statusCode'] != 200) {
            \Log::info('branch get info error: '. $branchs['response']);
            return false;
        }

        $branchs['response'] = json_decode($branchs['response'], 1);
        foreach ($branchs['response']['data'] as $item) {
            //insert or update branch
            $branch = $this->branchRepo->updateOrCreateData(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'hiiragi_code' => $item['hiiragi_code'],
                    'code' => $item['code']
                ]
            );

            //insert or update folder
            $folder = $this->folderRepo->updateOrCreateData(
                [
                    'branch_id' => $branch->id,
                    'is_system' => Folder::IS_SYSTEM,
                    'parent_id' => null
                ],
                [
                    'branch_id' => $branch->id,
                    'is_system' => Folder::IS_SYSTEM,
                    'parent_id' => null,
                    'name' => $item['name'],
                    'owner_id' => 1
                ]
            );

            //save info branch
            $this->infoBranchs[$item['id']] = [
                'branch_id' => $branch->id,
                'folder_id' => $folder->id
            ];

        }

        return true;
    }

    private function division($header)
    {
        $param = [
            'query' => [
                'number_layer' => 3,
            ]
        ];
        $url = config('app.endpoint_organization') . '/api/vendor/departments';
        $divisions = get($url, $param, $header);
        if ($divisions['statusCode'] != 200) {
            \Log::info('division get info error: '. $divisions['response']);
            return false;
        }

        $divisions['response'] = json_decode($divisions['response'], 1);
        foreach ($divisions['response']['data'] as $item) {
            if (isset($this->infoBranchs[$item['parent_department_id']]) == false) {
                continue;
            }

            //insert or update branch
            $division = $this->divisionRepo->updateOrCreateData(
                ['code' => $item['code']],
                [
                    'code' => $item['code'],
                    'branch_id' => $this->infoBranchs[$item['parent_department_id']]['branch_id'],
                    'name' => $item['name'],
                    'hiiragi_code' => $item['hiiragi_code']
                ]
            );

            //insert or update folder
            $folder = $this->folderRepo->updateOrCreateData(
                [
                    'division_id' => $division->id,
                    'is_system' => Folder::IS_SYSTEM,
                ],
                [
                    'division_id' => $division->id,
                    'is_system' => Folder::IS_SYSTEM,
                    'name' => $item['name'],
                    'parent_id' => $this->infoBranchs[$item['parent_department_id']]['folder_id'],
                    'owner_id' => 1,
                ]
            );

            //save info branch
            $this->infoDivisions[$item['id']] = [
                'division_id' => $division->id,
                'folder_id' => $folder->id
            ];
        }

        return true;
    }

    private function office($header)
    {
        $param = [
            'query' => [
                'number_layer' => 4,
            ]
        ];
        $url = config('app.endpoint_organization') . '/api/vendor/departments';
        $offices = get($url, $param, $header);
        if ($offices['statusCode'] != 200) {
            \Log::info('office get info error: '. $offices['response']);
            return false;
        }

        $offices['response'] = json_decode($offices['response'], 1);
        foreach ($offices['response']['data'] as $item) {
            if (isset($this->infoDivisions[$item['parent_department_id']]) == false) {
                continue;
            }

            $dataOffice = [
                'code' => $item['code'],
                'division_id' => $this->infoDivisions[$item['parent_department_id']]['division_id'],
                'name' => $item['name'],
                'hiiragi_code' => $item['hiiragi_code'],
            ];
            if ($item['email']) {
                $dataOffice['email'] = $item['email'];
            }
            //insert or update branch
            $office = $this->officeRepo->updateOrCreateData(
                ['code' => $item['code']],
                $dataOffice
            );

            //insert or update folder
            $folder = $this->folderRepo->updateOrCreateData(
                [
                    'office_id' => $office->id,
                    'is_system' => Folder::IS_SYSTEM,
                    'service_user_id' => null
                ],
                [
                    'office_id' => $office->id,
                    'is_system' => Folder::IS_SYSTEM,
                    'service_user_id' => null,
                    'name' => $item['name'],
                    'parent_id' => $this->infoDivisions[$item['parent_department_id']]['folder_id'],
                    'owner_id' => 1
                ]
            );

            //save info office
            $this->infoOffices[$item['id']] = [
                'office_id' => $office->id,
                'folder_id' => $folder->id
            ];
        }

        return true;
    }

    private function stores($header)
    {
        $param = [
            'query' => [
                'number_layer' => 5,
            ]
        ];
        $url = config('app.endpoint_organization') . '/api/vendor/departments';
        $stores = get($url, $param, $header);
        if ($stores['statusCode'] != 200) {
            \Log::info('store get info error: '. $stores['response']);
            return false;
        }

        $stores['response'] = json_decode($stores['response'], 1);
        foreach ($stores['response']['data'] as $item) {
            if (isset($this->infoOffices[$item['parent_department_id']]) == false) {
                continue;
            }

            $dataStore = [
                'code' => $item['code'],
                'office_id' => $this->infoOffices[$item['parent_department_id']]['office_id'],
                'name' => $item['name'],
                'hiiragi_code' => $item['hiiragi_code'],
            ];
            if ($item['email']) {
                $dataStore['email'] = $item['email'];
            }
            //insert or update branch
            $store = $this->storeRepo->updateOrCreateData(
                ['code' => $item['code']],
                $dataStore
            );

            //insert or update folder
            $folder = $this->folderRepo->updateOrCreateData(
                [
                    'store_id' => $store->id,
                    'is_system' => Folder::IS_SYSTEM,
                    'service_user_id' => null
                ],
                [
                    'store_id' => $store->id,
                    'is_system' => Folder::IS_SYSTEM,
                    'service_user_id' => null,
                    'name' => $item['name'],
                    'parent_id' => $this->infoOffices[$item['parent_department_id']]['folder_id'],
                    'owner_id' => 1
                ]
            );

            //save info branch
            $this->infoStores[$item['id']] = [
                'store_id' => $store->id,
                'folder_id' => $folder->id
            ];
        }

        return true;
    }

    private function users($data, $param)
    {
        foreach ($data as $item) {
            if (in_array($item['code'], $param['user_system_admin'])) {
                continue;
            }

            // $positionAdmin = $param['positions'][10];

            $infoOrganization = [
                'branch_id' => null,
                'division_id' => null,
                'office_id' => null,
                'store_id' => null
            ];
            if (is_null($item['department_3'])) {
                $infoOrganization['branch_id'] = $this->infoBranchs[$item['department_2']['id']]['branch_id'];
                
            } elseif (is_null($item['department_4'])) {
                $infoOrganization['branch_id'] = $this->infoBranchs[$item['department_2']['id']]['branch_id'];
                $infoOrganization['division_id'] = $this->infoDivisions[$item['department_3']['id']]['division_id'];
            } elseif (is_null($item['department_5'])) {
                $infoOrganization['branch_id'] = $this->infoBranchs[$item['department_2']['id']]['branch_id'];
                $infoOrganization['division_id'] = $this->infoDivisions[$item['department_3']['id']]['division_id'];
                $infoOrganization['office_id'] = $this->infoOffices[$item['department_4']['id']]['office_id'];
            } else {
                $infoOrganization['branch_id'] = $this->infoBranchs[$item['department_2']['id']]['branch_id'];
                $infoOrganization['division_id'] = $this->infoDivisions[$item['department_3']['id']]['division_id'];
                $infoOrganization['office_id'] = $this->infoOffices[$item['department_4']['id']]['office_id'];
                $infoOrganization['store_id'] = $this->infoStores[$item['department_5']['id']]['store_id'];
            }

            if (!isset($item['positions'][0]) || !isset($param['positions'][$item['positions'][0]['code']])) {
                // if (isset($item['positions'][0])) {
                //     $this->userErrors[$item['positions'][0]][] = $item['code'];
                // } else {
                //     $this->userErrors['position_empty'][] = $item['code'];
                // }
                continue;
            }
            if (!$item['email']) {
                // $this->userErrors['email'][] = $item['code'];
                continue;
            }

            if (!is_null($infoOrganization['store_id'])) {
                $positionInfo = $param['positions'][$item['positions'][0]['code']];
            } else {
                $positionInfo = $param['positions'][10];
                
            }
            $positionInfo = explode('__', $positionInfo);
            //update or insert user
            $user = $this->userRepo->getUserWithTrash($item['code']);
            if ($user) {
                $user->role_id = $positionInfo[1] ?? 4;
                $user->email = $item['email'];
                $user->branch_id = $infoOrganization['branch_id'];
                $user->division_id = $infoOrganization['division_id'];
                $user->office_id = $infoOrganization['office_id'];
                $user->store_id = $infoOrganization['store_id'];
                $user->position_id = $positionInfo[0];
                $user->name = $item['name'];
                $user->is_first_login = 0;
                $user->save();
            } else {
                $user = $this->userRepo->create([
                    'employee_id' => $item['code'],
                    'role_id' => $positionInfo[1],
                    'email' => $item['email'],
                    'branch_id' => $infoOrganization['branch_id'],
                    'division_id' => $infoOrganization['division_id'],
                    'office_id' => $infoOrganization['office_id'],
                    'store_id' => $infoOrganization['store_id'],
                    'position_id' => $positionInfo[0],
                    'name' => $item['name'],
                    'is_first_login' => 0,
                ]);
            }
            $this->userIDs[] = $user->id;

            //update or insert profile
            $profile = $this->profileRepo->getProfileWithTrash($user->id);
            if ($profile) {
                $profile->full_name = $item['family_name'];
                $profile->katakana_name = $item['family_name_kana'];
                $profile->phone = $item['phone'];
                $profile->save();
            } else {
                $this->profileRepo->create([
                    'user_id' => $user->id,
                    'full_name' => $item['family_name'],
                    'katakana_name' => $item['family_name_kana'],
                    'phone' => $item['phone'],
                ]);
            }
        }

        return true;
    }

    private function position($header)
    {
        $roles = $this->roleRepo->getAllRole();
        $getPosition = true;
        $url = config('app.endpoint_organization') . '/api/positions';
        $param = [
            'query' => [
                'page' => 1,
            ]
        ];
        while ($getPosition) {
            $positions = get($url, $param, $header);
            if ($positions['statusCode'] != 200) {
                \Log::info('position get info error in page ' . $param['page'] .': '. $positions['response']);
                continue;
            }

            $positions['response'] = json_decode($positions['response'], 1);
            if (empty($positions['response']['data']['data'])) {
                $getPosition = false;
                continue;
            }

            foreach ($positions['response']['data']['data'] as $item) {
                $position = $this->positionRepo->findByCode($item['code']);
                if ($position) {
                    $position->name = $item['name'];
                    $position->save();
                } else {
                    $this->positionRepo->create([
                        'code' => $item['code'],
                        'name' => $item['name'],
                        'role_id' => $item['code'] == 14 ? $roles[Role::ADMIN] : $roles[Role::STAFF]
                    ]);
                }
            }
            $param['query']['page']++;
        }

        return true;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!config('app.endpoint_organization')
            || !config('app.app_id_organization')
            || !config('app.app_secret_organization')
            ) {
                \Log::info('Miss connection infomation');
                return false;
            }

        //login
        $token = null;
        $appID = config('app.app_id_organization');
        $appSecret = config('app.app_secret_organization');
        $urlLogin = config('app.endpoint_organization') . '/api/vendor/auth';
        $paramLogin = [
            'app_id' => $appID,
            'app_secret' => $appSecret,
        ];

        $login = post($urlLogin, ['json' => $paramLogin]);
        if ($login['statusCode'] != 200) {
            \Log::info('organization login error: '. $login['response']);
            return false;
        }
        $token = json_decode($login['response'], 1)['data']['token'];

        //build header and param
        $header = [
            'headers' => [
                'token' => $token,
            ]
        ];

        $resultBranch = $this->branch($header);
        if (!$resultBranch || empty($this->infoBranchs)) {
            return false;
        }

        $resultDivision = $this->division($header);
        if (!$resultDivision || empty($this->infoDivisions)) {
            return false;
        }

        $resultOffice = $this->office($header);
        if (!$resultOffice || empty($this->infoOffices)) {
            return false;
        }

        $resultStore = $this->stores($header);
        if (!$resultStore || empty($this->infoStores)) {
            return false;
        }
        
        //update or insert position
        $this->position($header);

        //init data
        $positions = $this->positionRepo->getCode();
        $userSystemAdmin = $this->userRepo->getUserSystemAdmin();

        //get info user
        $getUser = true;
        $url = config('app.endpoint_organization') . '/api/vendor/employees/layer';
        $param = [
            'query' => [
                'page_size' => 1000,
                'page' => 1,
                'date' => Carbon::now()->format('Y-m-d') . 'T' . Carbon::now()->format('H:i:s') . 'Z'
            ]
        ];
        $masterData = [
            'positions' => $positions,
            'user_system_admin' => $userSystemAdmin,
            'user_level1' => userOfficeLevel1(),
            'user_level2' => userOfficeLevel2(),
        ];

        while ($getUser) {
            $users = get($url, $param, $header);
            if ($users['statusCode'] != 200) {
                \Log::info('organization get info user page ' . $param['query']['page'] . ' error: ', $users['response']);
                continue;
            }

            $users['response'] = json_decode($users['response'], 1);
            if (empty($users['response']['data'])) {
                $getUser = false;
                continue;
            }

            $this->users($users['response']['data'], $masterData);
            $param['query']['page']++;
        }

        //delete user after insert
        if (!empty($this->userIDs)) {
            $this->userRepo->deleteAllWithOutSuperuser();
            $this->profileRepo->deleteProfileWithOutSuperuser();
            $userIDChunk = array_chunk($this->userIDs, 5000);
            foreach ($userIDChunk as $item) {
                $this->userRepo->restoreByID($item);
                $this->profileRepo->restoreByUserID($item);
            }
        }
        // \Log::info($this->userErrors);
        \Log::info('Finish import office and user');
    }
}
