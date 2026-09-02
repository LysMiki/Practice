<?php

namespace App\EventSubscriber;

use App\Exception\UserAlreadyExistsException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/v1/api')) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof UserAlreadyExistsException) {
            $event->setResponse(
                new JsonResponse(
                    ['error' => 'User with this login and pass already exists'],
                    Response::HTTP_CONFLICT,
                )
            );
            return;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();

            $message = match ($statusCode) {
                400 => 'Bad request',
                401 => 'Authentication required',
                403 => 'Access denied',
                404 => 'Resource not found',
                405 => 'Method not allowed',
                default => 'HTTP error',
            };
        } else {
            $statusCode = 500;
            $message = 'Internal server error';
        }

        $event->setResponse(
            new JsonResponse([
                'error' => $message,
            ], $statusCode)
        );
    }
}
