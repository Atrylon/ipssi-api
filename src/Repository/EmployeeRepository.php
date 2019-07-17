<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\Employee;
use App\Utils\PaginationInformation;
use Doctrine\DBAL\Connection;

class EmployeeRepository
{
    /**@var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param PaginationInformation $pagination
     *
     * @return Employee[]
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllByPage(PaginationInformation $pagination): array
    {
        $query = $this->connection->prepare('
            SELECT DISTINCT e.emp_no, first_name, last_name, dept_name, salary
            FROM employees e
            INNER JOIN dept_emp ON e.emp_no = dept_emp.emp_no
            INNER JOIN departments d ON dept_emp.dept_no = d.dept_no
            INNER JOIN salaries AS currentSalaries ON
                currentSalaries.emp_no = e.emp_no AND
                currentSalaries.from_date = (
                    SELECT max(salaries.from_date) AS last_date
                    FROM salaries
                    WHERE e.emp_no = salaries.emp_no
                    GROUP BY e.emp_no
            ) 
            LIMIT :from, :perPage');

        $page = $pagination->getPage();
        $perPage = $pagination->getPerPage();

        $query->bindParam('from', $page, \PDO::PARAM_INT);
        $query->bindParam('perPage', $perPage, \PDO::PARAM_INT);
        $query->execute();

        $result = [];
        foreach ($query->fetchAll() as $employeeArray) {
            $result[] = (new Employee())
                ->setId((int) $employeeArray['emp_no'])
                ->setFirstName($employeeArray['first_name'])
                ->setLastName($employeeArray['last_name'])
                ->setDepartment($employeeArray['dept_name'])
                ->setActualSalary((int) $employeeArray['salary']);
        }

        return $result;
    }

}
