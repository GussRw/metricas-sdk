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


        if (ApiResource::returnOriginalResponse()) {
            return $response['data']['system']['public_key'];
        }


        return $response['system']['public_key'];
    }

    public static function addPublicKey($public_key)
    {
        $authentication = Authentication::login();
        $response = ApiResource::post('clients/api_key/public_key', [
            "public_key" => $public_key
        ], $authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        return $response['api_key']['public_key'];
    }

    public static function encryptForURL($data)
    {
        $encripted = self::encryptForPOST($data);
        return rtrim(strtr($encripted, '+/', '-_'), '=');
    }

    public static function base64URLDecode($encrypted)
    {
        return strtr($encrypted, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($encrypted)) % 4);
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

    public static function decrypt($data)
    {
        $fp = fopen(getenv("METRICAS_PRIVATE_KEY_PATH"), "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);
        $private_key = openssl_get_privatekey($priv_key, getenv("METRICAS_PRIVATE_KEY_PASSWORD"));

        $decrypted_data = "";
        openssl_private_decrypt(
            base64_decode($data),
            $decrypted_data,
            $private_key,
            OPENSSL_PKCS1_PADDING
        );
        return $decrypted_data;
    }
}
