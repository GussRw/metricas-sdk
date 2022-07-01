<?php

namespace Plerk\Metricas;

use GuzzleHttp\Client;

class ApiResource
{
    public $client;
    private static $instances = [];

    public function __construct()
    {
        if (env('METRICAS_ENV') == 'production') {
            $base_uri = 'https://api.metricas.io/v1/';
        } else {
            $base_uri = 'https://sandbox-api.metricas.io/v1/';
        }

        $this->client = new Client([
            'base_uri' => $base_uri,
            'timeout' => 30.0,
        ]);
    }

    private static function getInstance(): ApiResource
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public static function post($uri, $json, Authentication $authentication = null)
    {
        $apiResource = ApiResource::getInstance();
        $response = $apiResource->client->request(
            'POST',
            $uri,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    ...($authentication ? ['Authorization' => "Bearer {$authentication->token}"] : [])
                ],
                'json' => $json
            ]
        );

        return $apiResource->processResponse($response);
    }

    public static function put($uri, $json, Authentication $authentication = null)
    {
        $apiResource = ApiResource::getInstance();
        $response = $apiResource->client->request(
            'PUT',
            $uri,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    ...($authentication ? ['Authorization' => "Bearer {$authentication->token}"] : [])
                ],
                'json' => $json
            ]
        );
        return $apiResource->processResponse($response);
    }

    public static function get($uri, $query, $authentication)
    {
        $apiResource = ApiResource::getInstance();
        $response = $apiResource->client->request(
            'GET',
            $uri,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    ...($authentication ? ['Authorization' => "Bearer {$authentication->token}"] : [])

                ],
                'query' => $query
            ]
        );

        return $apiResource->processResponse($response);
    }

    private function processResponse($response)
    {
        $response = json_decode($response->getBody()->getContents(), true);

        if (getenv('METRICAS_RESPONSE', 'data') && isset($response[getenv('METRICAS_RESPONSE', 'data')])) {
            return $response[getenv('METRICAS_RESPONSE', 'data')];
        } else {
            return $response;
        }
    }

    public static function returnOriginalResponse(): bool
    {
        $response_key = getenv('METRICAS_RESPONSE', 'data');
        return ($response_key === null || strtolower($response_key === null) != 'null');
    }
}
