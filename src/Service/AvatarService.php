<?php
//
//namespace App\Service;
//
//use App\Controller\AvatarController;
//use App\Entity\User;
//use Symfony\Component\Filesystem\Filesystem;
//use Symfony\Component\HttpFoundation\File\UploadedFile;
//use Symfony\Component\String\Slugger\SluggerInterface;
//use Doctrine\ORM\EntityManagerInterface;
//
//class AvatarService implements AvatarServiceInterface
//{
//    private string $avatarDir;
//
//    public function __construct(
//        private readonly EntityManagerInterface $entityManager,
//        private readonly SluggerInterface $slugger,
//        string $avatarsDir
//    ) {
//        $this->avatarDir = $avatarsDir;
//    }
//
//    public function updateAvatar(User $user, UploadedFile $file): void
//    {
//        $filesystem = new Filesystem();
//
//        // Usuń stary plik, jeśli istnieje
//        if ($user->getAvatarFilename()) {
//            $oldPath = $this->avatarDir . '/' . $user->getAvatarFilename();
//            if ($filesystem->exists($oldPath)) {
//                $filesystem->remove($oldPath);
//            }
//        }
//
//        // Zapisz nowy plik
//        $safeFilename = $this->slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
//        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
//
//        $file->move($this->avatarDir, $newFilename);
//
//        $user->setAvatarFilename($newFilename);
//        $this->entityManager->flush();
//    }
//
//
//
//    public function deleteAvatar(User $user): void
//    {
//
//        $filesystem = new Filesystem();
//
//        // Usuń stary plik, jeśli istnieje
//        if ($user->getAvatarFilename()) {
//            $oldPath = $this->avatarDir . '/' . $user->getAvatarFilename();
//            if ($filesystem->exists($oldPath)) {
//                $filesystem->remove($oldPath);
//            }
//        }
//
//        $avatar = $user->getAvatar();
//        if (!$avatar) {
//            return;
//        }
//
//        // remove file from filesystem if you store it there
//        $filePath = sprintf('%s/%s', $this->getUploadDir(), $avatar->getFilename());
//        if (is_file($filePath)) {
//            @unlink($filePath);
//        }
//
//        // detach avatar entity from user (or set filename to null, depending on your model)
//        $user->setAvatar(null);
//
//        // if Avatar is its own entity and orphanRemoval=true, removing the relation is enough.
//        // if not, you may also need: $this->em->remove($avatar);
//
//        $this->em->flush();
//    }
//
//    private function getUploadDir(): string
//    {
//        // adjust to your config/parameter
//        return \dirname(__DIR__, 2) . '/public/uploads/avatars';
//    }
//}


declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class AvatarService implements AvatarServiceInterface
{
    private Filesystem $fs;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SluggerInterface       $slugger,
        private readonly string                 $avatarsDir, // e.g. '%kernel.project_dir%/public/uploads/avatars'
    )
    {
        $this->fs = new Filesystem();
    }

    public function updateAvatar(User $user, UploadedFile $file): void
    {
        // 1) Remove old file if present
        $this->removeFileIfExists($user->getAvatarFilename());

        // 2) Generate safe unique filename
        $base = pathinfo((string)$file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = (string)$this->slugger->slug($base);
        $ext = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $unique = bin2hex(random_bytes(6));
        $newName = sprintf('%s-%s.%s', $safeBase ?: 'avatar', $unique, $ext);

        // 3) Move file
        try {
            $file->move($this->avatarsDir, $newName);
        } catch (\Throwable $e) {
            // gracefully fail and keep state untouched
            throw new FileException('Could not move uploaded avatar file.', 0, $e);
        }

        // 4) Persist filename on user
        $user->setAvatarFilename($newName);
        $this->em->flush();
    }

    public function deleteAvatar(User $user): void
    {
        // 1) Remove file if present
        $this->removeFileIfExists($user->getAvatarFilename());

        // 2) Clear filename on user and persist
        if ($user->getAvatarFilename() !== null) {
            $user->setAvatarFilename(null);
            $this->em->flush();
        }
    }

    // --- helpers -------------------------------------------------------------

    private function removeFileIfExists(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = $this->pathFor($filename);
        if ($this->fs->exists($path)) {
            // suppress errors from unlink-level issues; Filesystem handles it cleanly
            $this->fs->remove($path);
        }
    }

    private function pathFor(string $filename): string
    {
        return rtrim($this->avatarsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($filename, DIRECTORY_SEPARATOR);
    }
}

