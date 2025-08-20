<?php

namespace Flinty916\LaravelSalesforce\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class SalesforceAuthenticationException extends Exception
{

    private string $description;
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link https://php.net/manual/en/exception.construct.php
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param null|Throwable $previous [optional] The previous throwable used for the exception chaining.
     */
    public function __construct(
        $message = "",
        $description = "",
        $code = 0,
        $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->description = $description;
    }

    public function report() {}

    public function render($request)
    {
        Log::error("Salesforce Authentication Error: {$this->message}");
        if ($request->expectsJson())
            return response()->json(["error" => true, "message" => $this->getMessage(), "description" => $this->description], $this->code);
        else
            return abort($this->code, 'Salesforce Authentication Exception');
    }
}
