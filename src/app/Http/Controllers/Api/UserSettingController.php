<?php

namespace App\Http\Controllers\Api;

use App\Helper\Constant;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangePasswordProfileRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Repositories\ProfileRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSettingController extends Controller
{
    protected $userRepository;
    protected $profileRepository;
    protected $roleRepository;

    public function __construct(
        UserRepository $userRepository,
        ProfileRepository $profileRepository,
        RoleRepository $roleRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->profileRepository = $profileRepository;
        $this->roleRepository = $roleRepository;
    }

    public function index()
    {
        $user = auth()->user();
        return responseOK(new UserResource($user));
    }

    public function update(UpdateProfileRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataUser = $request->only(['name', 'email']);
            $roleUser = $this->roleRepository->find(auth()->user()->role_id);
            if ($roleUser->code == Role::STAFF) {
                unset($dataUser['email']);
            }
            $this->userRepository->update(auth()->user()->id, $dataUser);
            $dataProfile = $request->only(['full_name', 'katakana_name', 'phone']);
            if ($request->has('avatar')) {
                $fileService = new FileService();
                $url = $fileService->uploadFile(Constant::AVATAR_FOLDER, $request->file('avatar'));
                $dataProfile['avatar'] = $url;
            }
            $this->profileRepository->update(auth()->user()->profile->id, $dataProfile);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError(500, $exception->getMessage());
        }
    }

    public function changePassword(ChangePasswordProfileRequest $request)
    {
        DB::beginTransaction();
        try {
            $currentUser = auth()->user();
            if (!Hash::check($request->old_password, auth()->user()->password)) {
                return response()->json(['errors' => ['password' => __('message.current_password')]], 422);
            }
            $data['password'] = $request->new_password;
            // if is the first time login
            if ($currentUser->is_first_login) {
                $data['is_first_login'] = User::IS_NOT_FIRST_LOGIN;
            }
            $this->userRepository->update($currentUser->id, $data);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError(500, $exception->getMessage());
        }
    }

}
