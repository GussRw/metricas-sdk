<?php

namespace Plerk\Metricas;

class Cardholder extends MetricasObject
{
    protected $requires_auth = true;

    public $id;
    public $name;
    public $first_name;
    public $second_name;
    public $surname;
    public $second_surname;
    public $rfc;
    public $curp;
    public $email;
    public $phone;
    public $primary_phone;
    public $mobile_phone;
    public $street;
    public $ext_street_number;
    public $int_street_number;
    public $suburb;
    public $township;
    public $city;
    public $state;
    public $postal_code;
    public $birthdate;
    public $gender;
    public $marital_status;
    public $has_kids;
    public $gross_monthly_income;
    public $net_monthly_income;
    public $observations;
    public $document;

    public function save()
    {
        $response = ApiResource::post('cardholders', $this->toArray(), $this->authentication);
        $this->fill($response["cardholder"]);
    }

    public static function find($id): Cardholder
    {
        return self::retrieve([
            'id' => $id
        ]);
    }

    public static function retrieve($query): Cardholder
    {
        $authentication = Authentication::login();
        $response = ApiResource::get('cardholders', $query, $authentication);
        return new Cardholder($response['cardholder']);
    }
}
