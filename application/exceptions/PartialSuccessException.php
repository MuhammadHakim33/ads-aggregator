<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PartialSuccessException
 *
 * Thrown when a batch operation succeeds partially — there are items that
 * were successfully processed and items that failed. Carries two payloads:
 *   - $partial_data : data from items that were SUCCESSFULLY processed
 *   - $errors       : list of error messages from items that FAILED
 */
class PartialSuccessException extends \RuntimeException
{
    private array $partial_data;
    private array $errors;

    public function __construct(array $errors, array $partial_data = [])
    {
        parent::__construct(implode(' | ', $errors));
        $this->errors = $errors;
        $this->partial_data = $partial_data;
    }

    public function getPartialData(): array
    {
        return $this->partial_data;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
