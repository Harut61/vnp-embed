<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiClient
{

    public $client;

    public $host;

    public $userName;

    public $passWord;

    public $accessToken;

    public $authResponse;

    public function __construct($host = null)
    {
        $this->client = new Client();
        $this->host = ($host) ? $host : $_ENV["API_ENTRYPOINT"];
    }


    /**
     * Create AccessToken with given credentials
     * @param string $username
     * @param string $password
     * @return null|string
     */
    public function createAccessToken($username, $password)
    {
        $this->userName = $username;
        $this->passWord = $password;

        $response = $this->client->request('POST',
            "{$this->host}authentication_token",
            ['json' =>
                [
                    "email" => $this->userName,
                    "password" => $this->passWord
                ]
            ]);

        if (200 !== $response->getStatusCode()) {
            return null;
        }

        $this->authResponse = $response->getBody()->getContents();
        $this->updateAccessToken();


        return $this->authResponse;
    }

    /**
     * Create AccessToken with given credentials
     * @param string $username
     * @param string $password
     * @return null|string
     */
    public function refreshToken($refreshToken)
    {

        $response = $this->client->request('POST',
            "{$this->host}/token/refresh",
            ['json' =>
                [
                    "refresh_token" => $refreshToken
                ]
            ]);

        if (200 !== $response->getStatusCode()) {
            return null;
        }

        $this->authResponse = $response->getBody()->getContents();

        $this->updateAccessToken();


        return $this->authResponse;
    }

    /**
     *  Update AccessToken and RefreshToken value on create or requesting new token
     */
    public function updateAccessToken()
    {
        if (json_decode($this->authResponse) != null) {
            $content = json_decode($this->authResponse, true);
            $this->accessToken = $content["token"];
            $this->refreshToken = $content["refresh_token"];
        }
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return [
            'Content-type' => 'application/json'

        ];
    }


    public function request($method, $url, $headers = [], $body = [])
    {


        try {

//            $headers = array_merge($this->getHeaders(), $headers);
//            dd($headers);
            if(strtoupper($method ) === "PATCH"){
                $headers['Content-type'] = 'application/merge-patch+json';
            }

            $response = $this->client->request($method,
                "{$this->host}{$url}",
                ['json' =>
                    $body,
                    'headers' => $headers
                ]);

            $jsonData = $response->getBody()->getContents();

            return [
                "status" => true,
                "code" => $response->getStatusCode(),
                "content" => json_decode($jsonData, true)
            ];

        } catch (RequestException $exception) {


            if ($exception->getCode() == 401) {
                return [
                    "status" => false,
                    "code" => $exception->getCode(),
                    "content" => ''
                ];


            } else if ($exception->getCode() == 404) {

                    throw new \Exception("Route Not Found", 404);

            }
            else {
                $errorMsg = json_decode($exception->getResponse()->getBody()->getContents(), true);

//                dd($exception->getResponse()->getBody()->getContents());

                if(is_array($errorMsg)){
                        $errorMsg = (array_key_exists("violations", $errorMsg)) ? $errorMsg["violations"] : $errorMsg;
                }

                return [
                    "status" => false,
                    "code" => $exception->getCode(),
                    "content" => $errorMsg
                ];
            }
        }

    }


    public function patch($url, $headers = [], $body)
    {

        try {

            $headers = array_merge([
                'Content-type' => 'application/merge-patch+json',
                'Authorization' => "Bearer {$this->accessToken}"
            ],
                $headers);
            $response = $this->client->patch(
                "{$this->host}{$url}",
                [
                    "json" => $body,
                    "headers" => $headers
                ]
            );

            $jsonData = $response->getBody()->getContents();
            return [
                "status" => true,
                "code" => $response->getStatusCode(),
                "content" => json_decode($jsonData, true)
            ];

        } catch (RequestException $exception) {
            dd($exception->getResponse()->getBody()->getContents());
            if ($exception->getCode() == 401) {
                $getUser = [];

                if (array_key_exists('refresh_token', $_COOKIE)) {
                    $getUser = $this->getAuth($_COOKIE['refresh_token']);
                }

                if (!empty($getUser["token"])) {
                    $this->accessToken = $getUser["token"];
                    $this->setCookie($getUser["refresh_token"]);
                } else {
                    throw new \Exception("unauthenticated", 401);
                }

            } else {
                $errorMsg = json_decode($exception->getResponse()->getBody()->getContents(), true);

                return [
                    "status" => false,
                    "code" => $exception->getCode(),
                    "content" => (array_key_exists("violations", $errorMsg)) ? $errorMsg["violations"] : $errorMsg
                ];
            }
        }
    }

    public function setCookie($refreshToken)
    {
        $cookie = new Cookie('refresh_token', $refreshToken, strtotime('now + 1 month'));

        $responce = new Response();
        $responce->headers->setCookie($cookie);
        $responce->sendHeaders();
        return $cookie;
    }

    public function getAuth($post)
    {

        $client = new \GuzzleHttp\Client(["base_uri" => $this->host]);
        $options = [
            'form_params' => [
                "refresh_token" => $post
            ]
        ];
        $response = $client->post("{$this->host}token/refresh", $options);

        if ($response->getStatusCode() == 200) {

            $this->authResponse = $response->getBody()->getContents();
            $this->updateAccessToken();
            $tokens = json_decode($response->getBody()->getContents(), true);
            return $tokens;
        }

        return $response->getStatusCode();
    }

    public function galleryUpload($url,  $localImagePath, $localImageName, $headers = [])
    {
        $headers = array_merge(['Authorization' => "Bearer {$this->accessToken}"], $headers);

        $options = [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($localImagePath, 'r'),
                    'filename' => $localImageName
                ]
            ],
            'headers' => $headers
        ];
        $response = $this->client->request("POST",
            "{$this->host}{$url}",
           $options);
        $jsonData = $response->getBody()->getContents();
        return [
            "status" => true,
            "code" => $response->getStatusCode(),
            "content" => json_decode($jsonData, true)
        ];
    }
}