<?php

namespace Plerk\Metricas;

class Authentication extends MetricasObject
{
    private static $instances = [];

    public $id;
    public $token;
    public $alias;
    public $name;


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

            if (env('METRICAS_RESPONSE', 'data') === null) {
                $authentication->fill($response["data"]["client"]);
            } else {
                $authentication->fill($response["client"]);
            }
        }
        return $authentication;
    }
}
