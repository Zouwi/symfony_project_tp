<?php

namespace App\Entity;

use App\Repository\LikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikeRepository::class)]
#[ORM\Table(name: '`like`')]
class Like
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $liker = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    private ?Comment $comment = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isLiker(): ?bool
    {
        return $this->liker;
    }

    public function setLiker(?bool $liker): static
    {
        $this->liker = $liker;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): static
    {
        $this->comment = $comment;

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
