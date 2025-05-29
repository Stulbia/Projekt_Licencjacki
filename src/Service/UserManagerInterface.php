<?php

/**
 * User Manager Interface.
 */

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface UserManagerInterface.
 */
interface UserManagerInterface
{
    /**
     * Saves a new user.
     *
     * @param UserInterface $user The user entity
     */
    public function register(UserInterface $user): void;

    /**
     * Saves user data changes.
     *
     * @param UserInterface $user The user entity
     */
    public function save(UserInterface $user): void;

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Change Password.
     *
     * @param UserInterface $user             User entity
     * @param string        $newPlainPassword New Plain Password
     */
    public function upgradePassword(UserInterface $user, string $newPlainPassword): void;

    /**
     * Verify Password.
     *
     * @param UserInterface $user          User entity
     * @param string        $plainPassword Plain Password
     */
    public function verifyPassword(UserInterface $user, string $plainPassword): bool;

    /**
     * Verify if this is the last admin.
     */
    public function canBeDowngraded(): bool;

    /**
     * Returns false if the attempt is trying to ban an admin.
     *
     * @param User $user The user entity
     */
    public function ifBanAdmin(User $user): bool;
}
