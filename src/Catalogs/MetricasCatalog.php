<?php

namespace Plerk\Metricas\Catalogs;

use Plerk\Metricas\ApiResource;
use Plerk\Metricas\MetricasObject;

class MetricasCatalog extends MetricasObject
{
    protected $uri;
    protected $key;

    public array $items;

    public function all(): array
    {
        $response = ApiResource::get($this->uri, [], $this->authentication);
        $this->items = $response[$this->key];
        return $this->items;
    }
}
