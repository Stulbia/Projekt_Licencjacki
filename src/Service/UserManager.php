<?php

/**
 * User Manager.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserManager.
 */
class UserManager implements UserManagerInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    private PaginatorInterface $paginator;

    private UserRepository $userRepository;

    /**
     * Constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher PasswordHasher
     * @param PaginatorInterface          $paginator      Paginator
     * @param UserRepository              $userRepository UserRepository
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher, PaginatorInterface $paginator, UserRepository $userRepository)
    {
        $this->passwordHasher = $passwordHasher;
        $this->paginator = $paginator;
        $this->userRepository = $userRepository;
    }

    /**
     * Saves a new user.
     *
     * @param UserInterface $user User entity
     */
    public function register(UserInterface $user): void
    {
        $password = $user->getPassword();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $this->save($user);
    }

    /**
     * Saves user data changes.
     *
     * @param UserInterface $user User entity
     */
    public function save(UserInterface $user): void
    {
        try {
            $this->userRepository->save($user);
        } catch (OptimisticLockException|ORMException $e) {
            // Handle exceptions here
        }
    }

    /**
     * Items per page.
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate($this->userRepository->queryAll(), $page, self::PAGINATOR_ITEMS_PER_PAGE);
    }

    /**
     * Change Password.
     *
     * @param User   $user             User entity
     * @param string $newPlainPassword New Plain Password
     */
    public function upgradePassword(UserInterface $user, string $newPlainPassword): void
    {
        $newHashedPassword = $this->passwordHasher->hashPassword($user, $newPlainPassword);
        $this->userRepository->upgradePassword($user, $newHashedPassword);
    }

    /**
     * Verify Password.
     *
     * @param User   $user          User entity
     * @param string $plainPassword Plain Password
     *
     * @return bool Verification
     */
    public function verifyPassword(UserInterface $user, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }

    /**
     * Verify if this is the last admin.
     *
     * @return bool Veryfication
     */
    public function canBeDowngraded(): bool
    {
        try {
            return $this->userRepository->countByAdmin() > 1;
        } catch (NoResultException|NonUniqueResultException $e) {
            return false;
        }
    }

    /**
     * Returns false if the attempt is trying to ban an admin.
     *
     * @param User $user User entity
     *
     * @return bool is Admin?
     */
    public function ifBanAdmin(User $user): bool
    {
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles, true)) {
            if ($user->isBanned()) {
                return false;
            }
        }

        return true;
    }
}
