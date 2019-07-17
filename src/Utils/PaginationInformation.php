<?php

namespace App\Utils;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationInformation
{
    /**
     * @var int
     *
     * @Assert\Type("numeric")
     * @Assert\Positive()
     */
    private $page;

    /**
     * @var int
     *
     * @Assert\Type("numeric")
     * @Assert\GreaterThan(value="1")
     * @Assert\LessThanOrEqual(value="100")
     */
    private $perPage;

    public function __construct($page, $perPage)
    {
        $this->page = $page;
        $this->perPage = $perPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }
}
