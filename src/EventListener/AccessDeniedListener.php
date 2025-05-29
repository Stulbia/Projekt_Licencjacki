<?php

/**
 * AccessDeniedListener.
 *
 * Class listening for exceptions in Symfony application.
 */

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * AccessDeniedListener.
 *
 * Class listening for exceptions in Symfony application.
 */
class AccessDeniedListener
{
    /**
     * Router.
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * RequestStack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * AccessDeniedListener constructor.
     *
     * @param RouterInterface $router       the router instance
     * @param RequestStack    $requestStack the request stack instance
     */
    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * Handles exceptions.
     *
     * @param ExceptionEvent $event the exception event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        // Get the exception
        $exception = $event->getThrowable();

        // Check if the exception is of type AccessDeniedException, NotFoundHttpException, or ResourceNotFoundException
        if (!$exception instanceof AccessDeniedException
            && !$exception instanceof NotFoundHttpException
            && !$exception instanceof AccessDeniedHttpException
            && !$exception instanceof ResourceNotFoundException) {
            return;
        }

        // Get the current request
        $request = $this->requestStack->getCurrentRequest();

        // Get the 'referer' header
        $referer = $request->headers->get('referer');

        // Redirect to the referer if available
        if ($referer) {
            $response = new RedirectResponse($referer);
        } else {
            // Redirect to the default route if referer is not available
            $response = new RedirectResponse($this->router->generate('default_route'));
        }

        // Set the response in the event
        $event->setResponse($response);
    }
}
