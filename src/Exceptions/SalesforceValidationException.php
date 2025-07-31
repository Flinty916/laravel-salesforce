<?php

namespace Flinty916\LaravelSalesforce\Exceptions;

use Exception;
use __IDE\LanguageLevelTypeAware;
use __IDE\Pure;

class SalesforceValidationException extends Exception
{

    private array $fields = [];

    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link https://php.net/manual/en/exception.construct.php
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param null|Throwable $previous [optional] The previous throwable used for the exception chaining.
     */
    public function __construct(
        $message = "",
        $fields = [],
        $code = 0,
        $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->fields = $fields;
    }

    public function render($request)
    {
        return response()->json(["error" => true, "message" => $this->getMessage(), "fields" => $this->fields]);
    }
}
