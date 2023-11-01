<?php
namespace App\Services;


use function GuzzleHttp\Psr7\parse_request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\EventListener\ValidateRequestListener;

class EmbedService extends BaseService
{
    public function __construct(SessionInterface $session)
    {
        $requestParseList = [];
        parent::__construct("", "", $session, $requestParseList);
    }

    public function embedAuthentication($token){

        $response = $this->apiClient->request("POST", "authentication/checker", [
            'Authorization' => "Bearer {$token}"
        ], []);
        return $response;
    }
}