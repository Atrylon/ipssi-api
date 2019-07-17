<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ValidationException;
use App\Models\Employee;
use App\Repository\EmployeeRepository;
use App\Utils\PaginationInformation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as AbstractControllerAlias;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmployeeController extends AbstractControllerAlias
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
    public function create(
        Request $request,
        Serializer $serializer,
        ValidatorInterface $validator,
        EmployeeRepository $employeeRepository
    ) {
        $employee = $serializer->deserialize(
            $request->getContent(),
            Employee::class,
            "json"
        );

        if (\count($violations = $validator->validate($employee)) !== 0) {
            throw new ValidationException($violations);
        }

        $employeeRepository->save($employee);

        return new JsonResponse($serializer->normalize($employee));
    }

    /**
     * @Route(path="/employee/{id}", methods={"DELETE"})
     */
    public function delete(int $id, EmployeeRepository $employeeRepository) {

        $employee = $employeeRepository->findById($id);
        $employeeRepository->delete($employee);

        return new JsonResponse('', 204);
    }
}
