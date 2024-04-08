<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Codeigniter\HTTP\IncomingRequest;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Response;

use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\TokenModel;
use App\Models\UserModel;

use Exception;

class TokenAuthenticationController extends ResourceController
{

    public function getNewRefreshToken()
    {
        $request = service('request');
        helper('jwt');
        $userModel = new UserModel();
        $tokenModel = new TokenModel();

        if ($request->getMethod() === 'post') {
            $refresh_token = trim($request->getVar('refresh_token'));

            $key = Services::getSecretKey();
            try {
                $decoded = JWT::decode($refresh_token, new Key($key, 'HS256'));
                $decoded_user_email = $decoded->email;
                $decoded_user_id = $decoded->sub;
                $validate_user = $userModel->findUserByEmail($decoded_user_email);

                if ($validate_user !== null) {
                    
                    $hash_raw_token = hash_hmac("sha256", $refresh_token, $key);
                    $retrieve_refresh_token = $tokenModel->where('refresh_token', $hash_raw_token)->first();

                    if ($retrieve_refresh_token) {
                        $tokenModel->where('refresh_token', $hash_raw_token)->delete();
                        $new_refresh_token = signRefreshTokenForUser($validate_user['email'], $validate_user['id']);
                        $tokenModel->insert(
                            [
                                'refresh_token' => hash_hmac("sha256", $new_refresh_token, $key),
                                'user_id' => $decoded_user_id
                            ]
                        );
                        unset($validate_user['password']);
                        $response = [
                            'status' => 200,
                            'message' => 'Refresh token generated successfully',
                            'error' => false,
                            'data' => $validate_user,
                            'access_token' => signJWTForUser($validate_user['email'], $validate_user['id']),
                            'refresh_token' => $new_refresh_token
                        ];

                        return $this->respond($response,  ResponseInterface::HTTP_OK);
                    } else {
                        $response = [
                            'status' => 400,
                            'message' => 'Invalid token (Token is not on the whitelist)',
                            'error' => true,
                            'data' => [],
                        ];

                        return $this->respond($response,  ResponseInterface::HTTP_BAD_REQUEST);
                    }
                }
            } catch (Exception $exception) {

                $response = [
                    'status' => 400,
                    'message' => 'Invalid token',
                    'error' => true,
                    'data' => [],

                ];

                return $this->respond($response,  ResponseInterface::HTTP_BAD_REQUEST);
            }
        }
    }

    protected function getUserById()
    {
    }
}
