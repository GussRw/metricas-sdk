<?php

namespace Plerk\Metricas\Catalogs;

use Plerk\Metricas\ApiResource;
use Plerk\Metricas\Authentication;
use Plerk\Metricas\MetricasObject;

class MetricasCatalog extends MetricasObject
{
    protected $uri;
    protected $key;


    public static function all(): array
    {
        $catalog = new static;
        $authentication = Authentication::login();
        $response = ApiResource::get($catalog->uri, [], $authentication);
        return $response[$catalog->key];
    }
}
