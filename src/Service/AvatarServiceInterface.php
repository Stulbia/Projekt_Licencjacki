<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface AvatarServiceInterface
{
    /**
     * Replaces user's avatar file on disk and updates the filename on the user.
     */
    public function updateAvatar(User $user, UploadedFile $file): void;

    /**
     * Deletes user's avatar file from disk and clears the filename.
     */
    public function deleteAvatar(User $user): void;
}
