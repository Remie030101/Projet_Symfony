<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité User représentant un utilisateur dans le système
 * 
 * Cette entité implémente UserInterface et PasswordAuthenticatedUserInterface
 * pour l'intégration avec le système de sécurité de Symfony
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Identifiant unique de l'utilisateur
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    /**
     * Email de l'utilisateur (unique)
     * 
     * @Assert\NotBlank(message="L'email ne peut pas être vide")
     * @Assert\Email(message="L'email '{{ value }}' n'est pas un email valide")
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas un email valide")]
    #[Groups(['user:read'])]
    private ?string $email = null;

    /**
     * Nom de famille de l'utilisateur
     * 
     * @Assert\NotBlank(message="Le nom ne peut pas être vide")
     * @Assert\Length(
     *     min=2,
     *     minMessage="Le nom doit contenir au moins {{ limit }} caractères",
     *     max=50,
     *     maxMessage="Le nom ne peut pas dépasser {{ limit }} caractères"
     * )
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide")]
    #[Assert\Length(
        min: 2,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        max: 50,
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Groups(['user:read'])]
    private ?string $nom = null;

    /**
     * Prénom de l'utilisateur
     * 
     * @Assert\NotBlank(message="Le prénom ne peut pas être vide")
     * @Assert\Length(
     *     min=2,
     *     minMessage="Le prénom doit contenir au moins {{ limit }} caractères",
     *     max=50,
     *     maxMessage="Le prénom ne peut pas dépasser {{ limit }} caractères"
     * )
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom ne peut pas être vide")]
    #[Assert\Length(
        min: 2,
        minMessage: "Le prénom doit contenir au moins {{ limit }} caractères",
        max: 50,
        maxMessage: "Le prénom ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Groups(['user:read'])]
    private ?string $prenom = null;

    /**
     * Rôles de l'utilisateur (ROLE_USER par défaut)
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * Mot de passe hashé de l'utilisateur
     * 
     * @Assert\NotBlank(message="Le mot de passe ne peut pas être vide")
     * @Assert\Length(
     *     min=8,
     *     minMessage="Le mot de passe doit contenir au moins {{ limit }} caractères",
     *     max=4096,
     *     maxMessage="Le mot de passe ne peut pas dépasser {{ limit }} caractères"
     * )
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe ne peut pas être vide")]
    #[Assert\Length(
        min: 8,
        minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères",
        max: 4096,
        maxMessage: "Le mot de passe ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $password = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    #[Groups(['user:read'])]
    private Collection $userRoles;

    /**
     * Relation OneToOne avec l'entité Preference
     * Les préférences sont supprimées en cascade si l'utilisateur est supprimé
     */
    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['user:read'])]
    private ?Preference $preference = null;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getNom(): ?string    
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        foreach ($this->userRoles as $role) {
            $roles[] = $role->getNom();
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    /**
     * @return Collection<int, Role>
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(Role $role): static
    {
        if (!$this->userRoles->contains($role)) {
            $this->userRoles->add($role);
        }
        return $this;
    }

    public function removeUserRole(Role $role): static
    {
        $this->userRoles->removeElement($role);
        return $this;
    }

    public function getPreference(): ?Preference
    {
        return $this->preference;
    }

    public function setPreference(?Preference $preference): static
    {
        if ($preference === null && $this->preference !== null) {
            $this->preference->setUser(null);
        }

        if ($preference !== null && $preference->getUser() !== $this) {
            $preference->setUser($this);
        }

        $this->preference = $preference;
        return $this;
    }
}
