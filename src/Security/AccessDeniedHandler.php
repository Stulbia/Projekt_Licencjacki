<?php

/**
 * Class AccessDeniedHandler.
 */

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

/**
 * Class AccessDeniedHandler.
 */
class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /**
     * handler for AccessDeniedException.
     *
     * Redirects to the previous page, or home page, If there's no referrer
     *
     * @param Request               $request               HTTP request
     * @param AccessDeniedException $accessDeniedException AccessDeniedException
     *
     * @return RedirectResponse RedirectResponse
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): RedirectResponse
    {
        // Get the referrer URL (the previous page)
        $refererUrl = $request->headers->get('referer');
        // If there's no referrer, redirect to the home page or any default page
        if (!$refererUrl) {
            $refererUrl = '/';
        }

        return new RedirectResponse($refererUrl);
    }
}
