<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class BaseService
 * @package App\Services
 */
class BaseService
{
    /**
     * @var ApiClient
     */
    public $apiClient;

    /**
     * @var
     */
    public $apiRoute;

    /**
     * @var
     */
    private $contentType;

    /**
     * @var SessionInterface
     */
    public $session;

    /** @var $sendMsg */
    public $sendMsg = true;

    /**
     * @var
     */
    protected $createdMsg = "Successfully Created!";

    /**
     * @var
     */
    protected $updatedMsg = "Successfully Updated!";

    /**
     * @var
     */
    protected $deletedMsg = "Successfully Deleted!";

    /**
     * @var array
     */
    protected $requestParseList = [];


    /**
     * BaseService constructor.
     * @param $apiRoute
     * @param $contentType
     * @param SessionInterface $session
     * @param array $requestParseList
     */
    public function __construct($apiRoute, $contentType, SessionInterface $session, array $requestParseList = [])
    {
        $this->apiRoute = $apiRoute;
        $this->contentType = $contentType;
        $this->apiClient = new ApiClient();

        if (isset($_SESSION)) {
            $this->apiClient->accessToken = array_key_exists("api_token", $_SESSION) ? $_SESSION["api_token"] : "";
            $this->session = $session;
        }

        $this->requestParseList = $requestParseList;

        $this->createdMsg = "{$this->contentType} {$this->createdMsg}";
        $this->updatedMsg = "{$this->contentType} {$this->updatedMsg}";
        $this->deletedMsg = "{$this->contentType} {$this->deletedMsg}";

    }

    /**
     * @param $response
     * @return array
     */
    public function prepareListResponse($response)
    {
        $defaultResponse = [
            "hydra:member" => [],
            "hydra:view" => []
        ];
        $response = (is_array($response)) ? $response : [];
        return array_merge($defaultResponse, $response);
    }


    /**
     * @param $collection
     * @param $requiredFields
     * @return mixed
     */
    public function prepareResponse($collection, $requiredFields)
    {
        foreach ($collection["hydra:member"] as $key => $item) {
            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $item)) {
                    $item[$field] = "";
                }
                $collection["hydra:member"][$key] = $item;
            }
        }
        return $collection;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAll($param)
    {
        return $this->getRequest("{$this->apiRoute}?$param");
    }

    /**
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        try {
            return $this->getRequest("{$this->apiRoute}/{$id}");
        } catch (\Exception $exception) {
            if ($exception->getCode() == 401) {
                $this->session->invalidate();
            } else if ($exception->getCode() == 404) {
                throw new \Exception("Story", 404);
            }
        }

    }

    /**
     * @param $param
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function post($param)
    {
        try {
            $response = $this->postRequest($this->apiRoute, $param);
            return $response;
        } catch (\Exception $exception) {
            if ($exception->getCode() == 401) {
                $this->session->invalidate();
            }
        }
    }

    /**
     * @param $id
     * @param $param
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function patch($id, $param)
    {
        try {
            $response = $this->patchRequest("{$this->apiRoute}/{$id}", $param);
            return $response;
        } catch (\Exception $exception) {
            if ($exception->getCode() == 401) {
                $this->session->invalidate();
            }
        }
    }

    /**
     * @param $id
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete($id)
    {
        try {
            $response = $this->deleteRequest("{$this->apiRoute}/{$id}");
            return $response;
        } catch (\Exception $exception) {
            if ($exception->getCode() == 401) {
                $this->session->invalidate();
            }
        }
    }

    public function galleryUpload($url, $localImagePath, $localImageName, $headers = [])
    {
        return $this->apiClient->galleryUpload($url, $localImagePath, $localImageName, $headers);
    }


    /**
     * @param $url
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function getRequest($url)
    {
        $response = $this->apiClient->request("GET", $url);

        if ($response['status']) {
            return $response["content"];
        } else {
            return $this->errorHandler($response);
        }
    }

    /**
     * @param $url
     * @param $param
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function postRequest($url, $param)
    {

        $response = $this->apiClient->request("POST", $url, [], $param);
        if ($response['status']) {
            return $response["content"];
        }
        else {
            return $response;
        }
    }

    /**
     * @param $url
     * @param $param
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function patchRequest($url, $param)
    {

        $response = $this->apiClient->request("PATCH", $url, [], $param);
        if ($response['status']) {
            if ($this->sendMsg) {
                $this->session->getFlashBag()->add("success", $this->updatedMsg);
            }
            return $response["content"];
        } else {
            return $this->errorHandler($response);
        }
    }

    /**
     * @param $url
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteRequest($url)
    {
        $response = $this->apiClient->request("DELETE", $url);
        if ($response['status']) {
            if ($this->sendMsg) {
                $this->session->getFlashBag()->add("delete", $this->deletedMsg);
            }
            return $response["content"];
        } else {
            return $this->errorHandler($response);
        }
    }

    /**
     * @param $params
     * @return mixed
     */
    public function parseValues($params)
    {
        foreach ($this->requestParseList as $contentType => $contentParam) {
            foreach ($params as $key => $value) {

                if (in_array($key, $contentParam)) {
                    if ($contentType == "int") {
                        $params[$key] = filter_var($value, FILTER_VALIDATE_INT);
                    }
                    if ($contentType == "float") {
                        $params[$key] = filter_var($value, FILTER_VALIDATE_FLOAT);
                    }
                    if ($contentType == "boolean") {
                        $params[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }

                }

            }
        }
        return $params;
    }

}