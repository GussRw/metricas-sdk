<?php

namespace Plerk\Metricas;

class Card extends MetricasObject
{
    use PhysicalCard;
    use VirtualCard;

    protected $requires_auth = true;

    public const TEMPORARY_INACTIVE = "TEMPORARY_INACTIVE";
    public const ACTIVE = "ACTIVE";
    public const NOT_ASSIGNED = "NOT_ASSIGNED";
    public const NEED_ACTIVATION = "NEED_ACTIVATION";
    public const VIRTUAL = "VIRTUAL";
    public const PHYSICAL = "PHYSICAL";

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
    public Operation $operation;
    public Account $account;
    public Cardholder $cardholder;

    public static function find($card_number): Card
    {
        $authentication = Authentication::login();
        $response = ApiResource::get('cards/info', [
            'card_number' => Client::encryptForURL($card_number)
        ], $authentication);

        $card = new Card($response['card']);
        $card->account = new Account($response['account']);
        $card->cardholder = new Cardholder($response['cardholder']);

        return $card;
    }


    public function activate()
    {
        $response = ApiResource::post('cards/activate', [
            "card_number" => $this->id,
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);

        $this->fill($response['card']);
        $this->account = new Account($response['account']);

        return $this;
    }

    public function setStatus(string $status)
    {
        $response = ApiResource::put('cards/status', [
            "card_number" => $this->id,
            "status" => $status,
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);

        $this->fill($response['card']);
        $this->account = new Account($response['account']);

        return $this;
    }

    public function loadBalance(bool $with_movements = false)
    {
        $response = ApiResource::get('cards/balance', [
            "card_number" => $this->id,
            "movements" => var_export($with_movements, 1)
        ], $this->authentication);
        $this->fill($response['card']);

        $this->available = $response['available'] ?? null;
        $this->movements = $with_movements && isset($response['movements']) ? $response['movements'] : [];
        $this->account = new Account($response['account']);

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

    public function makeDisbursement(float $amount): Card
    {
        $response = ApiResource::post('cards/disbursement', [
            "card_number" => $this->id,
            "amount" => $amount,
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);

        $this->fill($response['card']);
        $this->operation = new Operation($response['operation']);
        $this->account = new Account($response['account']);

        return $this;
    }

    public function makePayment(float $amount, string $payment_code): Card
    {
        $response = ApiResource::post('cards/payment', [
            "card_number" => $this->id,
            "amount" => $amount,
            "payment_code" => $payment_code,
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);

        $this->fill($response['card']);
        $this->operation = new Operation($response['operation']);
        $this->account = new Account($response['account']);

        return $this;
    }

    public function makeWithdrawal(float $amount, string $pin = null): Card
    {
        $response = ApiResource::post('cards/withdrawal', [
            "card_number" => $this->id,
            "amount" => $amount,
            "pin" => $pin ? Client::encryptForPOST($pin) : null,
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);

        $this->fill($response['card']);
        $this->operation = new Operation($response['operation']);
        $this->account = new Account($response['account']);

        return $this;
    }

    public function applyPurchase(float $amount, string $charge_code, string $pin = null): Card
    {
        $response = ApiResource::post('cards/purchase', [
            "card_number" => $this->id,
            "amount" => $amount,
            "charge_code" => $charge_code,
            "pin" => $pin ? Client::encryptForPOST($pin) : null,
            "latitude" => 12.65343,
            "longitude" => -134.87536
        ], $this->authentication);

        $this->fill($response['card']);
        $this->operation = new Operation($response['operation']);
        $this->account = new Account($response['account']);

        return $this;
    }


    public function loadMovements(string $initial_date, string $end_date)
    {
        $response = ApiResource::get('cards/movements', [
            "card_number" => $this->id,
            "initial_date" => $initial_date,
            "end_date" => $end_date,
        ], $this->authentication);

        $this->fill($response['card']);
        $this->account = new Account($response['account']);
        $this->movements = isset($response['movements']) ? $response['movements'] : [];

        return $this;
    }
}
