<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité Role représentant un rôle dans le système
 * 
 * Cette entité gère les rôles des utilisateurs et leurs permissions
 * Elle est liée aux utilisateurs via une relation ManyToMany
 */
#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    /**
     * Identifiant unique du rôle
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['role:read'])]
    private ?int $id = null;

    /**
     * Nom du rôle (ex: ROLE_USER, ROLE_ADMIN)
     * 
     * @Assert\NotBlank(message="Le nom du rôle ne peut pas être vide")
     * @Assert\Length(
     *     min=3,
     *     max=50,
     *     minMessage="Le nom du rôle doit contenir au moins {{ limit }} caractères",
     *     maxMessage="Le nom du rôle ne peut pas dépasser {{ limit }} caractères"
     * )
     * @Assert\Regex(
     *     pattern="/^ROLE_[A-Z_]+$/",
     *     message="Le nom du rôle doit commencer par 'ROLE_' et ne contenir que des majuscules et des underscores"
     * )
     */
    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: "Le nom du rôle ne peut pas être vide")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom du rôle doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom du rôle ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^ROLE_[A-Z_]+$/",
        message: "Le nom du rôle doit commencer par 'ROLE_' et ne contenir que des majuscules et des underscores"
    )]
    #[Groups(['role:read'])]
    private ?string $nom = null;

    /**
     * Description du rôle
     * 
     * @Assert\Length(
     *     max=255,
     *     maxMessage="La description ne peut pas dépasser {{ limit }} caractères"
     * )
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Groups(['role:read'])]
    private ?string $description = null;

    /**
     * Relation ManyToMany avec l'entité User
     * Liste des utilisateurs ayant ce rôle
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'userRoles')]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addUserRole($this);
        }
        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeUserRole($this);
        }
        return $this;
    }
} 