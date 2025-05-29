<?php

/**
 * Avatar upload service.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AvatarUploadService.
 */
class AvatarUploadService implements AvatarUploadServiceInterface
{
    /**
     * Constructor.
     *
     * @param string            $targetDirectory   Target directory
     * @param FileUploadService $fileUploadService File Upload Service
     */
    public function __construct(private readonly string $targetDirectory, private readonly FileUploadServiceInterface $fileUploadService)
    {
    }

    /**
     * Upload file.
     *
     * @param UploadedFile $file File to upload
     *
     * @return string Filename of uploaded file
     */
    public function upload(UploadedFile $file): string
    {
        return $this->fileUploadService->upload($file);
    }

    /**
     * Getter for target directory.
     *
     * @return string Target directory
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
