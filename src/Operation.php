<?php

namespace Plerk\Metricas;

class Operation extends MetricasObject
{
    public string $id;
    public string $authorization_code;
    public float $amount;
    public string $charge_code;
    public string $charge_description;

}
