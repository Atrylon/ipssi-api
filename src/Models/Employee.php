<?php

declare(strict_types=1);

namespace App\Models;

use Symfony\Component\Validator\Constraints as Assert;

class Employee
{
    /** @var int */
    private $id;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $firstName;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $lastName;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $department;

    /**
     * @Assert\Type("int")
     * @var int
     */
    private $actualSalary;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $sex;

    /**
     * @Assert\NotBlank()
     * @var \DateTime
     */
    private $birthDate;



    public function getId(): int
    {
        return $this->id;
    }

    public function getSex(): string
    {
        return $this->sex;
    }

    /**
     * @param string $sex
     */
    public function setSex( $sex)
    {
        $this->sex = $sex;
        return $this;
    }

    public function getBirthDate(): \DateTime
    {
        return $this->birthDate;
    }

    /**
     * @param \DateTime $birthDate
     */
    public function setBirthDate(\DateTime $birthDate)
    {
        $this->birthDate = $birthDate;
        return $this;
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

    public function setFirstName($firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName($lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setDepartment($department): self
    {
        $this->department = $department;
        return $this;
    }

    public function setActualSalary($actualSalary): self
    {
        $this->actualSalary = $actualSalary;
        return $this;
    }


}
