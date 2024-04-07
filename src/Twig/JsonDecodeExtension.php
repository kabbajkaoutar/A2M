<?php
// src/Twig/JsonDecodeExtension.php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class JsonDecodeExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('json_decode', [$this, 'jsonDecode']),
            new TwigFilter('int', [$this, 'toInt']),
            new TwigFilter('float', [$this, 'toFloat']),
            new TwigFilter('string', [$this, 'toString']),
            new TwigFilter('bool', [$this, 'toBool']),
            new TwigFilter('array', [$this, 'toArray']),
            new TwigFilter('object', [$this, 'toObject']),
        ];
    }

    public function jsonDecode($jsonString)
    {
        return json_decode($jsonString, true);
    }

    public function toInt($value)
    {
        return (int) $value;
    }

    public function toFloat($value)
    {
        return (float) $value;
    }

    public function toString($value)
    {
        return (string) $value;
    }

    public function toBool($value)
    {
        return (bool) $value;
    }

    public function toArray($value)
    {
        if (is_array($value)) {
            return $value;
        } elseif ($value instanceof \Traversable) {
            return iterator_to_array($value);
        } else {
            return (array) $value;
        }
    }

    public function toObject($value)
    {
        if (is_object($value)) {
            return $value;
        } else {
            return (object) $value;
        }
    }
}





?>