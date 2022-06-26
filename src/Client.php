<?php

namespace Plerk\Metricas;

class Client extends MetricasObject
{
    public $id;
    public $masked_card_number;
    public $type;
    public $status;

    private static function publicKey()
    {
        $authentication = Authentication::login();
        $response = ApiResource::get('clients/api_key/public_key', [], $authentication);
        return $response['system']['public_key'];
    }

    public static function encryptForGET($data)
    {
        $encripted = self::encryptForPOST($data);
        return rtrim(strtr($encripted, '+/', '-_'), '=');
    }

    public static function encryptForPOST($data)
    {
        $public_key = self::publicKey();

        $crypted = "";
        openssl_public_encrypt(
            $data,
            $crypted,
            $public_key,
            OPENSSL_PKCS1_PADDING
        );
        return base64_encode($crypted);
    }
}
