<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;


use App\Models\UrlModel;
use App\Models\UrlAnalyticsModel;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\returnSelf;

class UrlController extends ResourceController
{

    public function test()
    {
        return view('welcome_message.php');
    }
    public function retrieveLongUrl($data)
    {
        $request = service('request');
        $urlModel = new UrlModel();
        $urlAnalytics = new UrlAnalyticsModel();

        if ($request->getMethod() === 'get') {
            // Validate the shortcode before proceeding with other operations

            $result = $urlModel->where("shortened_code", trim($data))->first();
            $typeCastIsRovoked = (bool) $result['isRevoked'];

            if ($result === null || $typeCastIsRovoked === true) {
                // It means the short code does not exist or has been revoked. Therefore, send the error page
                return view("error_page.php");

                // Please note that you should return a json here instead of the errror page
            }

            // Retreive IP address from the request  
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
            //whether ip is from the proxy  
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            //whether ip is from the remote address  
            else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            // Validate IP Address
            if (filter_var($ip, FILTER_VALIDATE_IP)) {

                // Retrieve Browser and OS
                $agent = $this->request->getUserAgent();
                if ($agent->isBrowser()) {
                    $currentAgent = $agent->getBrowser() . ' ' . $agent->getVersion();
                } elseif ($agent->isRobot()) {
                    $currentAgent = $agent->getRobot();
                } elseif ($agent->isMobile()) {
                    $currentAgent = $agent->getMobile();
                } else {
                    $currentAgent = 'Unidentified User Agent';
                }

                // Make a request to the Geolocation API using the IP address from above using curl
                $ch = curl_init();

                $url = 'http://ip-api.com/json/' . $ip;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $apiResponse = curl_exec($ch);
                if (curl_errno($ch)) {
                    // Handle curl error
                    echo 'Curl error: ' . curl_error($ch);
                } else {
                    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if ($httpStatus === 200 && json_decode($apiResponse)->status === "success") {
                        // Convert the JSON response to an object
                        $responseObject = json_decode($apiResponse);

                        // Increment the click count for this shortened url
                        $urldata = [
                            'clicks' => $result['clicks'] + 1
                        ];

                        $urlModel->update($result['id'], $urldata);

                        $data = [
                            'user_ip_adr' => $ip,
                            'url_id' => $result['id'],
                            'country' => $responseObject->country,
                            'lat' => $responseObject->lat,
                            'long' => $responseObject->lon,
                            'timezone' => $responseObject->timezone,
                            'isp' => $responseObject->isp,
                            'browser' => $currentAgent,
                            'platform' => $agent->getPlatform()
                        ];

                        $urlAnalytics->insert($data);

                        $response = [
                            'status' => 200,
                            'message' => 'Sucessful',
                            'error' => false,
                            'data' => [
                                'original_url' => $result
                            ]
                        ];

                        return $this->respond($response,  ResponseInterface::HTTP_CREATED);
                    } else {
                        echo 'API request failed with HTTP status code: ' . $httpStatus;
                    }
                }
                // Close curl session
                curl_close($ch);
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'Invalid IP address',
                    'error' => true,
                    'data' => []
                ];
                // Invalid
                return $this->respond($response,  ResponseInterface::HTTP_BAD_REQUEST);
            }
        }
    }

    public function create_shorten_url()
    {
        $request = service('request');
        $urlModel = new UrlModel();
        helper('url');

        if ($request->getMethod() === 'post') {
            $rules = [
                'original_url' => 'required|valid_url_strict[https]',
                'user_id' => 'required|numeric'
            ];

            if (!$this->validate($rules)) {
                $response = [
                    'status' => 400,
                    'message' => $this->validator->getErrors(),
                    'error' => true,
                    'data' => []
                ];
                // Invalid data
                return $this->respond($response,  ResponseInterface::HTTP_BAD_REQUEST);
            } else {

                $short_code = $this->generateRandomString($length = 8);

                $data = [
                    'original_url' => trim($this->request->getVar('original_url')),
                    'shortened_code' => $short_code,
                    'isRevoked' => false,
                    'shortened_url' => base_url() . $short_code,
                    'clicks' => 0,
                    'user_id' => $this->request->getVar('user_id')
                ];

                $insertId = $urlModel->insert($data);

                if ($insertId !== null) {

                    $insertedRecord = $urlModel->find($insertId);
                    // Return a success message
                    $response = [
                        'status' => 201,
                        'message' => 'Your url has been shortened successfully',
                        'error' => false,
                        'data' => $insertedRecord
                    ];

                    return $this->respond($response,  ResponseInterface::HTTP_CREATED);
                }
            }
        }
    }

    public function getSingleUrlAnalytics(int $url_id)
    {
        $request = service('request');
        $urlAnalyticsModel = new UrlAnalyticsModel();
        if ($request->getMethod() === 'get') {
            $result = $urlAnalyticsModel->where('url_id', $url_id)->findAll();

            // var_dump($result);
            if(!$result) {
                $response = [
                    'status' => 404,
                    'message' => 'No records found for this id!',
                    'error' => true,
                    'data' => []
                ];
                // Invalid
                return $this->respond($response,  ResponseInterface::HTTP_NOT_FOUND);
            }
            $response = [
                'status' => 200,
                'message' => 'Your request is successful',
                'error' => false,
                'data' => $result
            ];

            return $this->respond($response,  ResponseInterface::HTTP_OK);
        }
    }

    public function revokeUrl($short_code)
    {
        $request = service('request');
        if ($request->getMethod() === 'put') {
            // Validate the shortcode before proceeding with other operations
            $urlModel = new UrlModel();
            $result = $urlModel->where("shortened_code", trim($short_code))->first();
            $typeCastIsRovoked = (bool) $result['isRevoked'];

            if ($typeCastIsRovoked === true) {
                $response = [
                    'status' => 200,
                    'message' => 'Url is already revoked!',
                    'error' => false,
                    'data' => []
                ];

                return $this->respond($response,  ResponseInterface::HTTP_OK);
            } else {

                $urlModel->set('isRevoked', true)
                    ->where('id', $result['id'])
                    ->update();

                $response = [
                    'status' => 200,
                    'message' => 'Url revoked and will no longer be reachable!',
                    'error' => false,
                    'data' => []
                ];

                return $this->respond($response,  ResponseInterface::HTTP_OK);
            }
        }
    }

    public function getAllUrls(string $id)
    {
        $request = service('request');
        $urlModel = new UrlModel();

        if ($request->getMethod() === 'get') {
            $result = $urlModel->where('user_id', $id)->findAll();

            if (!$result) {
                // No records found, therefore return message
                $response = [
                    'status' => 404,
                    'message' => 'No records found for this id!',
                    'error' => true,
                    'data' => []
                ];
                // Invalid
                return $this->respond($response,  ResponseInterface::HTTP_NOT_FOUND);
            } else {
                // Records found, therefore return data 
                $response = [
                    'status' => 200,
                    'message' => 'Your request is successful',
                    'error' => false,
                    'data' => $result
                ];

                return $this->respond($response,  ResponseInterface::HTTP_OK);
            }
        }
    }

    protected function generateRandomString($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = substr(str_shuffle($characters), 0, $length);

        return $randomString;
    }
}
