<?php
namespace AppBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CustomerCreate
{
    protected $shopUrl;

    public function __construct($shopUrl)
    {
        $this->shopUrl = $shopUrl;
    }
    public function create($email, $password, $firstname, $lastname)
    {
        $userData = \json_encode(
            [
                'customer' => [
                    'email' => $email,
                    'firstname' => $firstname,
                    'lastname' => $lastname
                ],
                'password' => $password
            ]
        );

        $request = new \GuzzleHttp\Psr7\Request(
            'POST',
            $this->shopUrl . 'rest/V1/customers',
            [
                "Content-Type" => "application/json",
                "Content-Lenght" => strlen(json_encode($userData))
            ],
            $userData
        );

        $response = $this->request($request);

        return $response;
    }

    private function request($request)
    {
        $client = new Client();
        try {
            $response = $client->send($request);
        }catch (RequestException $e){
            $responseData = \GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents());
            if(property_exists($responseData, 'message')) {
                return $responseData->message;
            }else{
                return 'An Error occured';
            }
        }

        return \GuzzleHttp\json_decode($response->getBody()->getContents());
    }
}