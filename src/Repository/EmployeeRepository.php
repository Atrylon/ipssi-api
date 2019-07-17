<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\RessourceNotFoundException;
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
            SELECT DISTINCT 
                e.emp_no, first_name, last_name, dept_name, salary, gender, birth_date
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
                ->setSex($employeeArray['gender'])
                ->setBirthDate(new \DateTime($employeeArray['birth_date']))
                ->setDepartment($employeeArray['dept_name'])
                ->setActualSalary((int) $employeeArray['salary']);
        }

        return $result;
    }

    public function save(Employee $employee): void
    {
        $query = $this->connection->prepare('
            INSERT INTO employees (first_name, last_name, gender, birth_date, hire_date) 
            VALUES(:firstname, :lastname, :sex, :birthDate, NOW());
            INSERT INTO salaries (salary) VALUES (:salary);
            INSERT INTO departments (dept_name) VALUES (:department);
        ');

        $query->execute([
            'firstname' => $employee->getFirstName(),
            'lastname' => $employee->getLastName(),
            'sex' => $employee->getSex(),
            'birthDate' => $employee->getBirthDate()->format('Y/m/d'),
            'salary' => $employee->getActualSalary(),
            'department' => $employee->getDepartment(),
        ]);

        $employee->setId((int) $this->connection->lastInsertId());
    }

    public function findById(int $id)
    {
        $query = $this->connection->prepare('
            SELECT DISTINCT 
                e.emp_no, first_name, last_name, dept_name, salary, gender, birth_date
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
            WHERE e.emp_no = :id');

        $query->execute(['id' => $id]);
        $employeeArray = $query->fetch();

        if($employeeArray === false) {
            throw new RessourceNotFoundException('Employee with id : ' . $id . ' does not exist');
        }

        return (new Employee())
                ->setId((int) $employeeArray['emp_no'])
                ->setFirstName($employeeArray['first_name'])
                ->setLastName($employeeArray['last_name'])
                ->setSex($employeeArray['gender'])
                ->setBirthDate(new \DateTime($employeeArray['birth_date']))
                ->setDepartment($employeeArray['dept_name'])
                ->setActualSalary((int) $employeeArray['salary']);
    }

    public function delete(Employee $employee): void
    {
        $query = $this->connection->prepare('DELETE FROM employees WHERE emp_no = :id');
        $query->execute(['id' => $employee->getId()]);
    }


}
