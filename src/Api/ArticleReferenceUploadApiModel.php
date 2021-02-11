<?php


namespace App\Api;


use Symfony\Component\Validator\Constraints as Assert;

class ArticleReferenceUploadApiModel
{
    public $data;
    private $decodedData;
    public $filename;
    public function setData(?string $data)
    {
        $this->data = $data;
        $this->decodedData = base64_decode($data);
    }
    public function getDecodedData(): ?string
    {
        return $this->decodedData;
    }
}
