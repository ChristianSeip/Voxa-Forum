<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: Topic::class, inversedBy: 'posts')]
	#[ORM\JoinColumn(nullable: false)]
	private ?Topic $topic = null;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
	private ?User $author = null;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $createdAt;

	#[ORM\Column(type: 'text')]
	private string $content;

	#[ORM\ManyToOne(targetEntity: User::class)]
	private ?User $editor = null;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $updatedAt = null;

	#[ORM\Column(type: 'boolean')]
	private bool $isDeleted = false;

	#[ORM\ManyToOne(targetEntity: User::class)]
	private ?User $deletedBy = null;

	#[ORM\Column(type: 'string', length: 45, nullable: true)]
	private ?string $ipAddress = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTopic(): ?Topic
	{
		return $this->topic;
	}

	public function setTopic(?Topic $topic): static
	{
		$this->topic = $topic;
		return $this;
	}

	public function getAuthor(): ?User
	{
		return $this->author;
	}

	public function setAuthor(?User $author): static
	{
		$this->author = $author;
		return $this;
	}

	public function getContent(): string
	{
		return $this->content;
	}

	public function setContent(string $content): static
	{
		$this->content = $content;
		return $this;
	}

	public function getCreatedAt(): \DateTimeInterface
	{
		return $this->createdAt;
	}

	public function setCreatedAt(\DateTimeInterface $createdAt): static
	{
		$this->createdAt = $createdAt;
		return $this;
	}

	public function getEditor(): ?User
	{
		return $this->editor;
	}

	public function setEditor(?User $editor): static
	{
		$this->editor = $editor;
		return $this;
	}

	public function getUpdatedAt(): ?\DateTimeInterface
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
	{
		$this->updatedAt = $updatedAt;
		return $this;
	}

	public function getDeletedBy(): ?User
	{
		return $this->deletedBy;
	}

	public function setDeletedBy(?User $deletedBy): static
	{
		$this->deletedBy = $deletedBy;
		return $this;
	}

	public function isDeleted(): bool
	{
		return $this->isDeleted;
	}

	public function setIsDeleted(bool $isDeleted): static
	{
		$this->isDeleted = $isDeleted;
		return $this;
	}

	public function getIpAddress(): ?string
	{
		return $this->ipAddress;
	}

	public function setIpAddress(?string $ipAddress): static
	{
		$this->ipAddress = $ipAddress;
		return $this;
	}

}
