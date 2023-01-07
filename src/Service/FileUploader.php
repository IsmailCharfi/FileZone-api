<?php

namespace App\Service;

use App\Entity\Upload\Upload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private SluggerInterface $slugger;
    private EntityManagerInterface $entityManager;
    private string $uploadsDirectory;

    public function __construct(string $uploadsDirectory, SluggerInterface $slugger, EntityManagerInterface $entityManager)
    {
        $this->uploadsDirectory = $uploadsDirectory;
        $this->slugger = $slugger;
        $this->entityManager = $entityManager;
    }

    public function upload(UploadedFile $file, int $directory = 0, string $originalFileName = null): ?Upload
    {
        $upload = new Upload();

        $upload->setOriginalName($originalFileName ?? $file->getClientOriginalName());
        $fileName = $this->generateUniqueFileName($file);
        $upload->setName($fileName);
        $upload->setDirectory($directory);

        $file->move($this->getDirectoryAbsolutePath($directory), $fileName);
        $this->entityManager->persist($upload);

        return $upload;
    }

    public function update(UploadedFile $file, Upload $upload): ?Upload
    {
        $fileName = $this->generateUniqueFileName($file);
        $file->move($this->getDirectoryAbsolutePath($upload->getDirectory()), $fileName);
        $oldFileName = $upload->getName();

        $upload->setOriginalName($file->getClientOriginalName());
        $upload->setName($fileName);

        $this->entityManager->flush();

        $oldFile = new File($this->getDirectoryAbsolutePath($upload->getDirectory()) . '/' . $oldFileName);
        $this->removeFile($oldFile);


        return $upload;
    }

    public function getAbsolutePath(Upload $upload): string
    {
        return $this->getDirectoryAbsolutePath($upload->getDirectory()) . '/' . $upload->getName();
    }

    public function getRelativePath(Upload $upload): string
    {
        return $this->getDirectoryRelativePath($upload->getDirectory()) . '/' . $upload->getName();
    }


    //this function deletes the file and the upload instance physically
    public function remove(Upload $upload)
    {
        if ($upload->getDeletedAt()) {
            $path = $this->getDirectoryAbsolutePath($upload->getDirectory()) . '/' . $upload->getName();
            $file = new File($path);
            $this->removeFile($file);

            $this->entityManager->remove($upload);
            $this->entityManager->flush();
            //second call to hard delete the instance
            $this->entityManager->remove($upload);
            $this->entityManager->flush();
            return;
        }
        //if the upload is soft deleted
        $this->entityManager->remove($upload);
        $this->entityManager->flush();
    }

    private function removeFile($file)
    {
        if ($file && file_exists($file)) {
            unlink($file);
        }
    }

    private function generateUniqueFileName(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        return $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
    }

    private function getDirectoryAbsolutePath($directory): string
    {
        return $this->uploadsDirectory . DirectoriesEnum::DIRECTORIES[$directory];
    }

    private function getDirectoryRelativePath($directory): string
    {
        return "/uploads/" . DirectoriesEnum::DIRECTORIES[$directory];
    }

}