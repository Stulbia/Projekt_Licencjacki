<?php

/**
 * Book voter.
 */

namespace App\Security\Voter;

use App\Entity\Book;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class BookVoter.
 */
class BookVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @var string
     */
    private const EDIT = 'EDIT';

    /**
     * View permission.
     *
     * @var string
     */
    private const VIEW = 'VIEW';

    /**
     * Delete permission.
     *
     * @var string
     */
    private const DELETE = 'DELETE';

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
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Book;
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
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            if ('PUBLIC' === $subject->getStatus()) {
                return match ($attribute) {
                    self::VIEW => true,
                    default => false,
                };
            }

            return false;
        }

        if (!$subject instanceof Book) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can edit book.
     *
     * @param Book         $book Book entity
     * @param UserInterface $user  User
     *
     * @return bool Result
     */
    private function canEdit(Book $book, UserInterface $user): bool
    {
        if ($this->security->isGranted('ROLE_BANNED')) {
            return false;
        }

        return $book->getAuthor() === $user;
    }

    /**
     * Checks if user can view book.
     *
     * @param Book         $book Book entity
     * @param UserInterface $user  User
     *
     * @return bool Result
     */
    private function canView(Book $book, UserInterface $user): bool
    {
        if ('PRIVATE' === $book->getStatus()) {
            return $book->getAuthor() === $user;
        }

        return true;
    }

    /**
     * Checks if user can delete book.
     *
     * @param Book         $book Book entity
     * @param UserInterface $user  User
     *
     * @return bool Result
     */
    private function canDelete(Book $book, UserInterface $user): bool
    {
        if ($this->security->isGranted('ROLE_BANNED')) {
            return false;
        }

        return $book->getAuthor() === $user;
    }
}
