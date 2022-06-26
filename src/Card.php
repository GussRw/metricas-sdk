<?php

namespace Plerk\Metricas;

class Card extends MetricasObject
{
    public string $id;
    public string $masked_card_number;
    public string $type;
    public string $status;
    public array $activity;
    public string $card_number;

    public static function find($card_number): Card
    {
        $authentication = Authentication::login();
        $response = ApiResource::get('cards/info', [
            'card_number' => Client::encryptForGET($card_number)
        ], $authentication);
        return new Card($response['card']);
    }
}
