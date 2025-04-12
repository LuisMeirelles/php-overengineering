<?php

namespace App\Core;

use Exception;
use Throwable;

class AppException extends Exception
{
    public function __construct(
        string           $message = "An unexpected error occurred",
        protected ?array $details = null,
        ?Throwable       $previous = null
    )
    {
        parent::__construct($message, $this->getCode(), $previous);
    }

    public function handleResponse(): array
    {
        http_response_code($this->getCode());

        $response = [
            'message' => $this->getMessage(),
        ];

        $exception = $this;

        while ($previous = $exception->getPrevious()) {
            $response['previous']['message'] = $previous->getMessage();
            $response['previous']['trace'] = array_map(static function ($traceEntry) {
                $result = [];

                if (isset($traceEntry['file'], $traceEntry['line'])) {
                    $result['location'] = "$traceEntry[file]:$traceEntry[line]";
                }

                if (isset($traceEntry['function'])) {
                    if (isset($traceEntry['class'])) {
                        $result['function'] = $traceEntry['class'] . '::' . $traceEntry['function'];
                    } else {
                        $result['function'] = $traceEntry['function'];
                    }

                    $result['args'] = $traceEntry['args'];
                }

                return $result;
            }, $previous->getTrace());

            $exception = $exception->getPrevious();
        }

        $details = $this->details;

        if ($details !== null) {
            $response['details'] = $details;
        }

        error_log(json_encode($response, JSON_PRETTY_PRINT) . PHP_EOL);

        return $response;
    }
}