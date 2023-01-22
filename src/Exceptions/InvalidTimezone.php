<?php

namespace Brainlet\LaravelConvertTimezone\Exceptions;

use InvalidArgumentException;

class InvalidTimezone extends InvalidArgumentException
{
    public static function make(string $timezone): self
    {
        return new static("The timezone `{$timezone}` is invalid.");
    }
}
