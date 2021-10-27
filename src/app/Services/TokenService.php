<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Token;

class TokenService
{
    public function makeToken($payload)
    {
        JWTFactory::customClaims($payload);
        $payload = JWTFactory::make($payload);
        return JWTAuth::encode($payload)->get();
    }

    public function makeAccessToken($userId, Uuid $id)
    {
        $payload = [
            'sub' => $userId,
            'token_id' => $id,
            'exp' => $this->ttlToTimestamp(config('jwt.ttl'))
        ];
        JWTFactory::customClaims($payload);
        $payload = JWTFactory::make($payload);
        return JWTAuth::encode($payload)->get();
    }

    public function makeRefreshToken($userId, Uuid $accesTokenId, $appLogin = 0)
    {
        $refreshTtlInMinute = config('jwt.refresh_ttl');
        if ($appLogin) {
            $refreshTtlInMinute = null;
        }
        $payload = [
            'sub' => $accesTokenId,
            'exp' =>  $this->ttlToTimestamp($refreshTtlInMinute)
        ];
        Cache::put($accesTokenId, $userId, $refreshTtlInMinute * 60);
        JWTFactory::customClaims($payload);
        $payload = JWTFactory::make($payload);
        return JWTAuth::encode($payload)->get();
    }

    public function refresh($refreshToken)
    {
        $payload = $this->validateRefreshToken($refreshToken);
        if (!$payload) {
            throw new TokenInvalidException();
        }
        $this->clearRefreshToken($payload['access_token_id']);
        $uuid = Uuid::uuid4();
        $accessToken = $this->makeAccessToken($payload['user_id'], $uuid);
        $refreshToken = $this->makeRefreshToken($payload['user_id'], $uuid);
        return [
            "access_token" => $accessToken,
            "refresh_token" => $refreshToken
        ];
    }

    public function clearRefreshToken($accesTokenId)
    {
        Cache::forget($accesTokenId);
    }

    public function validateToken($token)
    {
        try {
            return JWTAuth::decode(new Token($token));
        } catch (Exception $e) {
            return null;
        }
    }

    public function validateRefreshToken($token)
    {
        $payload = $this->validateToken($token);
        if ($payload && $userId = Cache::get($payload->get('sub'))) {
            return [
                'user_id' => $userId,
                'access_token_id' => $payload->get('sub')
            ];
        }
        return null;
    }

    public function saveTokenInCookie($token)
    {
        Cookie::queue(
            config('jwt.auth_cookie_name'),
            $token,
            0, // forever
            null,
            null,
            false, // secure
            true, // HttpOnly
            false,
            'lax' // sameSite
        );
    }

    protected function ttlToTimestamp($ttl)
    {
        return Carbon::now()->addMinutes($ttl)->getTimestamp();
    }
}
