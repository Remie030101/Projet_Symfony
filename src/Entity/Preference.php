<?php

namespace App\Entity;

use App\Repository\PreferenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité Preference représentant les préférences d'un utilisateur
 * 
 * Cette entité est liée à un utilisateur via une relation OneToOne
 * et stocke ses préférences personnelles (langue, thème, notifications)
 */
#[ORM\Entity(repositoryClass: PreferenceRepository::class)]
class Preference
{
    /**
     * Identifiant unique de la préférence
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['preference:read', 'user:read'])]
    private ?int $id = null;

    /**
     * Langue préférée de l'utilisateur
     * 
     * @Assert\NotBlank(message="La langue ne peut pas être vide")
     * @Assert\Length(
     *     min=2,
     *     max=5,
     *     minMessage="La langue doit contenir au moins {{ limit }} caractères",
     *     maxMessage="La langue ne peut pas dépasser {{ limit }} caractères"
     * )
     */
    #[ORM\Column(length: 5)]
    #[Assert\NotBlank(message: "La langue ne peut pas être vide")]
    #[Assert\Length(
        min: 2,
        max: 5,
        minMessage: "La langue doit contenir au moins {{ limit }} caractères",
        maxMessage: "La langue ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Groups(['preference:read', 'user:read'])]
    private ?string $langue = 'fr';

    /**
     * Thème préféré de l'interface (light/dark)
     * 
     * @Assert\NotBlank(message="Le thème ne peut pas être vide")
     * @Assert\Choice(
     *     choices={"light", "dark"},
     *     message="Le thème doit être 'light' ou 'dark'"
     * )
     */
    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Le thème ne peut pas être vide")]
    #[Assert\Choice(
        choices: ["light", "dark"],
        message: "Le thème doit être 'light' ou 'dark'"
    )]
    #[Groups(['preference:read', 'user:read'])]
    private ?string $theme = 'light';

    /**
     * État des notifications (activées/désactivées)
     */
    #[ORM\Column]
    #[Groups(['preference:read', 'user:read'])]
    private ?bool $notifications = true;

    /**
     * Relation OneToOne avec l'entité User
     * L'utilisateur propriétaire de ces préférences
     */
    #[ORM\OneToOne(inversedBy: 'preference', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(string $langue): static
    {
        $this->langue = $langue;
        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function isNotifications(): ?bool
    {
        return $this->notifications;
    }

    public function setNotifications(bool $notifications): static
    {
        $this->notifications = $notifications;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
} 