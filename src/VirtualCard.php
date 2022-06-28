<?php

namespace Plerk\Metricas;

class VirtualCard extends Card
{
    public string $product;
    public string $cardholder_id;
    public string $document_type;
    public string $document_number;
    public string $observations;

    public function save()
    {
        $response = ApiResource::post('cards/virtual', [
            "product" => $this->product,
            "cardholder_id" => $this->cardholder_id,
            "document_type" => $this->document_type,
            "document_number" => $this->document_number,
            "observations" => $this->observations,
        ], $this->authentication);

        $this->fill($response["card"]);
        $this->cardholder = new Cardholder($response['cardholder']);
        $this->account = new Account($response['account']);

    }
}
