<?php


namespace Digikraaft\ReviewRating\Exceptions;

use Exception;

class InvalidDate extends Exception
{
    public static function from(): self
    {
        return new self("The from date date cannot be greater than the to that.
        A valid from date must not be later than the to date");
    }
}
