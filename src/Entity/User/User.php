<?php

namespace App\Entity\User;

use App\Entity\AbstractEntity;
use App\Entity\Folder\Folder;
use App\Enum\UserRolesEnum;
use App\Repository\User\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt",hardDelete=true)
 */
class User extends AbstractEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     */
    protected ?string $email;

    /**
     * @ORM\Column(type="json")
     */
    protected array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    protected string $password;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected ?string $activationHash;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $isActive = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $activatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $activationLinkSentAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected string $firstname;


    /**
     * @ORM\Column(type="string", length=255)
     */
    protected string $lastname;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected ?string $resetPasswordHash = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $resetPasswordLinkSentAt;

    /**
     * @ORM\OneToOne(targetEntity=Folder::class, mappedBy="rootOwner", cascade={"persist", "remove"})
     */
    private ?Folder $root;


    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * @param UserRolesEnum[][] $rolesListsToCheck
     * @return bool
     * check with logic OR between args and logic AND between same array
     */
    public function hasRoles(array ...$rolesListsToCheck): bool
    {
        $roles = new ArrayCollection($this->getRoles());

        foreach ($rolesListsToCheck as $rolesToCheck) {
            if ($roles->filter(fn(string $role) => in_array($role, $rolesToCheck))->count() !== 0) {
                return true;
            }
        }
        return false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = UserRolesEnum::ROLE_USER;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $toDelete = array_diff($this->roles, $roles);
        array_map(fn(string $role) => $this->removeRole($role), $toDelete);
        array_map(fn(string $role) => $this->addRole($role), $roles);

        return $this;
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles)) {
            $this->roles = array_merge($this->roles, [$role]);
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        $this->roles = array_diff($this->roles, [$role]);
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return string|null
     */
    public function getActivationHash(): ?string
    {
        return $this->activationHash;
    }

    /**
     * @param string|null $activationHash
     * @return User
     */
    public function setActivationHash(?string $activationHash): self
    {
        $this->activationHash = $activationHash;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getActivatedAt(): ?DateTime
    {
        return $this->activatedAt;
    }

    /**
     * @param DateTime|null $activatedAt
     * @return User
     */
    public function setActivatedAt(?DateTime $activatedAt): self
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }


    public function getIsActive(): bool
    {
        return $this->isActive;
    }


    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getActivationLinkSentAt(): ?DateTime
    {
        return $this->activationLinkSentAt;
    }

    /**
     * @param DateTime|null $activationLinkSentAt
     * @return User
     */
    public function setActivationLinkSentAt(?DateTime $activationLinkSentAt): self
    {
        $this->activationLinkSentAt = $activationLinkSentAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return User
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return User
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResetPasswordHash(): ?string
    {
        return $this->resetPasswordHash;
    }

    /**
     * @param string|null $resetPasswordHash
     * @return User
     */
    public function setResetPasswordHash(?string $resetPasswordHash): self
    {
        $this->resetPasswordHash = $resetPasswordHash;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getResetPasswordLinkSentAt(): ?DateTime
    {
        return $this->resetPasswordLinkSentAt;
    }

    /**
     * @param DateTime|null $resetPasswordLinkSentAt
     */
    public function setResetPasswordLinkSentAt(?DateTime $resetPasswordLinkSentAt): void
    {
        $this->resetPasswordLinkSentAt = $resetPasswordLinkSentAt;
    }

    public function getFullname(): string
    {
        return $this->getFirstname() . " " . strtoupper($this->getLastname());
    }

    public function getRoot(): Folder
    {
        return $this->root;
    }

    public function setRoot(Folder $root): self
    {
        $this->root = $root;

        return $this;
    }

    public function export(): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'roles' => $this->getRoles(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'isActive' => $this->getIsActive(),
        ];
    }
}
