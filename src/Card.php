<?php

namespace Plerk\Metricas;

class Card extends MetricasObject
{
    protected $requires_auth = true;

    public const TEMPORARY_INACTIVE = "TEMPORARY_INACTIVE";
    public const ACTIVE = "ACTIVE";

    public string $id;
    public string $masked_card_number;
    public string $type;
    public string $status;
    public array $activity;
    public string $card_number;
    public float $available;
    public array $movements = [];
    public bool $validated = false;
    public string $pin;

    public static function find($card_number): Card
    {
        $authentication = Authentication::login();
        $response = ApiResource::get('cards/info', [
            'card_number' => Client::encryptForURL($card_number)
        ], $authentication);
        $card = new Card($response['card']);
        $card->card_number = $card_number;

        return $card;
    }

    public function assignToCardHolder(Cardholder $cardholder, $data)
    {
        $response = ApiResource::put('cards', [
            "card_number" => Client::encryptForPOST($this->card_number),
            "cardholder_id" => $cardholder->id,
            "document_type" => $data["document_type"] ?? null,
            "document_number" => $data["document_number"] ?? null,
            "observations" => $data["observations"] ?? null,
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);
        $this->fill($response['card']);

        return $this;
    }

    public function activate()
    {
        $response = ApiResource::post('cards/activate', [
            "card_number" => Client::encryptForPOST($this->card_number),
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);
        $this->fill($response['card']);

        return $this;
    }

    public function setStatus(string $status)
    {
        $response = ApiResource::put('cards/status', [
            "card_number" => Client::encryptForPOST($this->card_number),
            "status" => $status,
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);
        $this->fill($response['card']);

        return $this;
    }

    public function loadBalance(bool $with_movements = false)
    {
        $response = ApiResource::get('cards/balance', [
            "card_number" => Client::encryptForPOST($this->card_number),
            "movements" => var_export($with_movements, 1)
        ], $this->authentication);
        $this->fill($response['card']);

        $this->available = $response['available'] ?? null;
        $this->movements = $with_movements && isset($response['movements']) ? $response['movements'] : [];

        return $this;
    }

    public function authenticate(string $authentication_info)
    {
        $response = ApiResource::get('cards/authenticate', [
            "card_number" => $this->id,
            "authentication_info" => $authentication_info
        ], $this->authentication);
        $this->validated = $response['validated'];

        return $this;
    }

    public function setATMPin(string $pin)
    {
        $response = ApiResource::post('cards/pin', [
            "card_number" => $this->id,
            "pin" => Client::encryptForPOST($pin),
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);
        $this->fill($response['card']);

        return $this;
    }

    public function loadPOSPin()
    {
        $response = ApiResource::get('cards/pin/pos', [
            "card_number" => $this->id
        ], $this->authentication);
        $this->fill($response['card']);

        return $this;
    }


    public function updateATMPin(string $old_pin, string $new_pin)
    {
        $response = ApiResource::put('cards/pin', [
            "card_number" => $this->id,
            "old_pin" => Client::encryptForPOST($old_pin),
            "new_pin" => Client::encryptForPOST($new_pin),
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);
        $this->fill($response['card']);

        return $this;
    }
}
