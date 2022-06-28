<?php

namespace Plerk\Metricas\Catalogs;

use Plerk\Metricas\ApiResource;
use Plerk\Metricas\Authentication;
use Plerk\Metricas\MetricasObject;

class MetricasCatalog extends MetricasObject
{
    protected static $uri;
    protected static $key;


    public static function all(): array
    {
        $authentication = Authentication::login();
        $response = ApiResource::get(static::$uri, [], $authentication);
        return $response[static::$key];
    }
}
