<?php

namespace App\Entity;

use App\Repository\RolePermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RolePermissionRepository::class)]
class RolePermission
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'rolePermissions')]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
	private ?Role $role = null;

	#[ORM\Column(length: 100)]
	private ?string $name = null;

	#[ORM\Column]
	private ?int $value = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getRole(): Role
	{
		return $this->role;
	}

	public function setRole(Role $role): static
	{
		$this->role = $role;
		return $this;
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

	public function getValue(): ?int
	{
		return $this->value;
	}

	public function setValue(int $value): static
	{
		$this->value = $value;

		return $this;
	}
}
