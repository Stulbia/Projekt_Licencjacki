<?php
/**
 * UserChecker.
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * Class UserChecker.
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * Checks the user before authentication.
     *
     * @param UserInterface $user the user being authenticated
     *
     * @throws CustomUserMessageAuthenticationException if the user is banned
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && $user->isBanned()) {
            throw new CustomUserMessageAuthenticationException('message.banned');
        }
    }

    /**
     * Checks the user after authentication.
     *
     * @param UserInterface $user the authenticated user
     */
    public function checkPostAuth(UserInterface $user): void
    {
    }
}
