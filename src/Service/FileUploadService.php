<?php

// src/Service/FileUploadService.php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadService implements FileUploadServiceInterface
{
    public function __construct(private readonly string $targetDirectory)
    {
    }

    public function upload(UploadedFile $file): string
    {
        $filename = uniqid() . '.' . $file->guessExtension();
        $file->move($this->targetDirectory, $filename);
        return $filename;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
