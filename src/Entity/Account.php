<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank]
    private ?string $portalSpolecznosciowy = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $link = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'accounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPortalSpolecznosciowy(): ?string
    {
        return $this->portalSpolecznosciowy;
    }

    public function setPortalSpolecznosciowy(string $portalSpolecznosciowy): static
    {
        $this->portalSpolecznosciowy = $portalSpolecznosciowy;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): static
    {
        $this->link = $link;
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
