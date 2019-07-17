<?php

declare(strict_types=1);

namespace App\Listener;

use App\Exception\RessourceNotFoundException;
use App\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Validator\ConstraintViolation;

class RessourceNotFoundExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => 'onKernelException'
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();

        if (!$exception instanceof RessourceNotFoundException) {
            return;
        }

        $error = [
            'error' => [
                'reason' => $exception->getMessage(),
                'code' => 404,
            ],
        ];

        $event->setResponse(new JsonResponse($error, 404));
    }

}
