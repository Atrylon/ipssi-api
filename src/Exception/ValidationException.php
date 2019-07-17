<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationList;

class ValidationException extends \Exception
{
    /**@var ConstraintViolationList */
    private $constraintViolationList;

    public function __construct(ConstraintViolationList $constraintViolationList)
    {
        parent::__construct('User Input Invalid');

        $this->constraintViolationList = $constraintViolationList;
    }

    public function getConstraintViolationList(): ConstraintViolationList
    {
        return $this->constraintViolationList;
    }
}
