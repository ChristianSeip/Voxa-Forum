<?php

namespace App\Entity;

use App\Repository\ForumModeratorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForumModeratorRepository::class)]
class ForumModerator
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne(inversedBy: 'moderatedForums')]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
	private ?User $user = null;

	#[ORM\ManyToOne(inversedBy: 'moderators')]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
	private ?Forum $forum = null;

	public function getId(): ?int
	{
		return $this->id;
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

	public function getForum(): ?Forum
	{
		return $this->forum;
	}

	public function setForum(?Forum $forum): static
	{
		$this->forum = $forum;

		return $this;
	}
}
