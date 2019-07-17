<?php

declare(strict_types=1);

namespace App\Listener;

use App\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Validator\ConstraintViolation;

class ValidationExceptionListener implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => 'onKernelException'
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();

        if (!$exception instanceof ValidationException) {
            return;
        }

        $violations = [];
        /** @var ConstraintViolation $violation */
        foreach ($exception->getConstraintViolationList() as $violation) {
            $violations[] = [
                'path' => $violation->getPropertyPath(),
                'reason' => $violation->getMessage(),
                'invalidValue' => $violation->getInvalidValue(),
            ];
        }

        $error = [
            'error' => [
                'reason' => 'Invalid request content',
                'code' => 422,
                'violations' => $violations,
            ],
        ];

        $event->setResponse(new JsonResponse($error, 422));
    }

}
