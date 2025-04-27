<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 50, unique: true)]
	#[Assert\NotBlank]
	private ?string $name = null;

	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $description = null;

	#[ORM\Column(length: 50, nullable: true)]
	private ?string $style = null;

	/**
	 * @var Collection<int, RolePermission>
	 */
	#[ORM\OneToMany(targetEntity: RolePermission::class, mappedBy: 'role', cascade: ['persist', 'remove'], orphanRemoval: true)]
	private Collection $rolePermissions;

	#[ORM\Column(type: 'boolean', options: ['default' => false])]
	private bool $isSystemRole = false;

	public function __construct()
	{
		$this->rolePermissions = new ArrayCollection();
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

	public function getStyle(): ?string
	{
		return $this->style;
	}

	public function setStyle(?string $style): static
	{
		$this->style = $style;

		return $this;
	}

	/**
	 * @return Collection<int, RolePermission>
	 */
	public function getRolePermissions(): Collection
	{
		return $this->rolePermissions;
	}

	public function getPermissionValue(string $permissionKey): int
	{
		foreach ($this->rolePermissions as $permission) {
			if ($permission->getName() === $permissionKey) {
				return $permission->getValue();
			}
		}
		return 0;
	}

	public function setPermissionValue(string $key, int $value): void
	{
		foreach ($this->getRolePermissions() as $perm) {
			if ($perm->getPermission() === $key) {
				$perm->setValue($value);
				return;
			}
		}
	}

	public function getPermissionByName(string $name): ?RolePermission
	{
		foreach ($this->rolePermissions as $permission) {
			if ($permission->getName() === $name) {
				return $permission;
			}
		}
		return null;
	}

	public function isSystemRole(): bool
	{
		return $this->isSystemRole;
	}

	public function setIsSystemRole(bool $isSystemRole): self
	{
		$this->isSystemRole = $isSystemRole;
		return $this;
	}

}
