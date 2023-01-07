<?php

namespace App\Entity\Folder;

use App\Entity\AbstractEntity;
use App\Entity\Upload\Upload;
use App\Entity\User\User;
use App\Repository\Folder\FolderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=FolderRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt",hardDelete=true)
 */
class Folder extends AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="root", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private ?User $rootOwner;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="createdFolders")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $creator;

    /**
     * @ORM\ManyToOne(targetEntity=Folder::class, inversedBy="folders")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Folder $parent;

    /**
     * @ORM\OneToMany(targetEntity=Folder::class, mappedBy="parent")
     */
    private Collection $folders;

    /**
     * @ORM\OneToMany(targetEntity=Upload::class, mappedBy="parent")
     */
    private Collection $files;

    public function __construct()
    {
        $this->folders = new ArrayCollection();
        $this->files = new ArrayCollection();
    }


    public function getRootOwner(): ?User
    {
        return $this->rootOwner;
    }

    public function setRootOwner(?User $user): self
    {
        $this->rootOwner = $user;

        return $this;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function setCreator(User $user): self
    {
        $this->creator = $user;

        return $this;
    }

    public function getParent(): ?User
    {
        return $this->rootOwner;
    }

    public function setParent(?Folder $folder): self
    {
        $this->parent = $folder;

        return $this;
    }

    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(Folder $folder): self
    {
        if (!$this->folders->contains($folder)) {
            $this->folders[] = $folder;
            $folder->setParent($this);
        }

        return $this;
    }

    public function removeFolder(Folder $folder): self
    {
        if ($this->folders->removeElement($folder)) {
            // set the owning side to null (unless already changed)
            if ($folder->getParent() === $this) {
                $folder->setParent(null);
            }
        }

        return $this;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(Upload $upload): self
    {
        if (!$this->files->contains($upload)) {
            $this->files[] = $upload;
            $upload->setParent($this);
        }

        return $this;
    }

    public function removeFile(Upload $upload): self
    {
        if ($this->files->removeElement($upload)) {
            // set the owning side to null (unless already changed)
            if ($upload->getParent() === $this) {
                $upload->setParent(null);
            }
        }

        return $this;
    }


    function export(): array
    {
        return [];
    }
}