<?php

namespace Plerk\Metricas;

trait VirtualCard
{
    public string $product;
    public string $cardholder_id;
    public string $document_type;
    public string $document_number;
    public string $observations;

    public function createVirtualCard()
    {
        $response = ApiResource::post('cards/virtual', [
            "product" => $this->product,
            "cardholder_id" => $this->cardholder_id,
            "document_type" => $this->document_type,
            "document_number" => $this->document_number,
            "observations" => $this->observations,
            "latitude" => 19.4295267,
            "longitude" => -99.2075014
        ], $this->authentication);

        if (ApiResource::returnOriginalResponse()) {
            return $response;
        }

        $this->fill($response["card"]);
        $this->cardholder = new Cardholder($response['cardholder']);
        $this->account = new Account($response['account']);
    }
}
