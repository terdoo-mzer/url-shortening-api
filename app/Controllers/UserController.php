<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TokenModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Codeigniter\HTTP\IncomingRequest;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Response;
use Config\Services;
use Exception;


class UserController extends ResourceController
{
    public function createUser()
    {
        $request = service('request'); // Use service() helper to get the request instance

        if ($request->getMethod() === 'post') { // Check if the request method is POST
            $userModel = new UserModel();

            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|min_length[5]|max_length[30]|valid_email|is_unique[users.email]',
                'password' => 'required',
                'confirm_password' => 'required|matches[password]',
            ];

            if (!$this->validate($rules)) { // Use validate() method to perform validation
                $response = [
                    'status' => 400,
                    'message' => $this->validator->getErrors(),
                    'error' => true,
                    'data' => []
                ];

                // Generic response method
                return $this->respond($response,  ResponseInterface::HTTP_BAD_REQUEST);
            } else {
                $data = [
                    'first_name' => $request->getVar('first_name'),
                    'last_name' => $request->getVar('last_name'),
                    'email' => $request->getVar('email'),
                    'password' => $request->getVar('password'),
                ];

                // Insert the user data into the database
                $userModel->insert($data);

                // Return a success message
                $response = [
                    'status' => 201,
                    'message' => 'User is successfully created ',
                    'error' => false,
                    'data' => []
                ];

                return $this->respond($response,  ResponseInterface::HTTP_CREATED);
            }

            
        }
    }

    public function loginUser()
    {
        $request = service('request'); // Use service() helper to get the request instance
        $tokenModel = new TokenModel(); // Save refresh token to the database
        $userModel = new UserModel();
        $key = Services::getSecretKey();
        helper('jwt');

        if ($request->getMethod() === 'post') { // Check if the request method is POST

            $rules = [
                'email' => 'required|min_length[5]|max_length[30]|valid_email',
                'password' => 'required',
            ];

            if (!$this->validate($rules)) { // Use validate() method to perform validation
                $response = [
                    'status' => 400,
                    'message' => $this->validator->getErrors(),
                    'error' => true,
                    'data' => []
                ];
                // Invalid data
                return $this->respond($response,  ResponseInterface::HTTP_BAD_REQUEST);
            } else {
                $data = [
                    'email' => $request->getVar('email'),
                    'password' => $request->getVar('password'),
                ];

                // Find if user exists with the provided email
                $userRecord = $userModel->findUserByEmail($data['email']);

                if (password_verify($data['password'], $userRecord['password'])) {
                    // User exists. Therefore, create JWT and log user in

                    unset($userRecord['password']);

                    $refresh_token = signRefreshTokenForUser($userRecord['email'], $userRecord['id']);
                    try {

                        // Hash refresh token before committing to the database
                        /******
                         * TODO extract the hashing logic to the tokensModel
                         * 
                         * ******/

                        $tokenModel->insert([
                            'refresh_token' => hash_hmac("sha256", $refresh_token, $key),
                            'user_id' => $userRecord['id']
                        ]);

                        $response = [
                            'status' => 200,
                            'message' => 'User logged in successfully',
                            'error' => false,
                            'data' => $userRecord,
                            'access_token' => signJWTForUser($userRecord['email'], $userRecord['id']),
                            'refresh_token' => $refresh_token
                        ];

                        return $this->respond($response,  ResponseInterface::HTTP_OK);
                    } catch (Exception $exception) {
                        return $exception;
                    }
                } else {
                    // Return failed message to the user. Passwrod does not match with 
                    // provided email.
                    $response = [
                        'status' => 401,
                        'message' => 'User email or password incorrect',
                        'error' => true,
                        'data' => [],
                    ];

                    return $this->respond($response,  ResponseInterface::HTTP_UNAUTHORIZED);
                }

                // Return a success message or redirect to another page
                $response = [
                    'status' => 201,
                    'message' => 'User is successfully created ',
                    'error' => false,
                    'data' => []
                ];
                return $this->respond($response,  ResponseInterface::HTTP_CREATED);
            }
        }
    }

    public function logout()
    {
        $request = service('request');
        $tokenModel = new TokenModel();
        $key = Services::getSecretKey();

        if ($request->getMethod() === 'delete') {
            
            $refresh_token = trim($request->getVar('refresh_token'));

            $hash_raw_token = hash_hmac("sha256", $refresh_token, $key);
            $retrieve_refresh_token = $tokenModel->where('refresh_token', $hash_raw_token)->delete();

            $response = [
                'status' => 200,
                'message' => 'you have been logged out successfully',
                'error' => false,
                'data' => []
            ];

            return $this->respond($response,  ResponseInterface::HTTP_OK);

        }
    }
}
