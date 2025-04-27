<?php

namespace App\Entity;

use App\Repository\ForumPermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForumPermissionRepository::class)]
#[ORM\UniqueConstraint(name: 'forum_permission_unique', columns: ['forum_id', 'role_id', 'permission'])]
class ForumPermission
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne(inversedBy: 'forumPermissions')]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
	private ?Forum $forum = null;

	#[ORM\ManyToOne]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
	private ?Role $role = null;

	#[ORM\Column(length: 100)]
	private ?string $permission = null;

	#[ORM\Column]
	private ?int $value = null;

	public function getId(): ?int
	{
		return $this->id;
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

	public function getRole(): ?Role
	{
		return $this->role;
	}

	public function setRole(?Role $role): static
	{
		$this->role = $role;

		return $this;
	}

	public function getPermission(): ?string
	{
		return $this->permission;
	}

	public function setPermission(string $permission): static
	{
		$this->permission = $permission;

		return $this;
	}

	public function getValue(): ?int
	{
		return $this->value;
	}

	public function setValue(?int $value): static
	{
		$this->value = $value;
		return $this;
	}
}
