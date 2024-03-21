<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileService
{
    private $uploadDirectory;
    private $slugger;

    public function __construct(string $uploadDirectory, SluggerInterface $slugger)
    {
        $this->uploadDirectory = $uploadDirectory;
        $this->slugger = $slugger;
    }

    public function storeFile(UploadedFile $file, string $path = ''): string
    {
        $targetDirectory = $this->getTargetDirectoryPath($path);

        $this->ensureDirectoryExists($targetDirectory);

        $filename = $this->getUniqueFilename($file);

        $file->move($targetDirectory, $filename);

        return trim($path, '/') . '/' . $filename;
    }

    public function removeFile(string $path): void
    {
        $filePath = $this->getTargetDirectoryPath($path);

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    private function getTargetDirectoryPath(string $path = ''): string
    {
        if (empty($path)) {
            return $this->uploadDirectory;
        }

        return $this->uploadDirectory . '/' . trim($path, '/');
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function getUniqueFilename(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);

        return $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
    }
}
