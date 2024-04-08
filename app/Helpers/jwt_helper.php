<?php

use App\Models\UserModel;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// use Exception;

function getJWTFromRequest($authHeader)
{
    if (is_null($authHeader)) {
        throw new Exception('Missing or invalid JWT in request');
    }

    return explode(' ', $authHeader);
}

function validateJWTFromRequest(string $jwt)
{
    $key = Services::getSecretKey();
    $decodedToken = JWT::decode($jwt, new Key($key, 'HS256'));
    $userModel = new UserModel();
    $userModel->findUserByEmail($decodedToken->email);
}

function signJWTForUser(string $email, int $id)
{
    $issueAtTime = time();
    $tokenTimeToLive = getenv('JWT_TIME_TO_LIVE');
    $tokenExpiration = $issueAtTime + $tokenTimeToLive;
    $payload = [
        "sub" => $id,
        "email" => $email,
        "iat" => $issueAtTime,
        "exp" => $tokenExpiration
    ];

    $jwt = JWT::encode($payload, services::getSecretKey(), 'HS256');

    return $jwt;
}

function signRefreshTokenForUser(string $email, int $id)
{
    $issueAtTime = time();
    $tokenTimeToLive = env('REFRESH_TOKEN_TIME_TO_LIVE');
    $tokenExpiration = $issueAtTime + $tokenTimeToLive;
    $payload = [
        "sub" => $id,
        "email" => $email,
        "exp" => $tokenExpiration
    ];

    $refresh_token = JWT::encode($payload, services::getSecretKey(), 'HS256');

    return $refresh_token;
}
