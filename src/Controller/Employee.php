<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ValidationException;
use App\Repository\EmployeeRepository;
use App\Utils\PaginationInformation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as AbstractControllerAlias;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Employee extends AbstractControllerAlias
{
    /**
     * @Route(path="/employee", methods={"GET"})
     */
    public function list(
        Request $request,
        Serializer $serializer,
        ValidatorInterface $validator,
        EmployeeRepository $employeeRepository
    ) {
        $queryParam = $request->query;
        $paginationInfo = new PaginationInformation(
            $queryParam->get('page', 1),
            $queryParam->get('perPage', 50)
        );

        if (\count($violations = $validator->validate($paginationInfo))) {
            throw new ValidationException($violations);
        }

        $results = $employeeRepository->getAllByPage($paginationInfo);
        return new JsonResponse($serializer->normalize($results));
    }

    /**
     * @Route(path="/employee", methods={"POST"})
     */
    public function addEmployee(
        Request $request,
        Serializer $serializer,
        ValidatorInterface $validator,
        EmployeeRepository $employeeRepository
    ) {

//        dd($request->getContent());
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        $employee = [
            "firstName"=>$parametersAsArray["firstName"],
            "lastName"=>$parametersAsArray["lastName"],
            "gender"=>$parametersAsArray["gender"],
            "birthDate"=>$parametersAsArray["birthDate"]
        ];
        $department = $parametersAsArray["department"];
        $salary = $parametersAsArray["actualSalary"];

        $post = $employeeRepository->postEmployee($employee, $department, $salary);

        dd($post);

        dd(gettype($employee), $department, $salary);
    }
}
