<?php

namespace App\Message;

class ProcessCsvCommand
{
    private $uploadId;

    /**
     * Constructor
     * 
     * @param $uploadId
     */
    public function __construct($uploadId)
    {
        $this->uploadId = $uploadId;
    }

    /**
     * @return integer
     */
    public function getUploadId()
    {
        return $this->uploadId;
    }

    /**
     * @param mixed $uploadId
     */
    public function setUploadId($uploadId): void
    {
        $this->uploadId = $uploadId;
    }
}