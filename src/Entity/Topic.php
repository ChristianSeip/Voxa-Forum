<?php

namespace App\Entity;

use App\Enum\StickyStatus;
use App\Repository\TopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TopicRepository::class)]
class Topic
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 255)]
	private ?string $title = null;

	#[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'topics')]
	#[ORM\JoinColumn(nullable: false)]
	private ?Forum $forum = null;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'topics')]
	private ?User $author = null;

	#[ORM\Column]
	private ?\DateTimeImmutable $createdAt = null;

	#[ORM\Column]
	private bool $isClosed = false;

	#[ORM\Column]
	private int $stickyStatus = 0;

	#[ORM\Column]
	private int $viewCount = 0;

	#[ORM\Column]
	private int $postCount = 1;

	#[ORM\ManyToOne(targetEntity: User::class)]
	private ?User $lastPoster = null;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $lastPostAt = null;

	#[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'topic', cascade: ['persist', 'remove'])]
	private Collection $posts;

	public function __construct()
	{
		$this->posts = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function setTitle(string $title): static
	{
		$this->title = $title;

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

	public function getAuthor(): ?User
	{
		return $this->author;
	}

	public function setAuthor(?User $author): static
	{
		$this->author = $author;

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

	public function isClosed(): ?bool
	{
		return $this->isClosed;
	}

	public function setIsClosed(bool $isClosed): static
	{
		$this->isClosed = $isClosed;

		return $this;
	}

	public function getStickyStatus(): ?int
	{
		return $this->stickyStatus;
	}

	public function getStickyStatusEnum(): StickyStatus
	{
		return StickyStatus::from($this->stickyStatus);
	}

	public function setStickyStatus(int $stickyStatus): static
	{
		$this->stickyStatus = $stickyStatus;

		return $this;
	}

	public function getViewCount(): ?int
	{
		return $this->viewCount;
	}

	public function setViewCount(int $value): static
	{
		$this->viewCount = $value;
		return $this;
	}

	public function getPostCount(): int
	{
		return $this->postCount;
	}

	public function setPostCount(int $value): self
	{
		$this->postCount = $value;
		return $this;
	}

	public function getPosts(): Collection
	{
		return $this->posts;
	}

	public function addPost(Post $post): self
	{
		if (!$this->posts->contains($post)) {
			$this->posts->add($post);
			$post->setTopic($this);
		}
		return $this;
	}

	public function removePost(Post $post): self
	{
		if ($this->posts->removeElement($post)) {
			if ($post->getTopic() === $this) {
				$post->setTopic(null);
			}
		}
		return $this;
	}

	public function getLastPoster(): ?User
	{
		return $this->lastPoster;
	}

	public function setLastPoster(?User $lastPoster): static
	{
		$this->lastPoster = $lastPoster;
		return $this;
	}

	public function getLastPostAt(): \DateTimeInterface
	{
		return $this->lastPostAt;
	}

	public function setLastPostAt(\DateTimeImmutable $lastPostAt): static
	{
		$this->lastPostAt = $lastPostAt;
		return $this;
	}

	public function getSlug(): string
	{
		return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->title), '-'));
	}
}
