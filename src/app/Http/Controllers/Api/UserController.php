<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangeOfficeRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\ChangePositionRequest;
use App\Http\Requests\User\SettingRoleRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\BasicUserCollection;
use App\Http\Resources\BasicUserResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Mail\NewUserRegister;
use App\Http\Resources\UserSoftDeleteResource;
use App\Models\Role;
use App\Models\User;
use App\Repositories\ProfileRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    protected $userRepository;
    protected $profileRepository;

    public function __construct(UserRepository $userRepository, ProfileRepository $profileRepository)
    {
        $this->userRepository = $userRepository;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Get list users
     */
    public function index(Request $request)
    {
        $users = $this->userRepository->getList($request);
        return responseOK(new UserCollection($users));
    }

    /**
     * Show the profile for the given user.
     */
    public function show($id)
    {
        $user = $this->userRepository->findOrFail($id);
        return responseOK(new UserResource($user));
    }

    /**
     * Store a new user
     */
    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $userSoftDelete = $this->userRepository->getUserSoftDelete($request->employee_id);
            if (!empty($userSoftDelete)) {
                return responseCreateUniqueSoftDelete(new UserSoftDeleteResource($userSoftDelete));
            }
            $requestData = $request->only([
                'role_id',
                'branch_id',
                'division_id',
                'office_id',
                'store_id',
                'position_id',
                'name',
                'employee_id',
                'email',
            ]);
            $user = $this->userRepository->create($requestData);
            $requestDataProfile = $request->only([
                'full_name',
                'katakana_name',
                'phone'
            ]);
            $requestDataProfile['user_id'] = $user->id;
            $this->profileRepository->create($requestDataProfile);
            DB::commit();
            return responseCreated(new UserResource($user));
        } catch (\Exception $exception) {
            DB::rollback();
            if (strpos(get_class($exception), 'QueryException') !== false) {
                return responseValidate(['employee_id' =>  __('message.users.employee_id.unique')]);
            }
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Update the given user
     */
    public function update($id, UpdateUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->userRepository->findOrFail($id);
            $requestData = $request->only([
                'role_id',
                'branch_id',
                'division_id',
                'office_id',
                'store_id',
                'position_id',
                'name',
                'email',
            ]);
            $this->userRepository->update($id, $requestData);

            $requestDataProfile = $request->only([
                'full_name',
                'katakana_name',
                'phone'
            ]);
            $this->profileRepository->update($user->profile->id, $requestDataProfile);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollback();
            if (strpos(get_class($exception), 'QueryException') !== false) {
                return responseValidate(['employee_id' =>  __('message.users.employee_id.unique')]);
            }
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Delete the given user
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            if (auth()->user()->id == $id) {
                return responseError(403, __('message.unauthorized'));
            }
            $user = $this->userRepository->findOrFail($id);
            if ($user->role->code == Role::SYSTEM_ADMIN) {
                return responseError(403, __('message.unauthorized'));
            }
            $this->profileRepository->delete($user->profile->id);
            $this->userRepository->delete($id);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Change role a user
     */
    public function changeRole(SettingRoleRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->userRepository->update($request->user_id, ['role_id' => $request->role_id]);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Change role of user
     */
    public function changeOffice(ChangeOfficeRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->userRepository->update($request->user_id, ['office_id' => $request->office_id]);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Change password of user
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->userRepository->update($request->user_id, ['password' => $request->password]);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    public function changePosition(ChangePositionRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->userRepository->update($request->user_id, ['position_id' => $request->position_id]);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError(500, $exception->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->userRepository->deletes(explode(',', $request->ids));
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError($exception->getCode(), $exception->getMessage());
        }
    }

    public function changeStatus($id, Request $request)
    {
        if ($request->has('status')) {
            DB::beginTransaction();
            try {
                $user = $this->userRepository->findOrFail($id);
                if ($user->role->code == Role::SYSTEM_ADMIN) {
                    return responseError(403, __('message.unauthorized'));
                }
                $this->userRepository->update($id, ['status' => $request->status]);
                DB::commit();
                return responseUpdatedOrDeleted();
            } catch (\Exception $exception) {
                DB::rollback();
                return responseError(500, $exception->getMessage());
            }
        }
    }

    public function getAll()
    {
        return responseOK(new BasicUserCollection($this->userRepository->getAllWithOutRelation()));
    }

    public function restore(StoreUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $userRestore = $this->userRepository->getUserSoftDelete($request->employee_id);
            if (empty($userRestore)) {
                return responseError(404, __('message.restore_user_failure'));
            }
            $userRestore->profile()->restore();
            $userRestore->restore();
            $requestData = $request->only([
                'role_id',
                'branch_id',
                'division_id',
                'office_id',
                'store_id',
                'position_id',
                'name',
                'employee_id',
                'email',
            ]);
            $password = User::PRE_PASSWORD . $request->employee_id;
            $requestData['password'] = $password;
            $requestData['is_first_login'] = User::IS_FIRST_LOGIN;
            $this->userRepository->update($userRestore->id, $requestData);
            $requestDataProfile = $request->only([
                'full_name',
                'katakana_name',
                'phone'
            ]);
            $this->profileRepository->update($userRestore->profile->id, $requestDataProfile);
            $user = $this->userRepository->findByEmployeeId($request->employee_id);
            $userInfoToSendMail = [
                'full_name' => $user->profile->full_name,
                'user_name' => $user->name,
                'employee_id' => $user->employee_id
            ];
            Mail::to($user->email)->send(new NewUserRegister($userInfoToSendMail, $password));
            DB::commit();
            return responseCreated(new UserResource($user));
        } catch (\Exception $exception) {
            DB::rollback();
            if (strpos(get_class($exception), 'QueryException') !== false) {
                return responseValidate(['employee_id' =>  __('message.users.employee_id.unique')]);
            }
            return responseError(500, __('message.restore_user_failure'));
        }
    }

}
