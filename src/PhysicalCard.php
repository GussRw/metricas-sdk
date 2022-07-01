<?php

namespace Plerk\Metricas;

trait PhysicalCard
{
    public function assignToCardHolder(Cardholder $cardholder, $card_number, $data)
    {
        $response = ApiResource::put('cards', [
            "card_number" => Client::encryptForPOST($card_number),
            "cardholder_id" => $cardholder->id,
            "document_type" => $data["document_type"] ?? null,
            "document_number" => $data["document_number"] ?? null,
            "observations" => $data["observations"] ?? null,
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);

        if (getenv('METRICAS_RESPONSE', 'data') === null) {
            return $response;
        }

        $this->fill($response['card']);
        $this->account = new Account($response['account']);
        $this->cardholder = new Cardholder($response['cardholder']);

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

        if (getenv('METRICAS_RESPONSE', 'data') === null) {
            return $response;
        }

        $this->fill($response['card']);
        $this->operation = new Operation($response['operation']);
        $this->account = new Account($response['account']);

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

        if (getenv('METRICAS_RESPONSE', 'data') === null) {
            return $response;
        }

        $this->fill($response['card']);
        $this->operation = new Operation($response['operation']);
        $this->account = new Account($response['account']);

        return $this;
    }

    public function validateATMPin(string $pin)
    {
        $response = ApiResource::get('cards/pin/validate', [
            "card_number" => $this->id,
            "pin" => Client::encryptForURL($pin),
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);

        if (getenv('METRICAS_RESPONSE', 'data') === null) {
            return $response;
        }

        $this->validated = (bool) filter_var($response['validated'], FILTER_VALIDATE_BOOLEAN);

        return $this;
    }


    public function loadPOSPin()
    {
        $response = ApiResource::get('cards/pin/pos', [
            "card_number" => $this->id
        ], $this->authentication);

        if (getenv('METRICAS_RESPONSE', 'data') === null) {
            return $response;
        }

        $this->fill($response['card']);
        $this->account = new Account($response['account']);

        return $this;
    }
}
