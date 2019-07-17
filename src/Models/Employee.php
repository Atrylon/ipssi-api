<?php

declare(strict_types=1);

namespace App\Models;

class Employee
{
    /** @var int */
    private $id;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $department;

    /** @var int */
    private $actualSalary;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }

    public function getActualSalary(): int
    {
        return $this->actualSalary;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setDepartment(string $department): self
    {
        $this->department = $department;
        return $this;
    }

    public function setActualSalary(int $actualSalary): self
    {
        $this->actualSalary = $actualSalary;
        return $this;
    }


}
