<?php

/**
 * Avatar service interface.
 */

namespace App\Service;

use App\Entity\Avatar;
use App\Entity\User;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface AvatarServiceInterface.
 */
interface AvatarServiceInterface
{
    /**
     * Create avatar.
     *
     * @param UploadedFile $uploadedFile Uploaded file
     * @param Avatar       $avatar       Avatar entity
     * @param User         $user         User entity
     */
    public function create(UploadedFile $uploadedFile, Avatar $avatar, User $user): void;

    /**
     * Update avatar.
     *
     * @param UploadedFile $uploadedFile Uploaded file
     * @param Avatar       $avatar       Avatar entity
     * @param User         $user         User
     */
    public function update(UploadedFile $uploadedFile, Avatar $avatar, User $user): void;

    /**
     * Delete avatar.
     *
     * @param Avatar $avatar Avatar entity
     * @param User   $user   User entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Avatar $avatar, User $user): void;
}
