<?php

namespace App\Entity\Upload;

use App\Entity\AbstractEntity;
use App\Entity\Folder\Folder;
use App\Repository\Upload\UploadRepository;
use App\Service\FileUploader;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=UploadRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt",hardDelete=true)
 */
class Upload extends AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $originalName;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $directory;

    /**
     * @ORM\ManyToOne(targetEntity=Folder::class, inversedBy="files")
     * @ORM\JoinColumn(nullable=false)
     */
    private Folder $parent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getDirectory(): ?int
    {
        return $this->directory;
    }

    public function setDirectory(int $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function getPath(): string
    {
        return "uploads/" . DirectoriesEnum::DIRECTORIES[$this->getDirectory()] . "/" . $this->getName();
    }

    public function get64Base(): string
    {
        return "data:image;base64," . base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . $this->getPath()));
    }

    public function getParent(): Folder
    {
        return $this->parent;
    }

    public function setParent(Folder $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function export(): array
    {
        return [
            'id' => $this->getId(),
            'originalName' => $this->getOriginalName(),
            'path' => "/" . $this->getPath(),
            'name' => $this->getName(),
            'directory' => DirectoriesEnum::DIRECTORIES[$this->getDirectory()],
        ];
    }
}
