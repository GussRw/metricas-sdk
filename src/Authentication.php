<?php

namespace Plerk\Metricas;

class Authentication extends MetricasObject
{
    private static $instances = [];

    public $id;
    public $status_id;
    public $client_id;
    public $jti;
    public $token;
    public $alias;
    public $name;
    public $description;
    public $created_at;
    public $updated_at;
    public $public_key;


    private static function getInstance(): Authentication
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public static function login()
    {
        $authentication = Authentication::getInstance();

        if (!$authentication->token) {
            $response = ApiResource::post(
                'auth/login',
                [
                    "api_client" => [
                        "api_key" => env("METRICAS_API_KEY"),
                        "password" => env("METRICAS_PASSWORD")
                    ]
                ]
            );
            $authentication->fill($response["client"]);
        }
        return $authentication;
    }
}
