<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;

interface AvatarServiceInterface
{
    public function updateAvatar(User $user, UploadedFile $file): void;
}
