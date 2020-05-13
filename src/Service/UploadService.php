<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadService
{
    /** @var String */
    private $uploadsFolder;

    /** @var SluggerInterface $slugger */
    private $slugger;

    /**
     * Constructor
     * 
     * @param $uploadsFolder
     * @param SluggerInterface $slugger
     */
    public function __construct(
        $uploadsFolder, 
        SluggerInterface $slugger
    ) {
        $this->uploadsFolder = $uploadsFolder;
        $this->slugger = $slugger;
    }

    /**
     * Upload
     * 
     * @param UploadedFile $file
     */
    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->uploadsFolder, $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }
}
