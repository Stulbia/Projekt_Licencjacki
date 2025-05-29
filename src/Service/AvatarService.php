<?php

/**
 * Avatar service.
 */

namespace App\Service;

use App\Entity\Avatar;
use App\Entity\User;
use App\Repository\AvatarRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AvatarService.
 */
class AvatarService implements AvatarServiceInterface
{
    /**
     * Constructor.
     *
     * @param string                       $targetDirectory     Target directory
     * @param AvatarRepository             $avatarRepository    Avatar repository
     * @param AvatarUploadServiceInterface $avatarUploadService Avatar upload service
     * @param Filesystem                   $filesystem          Filesystem component
     */
    public function __construct(private readonly string $targetDirectory, private readonly AvatarRepository $avatarRepository, private readonly AvatarUploadServiceInterface $avatarUploadService, private readonly Filesystem $filesystem)
    {
    }

    /**
     * Create avatar.
     *
     * @param UploadedFile $uploadedFile Uploaded file
     * @param Avatar       $avatar       Avatar entity
     * @param User         $user         User entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(UploadedFile $uploadedFile, Avatar $avatar, User $user): void
    {
        $avatarFilename = $this->avatarUploadService->upload($uploadedFile);

        $avatar->setUser($user);
        $avatar->setFilename($avatarFilename);
        $this->avatarRepository->save($avatar);
        try {
            $this->avatarRepository->save($avatar);
        } catch (OptimisticLockException|ORMException) {
        }
    }

    /**
     * Update avatar.
     *
     * @param UploadedFile $uploadedFile Uploaded file
     * @param Avatar       $avatar       Avatar entity
     * @param User         $user         User entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(UploadedFile $uploadedFile, Avatar $avatar, User $user): void
    {
        $filename = $avatar->getFilename();

        if (null !== $filename) {
            $this->filesystem->remove(
                $this->targetDirectory.'/'.$filename
            );

            $this->create($uploadedFile, $avatar, $user);
        }
    }

    /**
     * Delete avatar.
     *
     * @param Avatar $avatar Avatar entity
     * @param User   $user   User   entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Avatar $avatar, User $user): void
    {
        $filename = $avatar->getFilename();

        if (null !== $filename) {
            $this->filesystem->remove(
                $this->targetDirectory.'/'.$filename
            );
        }
        $avatar->setUser(null);
        $avatar->setFilename(null);
        $this->avatarRepository->delete($avatar);
    }
}
