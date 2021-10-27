<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\ResetPassword;
use App\Models\User;
use App\Repositories\PasswordResetRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    protected $userRepository;
    protected $passwordResetRepository;

    public function __construct(UserRepository $userRepository, PasswordResetRepository $passwordResetRepository)
    {
        $this->userRepository = $userRepository;
        $this->passwordResetRepository = $passwordResetRepository;
    }
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            if ($request->employee_id == User::SYSTEM_ADMIN_CODE) {
                $user = $this->userRepository->findByEmployeeId($request->employee_id);
                if (!empty($user)) {
                    if (!$user->status) {
                        return responseError(404, __('message.employee_id_not_exist'));
                    }
                    $passwordReset = $this->passwordResetRepository->create([
                        'employee_id' => $user->employee_id,
                        'token' => Str::random(60)
                    ]);
                    if ($passwordReset) {
                        $fullName = $user->profile->full_name;
                        Mail::to($user->email)->send(new ResetPassword($fullName, $passwordReset->token));
                    }
                }
            }
            return responseOK(['message' => __('passwords.sent')]);
        } catch (\Exception $exception) {
            return responseError(500, $exception->getMessage());
        }
    }

    public function resetPassword(ResetPasswordRequest $request, $token)
    {
        $passwordReset = $this->passwordResetRepository->findByToken($token);
        $expiredTime = config('mail.mail_reset_password_expired_time');
        if (empty($passwordReset) || Carbon::parse($passwordReset->created_at)->addMinutes($expiredTime)->isPast()) {
            DB::table('password_resets')->where('token', $token)->delete();
            return responseValidate(['token' =>  __('passwords.token')]);
        }
        $user = $this->userRepository->findByEmployeeId($passwordReset->employee_id);
        $this->userRepository->update($user->id, $request->only('password'));
        DB::table('password_resets')->where('token', $token)->delete();

        return responseOK(['message' => __('passwords.reset')]);
    }
}
