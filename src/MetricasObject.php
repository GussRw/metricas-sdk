<?php

namespace Plerk\Metricas;

use ReflectionClass;
use ReflectionProperty;

abstract class MetricasObject
{
    public function save()
    {

    }

    static function create($data): static
    {
        $object = new static;
        $object->fill($data);
        $object->save();
        return $object;
    }

    public function fill($data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) $this->$key = $value;
        }
    }

    public function toArray()
    {
        $props = $this->getProps();
        return array_combine($props, array_map(function ($prop) {
            return $this->$prop;
        }, $props));
    }

    private function getProps()
    {
        $reflect = new ReflectionClass($this);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        return array_map(function ($prop) {
            return $prop->name;
        }, $props);
    }
}
