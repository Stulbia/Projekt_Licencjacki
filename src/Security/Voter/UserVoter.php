<?php

/**
 * User voter.
 */

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserVoter.
 */
class UserVoter extends Voter
{
    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'VIEW';

    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'EDIT';

    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'DELETE';

    /**
     * Constructor.
     *
     * @param Security $security Security
     */
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof User;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();
        if (!$currentUser instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var User $targetUser */
        $targetUser = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($targetUser, $currentUser),
            self::EDIT => $this->canEdit($targetUser, $currentUser),
            self::DELETE => $this->canDelete($targetUser, $currentUser),
            default => false,
        };
    }

    /**
     * Checks if user can be viewed.
     *
     * @param UserInterface $targetUser  Target User
     * @param UserInterface $currentUser Current User
     *
     * @return bool Result
     */
    private function canView(UserInterface $targetUser, UserInterface $currentUser): bool
    {
        return $currentUser === $targetUser;
    }

    /**
     * Checks if user can edit another user.
     *
     * @param UserInterface $targetUser  Target User
     * @param UserInterface $currentUser Current User
     *
     * @return bool Result
     */
    private function canEdit(UserInterface $targetUser, UserInterface $currentUser): bool
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            return false;
        }

        return $currentUser === $targetUser;
    }

    /**
     * Checks if user can delete another user.
     *
     * @param UserInterface $targetUser  Target User
     * @param UserInterface $currentUser Current User
     *
     * @return bool Result
     */
    private function canDelete(UserInterface $targetUser, UserInterface $currentUser): bool
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            return false;
        }

        return $currentUser === $targetUser;
    }
}
