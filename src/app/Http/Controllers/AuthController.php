<?php

namespace App\Http\Controllers;

use App\Exceptions\LoginFailed;
use App\Helper\Constant;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\TokenService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $tokenService;
    protected $userRepository;

    public function __construct(TokenService $tokenService, UserRepository $userRepository)
    {
        $this->tokenService = $tokenService;
        $this->userRepository = $userRepository;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['employee_id', 'password']);
        $credentials['status'] = User::ACTIVE;
        $uuid = Uuid::uuid4();
        if ($request->employee_id != User::SYSTEM_ADMIN_CODE) {
            throw new LoginFailed(__('message.login_failure'));
        }
        if ($request->app_login) {
            $token = auth('api')
                ->setTTl(9999999)
                ->claims(['token_id' => $uuid])
                ->attempt($credentials);
        } else {
            $token = auth('api')
                ->claims(['token_id' => $uuid])
                ->attempt($credentials);
        }
        if (!$token) {
            throw new LoginFailed(__('message.login_failure'));
        }
        $refreshToken = $this->tokenService->makeRefreshToken(auth('api')->user()->id, $uuid, $request->app_login);
        $this->tokenService->saveTokenInCookie($refreshToken);
        return $this->respondWithToken($token, $refreshToken);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return responseOK(auth('api')->user()->load('branch', 'division', 'office', 'store', 'profile'));
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $accessTokenId = auth('api')
            ->payload()
            ->get('token_id');
        $this->tokenService->clearRefreshToken($accessTokenId);
        auth('api')->logout();
        return responseOK();
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        try {
            $payload = $this->tokenService
                ->refresh($request->bearerToken());
            $this->tokenService->saveTokenInCookie($payload['refresh_token']);
            return $this->respondWithToken(
                $payload['access_token'],
                $payload['refresh_token']
            );
        } catch (Exception $e) {
            throw new AuthenticationException(__('message.unauthenticated'));
        }
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $refreshToken = null, $loginAppSuccess  = null)
    {
        $response = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
        if ($refreshToken) {
            $response['refresh_token'] = $refreshToken;
        }
        if ($loginAppSuccess) {
            $response['app_success'] = $loginAppSuccess;
        }
        $response['user'] = auth('api')->user();
        return responseOK($response);
    }

    public function ssoAuthentication(Request $request)
    {
        if (!empty($request->code)) {
            // Get access token
            $url = Config::get('app.url_sso_authenticated') . '/oauth/token';
            $responseToken = post($url, ['json' => [
                'app_id' => Config::get('app.app_id_sso'),
                'app_secret' => Config::get('app.app_secret_sso'),
                'code' => $request->code,
            ]]);
            $responseToken = json_decode($responseToken['response'], true);
            if ($responseToken['success']) {
                $token = $responseToken['data']['access_token'];
                // Get auth user info
                $urlGetUserInfo = Config::get('app.url_sso_authenticated') . '/oauth/user';
                $responseUser = post($urlGetUserInfo, ['json' => ['access_token' => $token]]);
                $responseUser = json_decode($responseUser['response'], true);
                if ($responseUser['success']) {
                    $authUser = $this->userRepository->findByEmployeeId($responseUser['data']['code']);
                    // check user exist and active
                    if (empty($authUser)) {
                        throw new LoginFailed(__('message.login_failure'));
                    }
                    if (!empty($authUser) && $authUser->status == User::ACTIVE) {
                        $uuid = Uuid::uuid4();
                        $loginAppSuccess = false;
                        if (isset($request->app)) {
                            $loginAppSuccess = true;
                            JWTAuth::factory()->setTTL(99999);
                        }
                        $token = JWTAuth::fromUser($authUser);
                        if (!$token) {
                            throw new LoginFailed(__('message.login_failure'));
                        }
                        $refreshToken = $this->tokenService->makeRefreshToken($authUser->id, $uuid);
                        $this->tokenService->saveTokenInCookie($refreshToken);
                        return $this->respondWithToken($token, $refreshToken, $loginAppSuccess);
                    }
                }
            }
        }
        return responseError(401, __('message.unauthenticated'));
    }
}
