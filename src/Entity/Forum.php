<?php

namespace App\Entity;

use App\Repository\ForumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForumRepository::class)]
class Forum
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 100)]
	private ?string $name = null;

	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $description = null;

	#[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
	private ?Forum $parent = null;

	#[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
	private Collection $children;

	#[ORM\Column(type: 'integer')]
	private int $position = 0;

	#[ORM\Column(type: 'boolean')]
	private bool $isLocked = false;

	#[ORM\Column(type: 'boolean')]
	private bool $isHidden = false;

	#[ORM\OneToMany(targetEntity: ForumModerator::class, mappedBy: 'forum', cascade: ["persist"], orphanRemoval: true)]
	private Collection $moderators;

	#[ORM\OneToMany(targetEntity: ForumPermission::class, mappedBy: 'forum', orphanRemoval: true)]
	private Collection $forumPermissions;

	public function __construct()
	{
		$this->children = new ArrayCollection();
		$this->moderators = new ArrayCollection();
		$this->forumPermissions = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(string $name): static
	{
		$this->name = $name;
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

	public function getParent(): ?self
	{
		return $this->parent;
	}

	public function setParent(?self $parent): static
	{
		$this->parent = $parent;
		return $this;
	}

	public function getChildren(): Collection
	{
		return $this->children;
	}

	public function addChild(Forum $child): static
	{
		if (!$this->children->contains($child)) {
			$this->children->add($child);
			$child->setParent($this);
		}
		return $this;
	}

	public function removeChild(Forum $child): static
	{
		if ($this->children->removeElement($child)) {
			if ($child->getParent() === $this) {
				$child->setParent(null);
			}
		}
		return $this;
	}

	public function getPosition(): ?int
	{
		return $this->position;
	}

	public function setPosition(int $position): static
	{
		$this->position = $position;
		return $this;
	}

	public function isLocked(): bool
	{
		return $this->isLocked;
	}

	public function setLocked(bool $isLocked): static
	{
		$this->isLocked = $isLocked;
		return $this;
	}

	public function isHidden(): bool
	{
		return $this->isHidden;
	}

	public function setIsHidden(bool $isHidden): static
	{
		$this->isHidden = $isHidden;
		return $this;
	}

	public function getModerators(): Collection
	{
		return $this->moderators;
	}

	public function hasModerator(User $user): bool
	{
		foreach ($this->getModerators() as $moderator) {
			if ($moderator === $user) {
				return true;
			}
		}
		return false;
	}

	public function addModerator(User $user): void
	{
		if (!$this->hasModerator($user)) {
			$mod = new ForumModerator();
			$mod->setForum($this);
			$mod->setUser($user);
			$this->moderators->add($mod);
		}
	}

	public function removeModerator(User $user): void
	{
		foreach ($this->moderators as $mod) {
			if ($mod->getUser() === $user) {
				$this->moderators->removeElement($mod);
				break;
			}
		}
	}

	public function getForumPermissions(): Collection
	{
		return $this->forumPermissions;
	}

	public function addForumPermission(ForumPermission $forumPermission): static
	{
		if (!$this->forumPermissions->contains($forumPermission)) {
			$this->forumPermissions->add($forumPermission);
			$forumPermission->setForum($this);
		}
		return $this;
	}

	public function removeForumPermission(ForumPermission $forumPermission): static
	{
		if ($this->forumPermissions->removeElement($forumPermission)) {
			if ($forumPermission->getForum() === $this) {
				$forumPermission->setForum(null);
			}
		}
		return $this;
	}

	public function findForumPermission(string $key, Role $role): ?ForumPermission
	{
		foreach ($this->forumPermissions as $perm) {
			if ($perm->getPermission() === $key && $perm->getRole() === $role) {
				return $perm;
			}
		}
		return null;
	}

	public function getSlug(): string
	{
		return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->name), '-'));
	}
}
