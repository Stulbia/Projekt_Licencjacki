<?php

namespace App\Service;

use App\Controller\AvatarController;
use App\Entity\User;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class AvatarService implements AvatarServiceInterface
{
    private string $avatarDir;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SluggerInterface $slugger,
        string $avatarsDir
    ) {
        $this->avatarDir = $avatarsDir;
    }

    public function updateAvatar(User $user, UploadedFile $file): void
    {
        $filesystem = new Filesystem();

        // Usuń stary plik, jeśli istnieje
        if ($user->getAvatarFilename()) {
            $oldPath = $this->avatarDir . '/' . $user->getAvatarFilename();
            if ($filesystem->exists($oldPath)) {
                $filesystem->remove($oldPath);
            }
        }

        // Zapisz nowy plik
        $safeFilename = $this->slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($this->avatarDir, $newFilename);

        $user->setAvatarFilename($newFilename);
        $this->entityManager->flush();
    }
}
