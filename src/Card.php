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
    public string $cvc;
    public string $exp_year;
    public string $exp_month;
    public Operation $operation;
    public Account $account;
    public Cardholder $cardholder;

    public static function find($card_number): Card|array
    {
        $card = self::findById(Client::encryptForURL($card_number));
        return $card;
    }

    public static function findById($card_id): Card|array
    {
        $authentication = Authentication::login();
        $response = ApiResource::get('cards/info', [
            'card_number' => $card_id
        ], $authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        $card = new Card($response['card']);
        $card->account = new Account($response['account']);
        $card->cardholder = new Cardholder($response['cardholder']);

        return $card;
    }


    public function activate()
    {
        $response = ApiResource::post('cards/activate', [
            "card_number" => $this->id,
            "latitude" => 19.4295267,
            "longitude" => -99.2075014
        ], $this->authentication);


        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        $this->fill($response['card']);
        $this->account = new Account($response['account']);

        return $this;
    }

    public function setStatus(string $status)
    {
        $response = ApiResource::put('cards/status', [
            "card_number" => $this->id,
            "status" => $status,
            "latitude" => 19.4295267,
            "longitude" => -99.2075014
        ], $this->authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

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

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

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

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }


        $this->validated = $response['validated'];

        return $this;
    }

    public function makeDisbursement(float $amount): Card|array
    {
        $response = ApiResource::post('cards/disbursement', [
            "card_number" => $this->id,
            "amount" => $amount,
            "latitude" => 19.4295267,
            "longitude" => -99.2075014
        ], $this->authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        $this->fill($response['card']);
        $this->operation = new Operation($response['operation']);
        $this->account = new Account($response['account']);

        return $this;
    }

    public function makePayment(float $amount, string $payment_code): Card|array
    {
        $response = ApiResource::post('cards/payment', [
            "card_number" => $this->id,
            "amount" => $amount,
            "payment_code" => $payment_code,
            "latitude" => 19.4295267,
            "longitude" => -99.2075014
        ], $this->authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        $this->fill($response['card']);
        $this->operation = new Operation($response['operation']);
        $this->account = new Account($response['account']);

        return $this;
    }

    public function makeWithdrawal(float $amount, string $pin = null): Card|array
    {
        $response = ApiResource::post('cards/withdrawal', [
            "card_number" => $this->id,
            "amount" => $amount,
            "pin" => $pin ? Client::encryptForPOST($pin) : null,
            "latitude" => 19.4295267,
            "longitude" => -99.2075014
        ], $this->authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        $this->fill($response['card']);
        $this->operation = new Operation($response['operation']);
        $this->account = new Account($response['account']);

        return $this;
    }

    public function applyPurchase(float $amount, string $charge_code, string $pin = null): Card|array
    {
        $response = ApiResource::post('cards/purchase', [
            "card_number" => $this->id,
            "amount" => $amount,
            "charge_code" => $charge_code,
            "pin" => $pin ? Client::encryptForPOST($pin) : null,
            "latitude" => 19.4295267,
            "longitude" => -99.2075014
        ], $this->authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

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

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        $this->fill($response['card']);
        $this->account = new Account($response['account']);
        $this->movements = isset($response['movements']) ? $response['movements'] : [];

        return $this;
    }


    public static function makeTransfer(string $origin_card, string $destiny_card, float $amount): Operation|array
    {
        $authentication = Authentication::login();

        $response = ApiResource::post('cards/transfer', [
            "origin_card" => Client::encryptForPOST($origin_card),
            "destination_card" => Client::encryptForPOST($destiny_card),
            "amount" => $amount,
            "latitude" => 19.4295267,
            "longitude" => -99.2075014
        ], $authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        return new Operation($response['operation']);
    }

    public static function createPhysicalCard(string $card_number, array $delivery_data): Card|array
    {
        $authentication = Authentication::login();

        $response = ApiResource::put('cards/virtual', [
            "card_number" => Client::encryptForPOST($card_number),
            "delivery_street" => $delivery_data['delivery_street'],
            "delivery_street_number" => $delivery_data['delivery_street_number'],
            "delivery_township" => $delivery_data['delivery_township'],
            "delivery_state" => $delivery_data['delivery_state'],
            "delivery_postal_code" => $delivery_data['delivery_postal_code'],
            "latitude" => 19.4295267,
            "longitude" => -99.2075014
        ], $authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        $card = new Card($response['card']);
        $card->account = new Account($response['account']);
        return $card;
    }

    public static function createVirtualCardCVC(string $card_number, string $pin): Card|array
    {
        $authentication = Authentication::login();

        $response = ApiResource::get('cards/virtual/cvc', [
            "card_number" => Client::encryptForURL($card_number),
            "pin" => Client::encryptForURL($pin),
            "latitude" => 19.4295267,
            "longitude" => -99.2075014
        ], $authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        $card = new Card($response['card']);
        $card->account = new Account($response['account']);
        return $card;
    }
}
