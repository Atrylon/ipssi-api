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

    /**
     * @param array $employee
     * @param string $department
     * @param int $salary
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function postEmployee(array $employee, string $department, int $salary): array
    {
        $query = $this->connection->prepare('SELECT MAX(emp_no) as max_no FROM employees');
        $query->execute();
        $result = $query->fetchAll();
        $emp_no = intval($result[0]["max_no"]) + 1;

        $today = date('Y/m/d');

//        dd($employee, $emp_no);

        $query = $this->connection->prepare('INSERT INTO employees (emp_no, birth_date, first_name, last_name, gender, hire_date) 
            VALUES (:emp_no, :birth_date, :first_name, :last_name, :gender, :hire_date) ');
        $query->bindParam('emp_no', $emp_no, \PDO::PARAM_INT);
        $query->bindParam('birth_date', $employee['birthDate'], \PDO::PARAM_STR);
        $query->bindParam('first_name', $employee['firstName'], \PDO::PARAM_STR);
        $query->bindParam('last_name', $employee['lastName'], \PDO::PARAM_STR);
        $query->bindParam('gender', $employee['gender'], \PDO::PARAM_STR);
        $query->bindParam('hire_date',$today , \PDO::PARAM_STR);
        $query->execute();


        $query = $this->connection->prepare('SELECT dept_no FROM departments WHERE dept_name=:department');
        $query->bindParam('department', $department, \PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetchAll();

        $dept_no = '';

        if(sizeof($result) > 0){

            $dept_no = $result[0]["dept_no"];
        }
        else{
            $query = $this->connection->prepare('SELECT dept_no FROM departments');
            $query->execute();
            $result = $query->fetchAll();

            $dept_no_sub = 0;
            foreach($result as $no){
                if(!$dept_no){
                    $dept_no = intval(substr($no["dept_no"], 1));
                }
                else{
                    $dept_no_sub = substr($no["dept_no"], 1);
                    if($dept_no < $dept_no_sub){
                        $dept_no = $dept_no_sub;
                    }
                }
            }

            $dept_no =$dept_no +1;
            $dept_no = "d".$dept_no;
            $query = $this->connection->prepare('INSERT INTO departments (dept_no, dept_name) 
        VALUES (:dept_no, :dept_name) ');
            $query->bindParam('dept_no', $dept_no, \PDO::PARAM_STR);
            $query->bindParam('dept_name', $department, \PDO::PARAM_STR);
            $query->execute();
        }

        $query = $this->connection->prepare('INSERT INTO dept_emp (emp_no, dept_no, from_date, to_date) 
        VALUES (:emp_no, :dept_no, :from_date, "9999-01-01") ');
        $query->bindParam('emp_no', $emp_no, \PDO::PARAM_INT);
        $query->bindParam('dept_no', $dept_no, \PDO::PARAM_STR);
        $query->bindParam('from_date', $today, \PDO::PARAM_STR);
        $query->execute();


        $query = $this->connection->prepare('INSERT INTO salaries (emp_no, salary, from_date, to_date) 
        VALUES (:emp_no, :salary, :from_date, "9999-01-01") ');
        $query->bindParam('emp_no', $emp_no, \PDO::PARAM_INT);
        $query->bindParam('salary', $salary, \PDO::PARAM_INT);
        $query->bindParam('from_date', $today, \PDO::PARAM_STR);
        $query->execute();

        $query = $this->connection->prepare('
            SELECT e.emp_no as id, first_name, last_name, dept_name, salary
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
            ORDER BY e.emp_no DESC
            LIMIT 1');
        $query->execute();
        $result = $query->fetchAll();

        return $result[0];
    }

}
