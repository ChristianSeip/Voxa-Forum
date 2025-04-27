<?php

namespace App\Entity;

use App\Enum\Gender;
use App\Repository\UserProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserProfileRepository::class)]
class UserProfile
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 75, nullable: true)]
	private ?string $name = null;

	#[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
	private ?\DateTimeInterface $birthdate = null;

	#[ORM\Column(nullable: true, enumType: Gender::class)]
	private ?Gender $gender = null;

	#[ORM\Column(length: 100, nullable: true)]
	private ?string $location = null;

	#[ORM\OneToOne(inversedBy: 'userProfile', cascade: ['persist', 'remove'])]
	#[ORM\JoinColumn(nullable: false)]
	private ?User $user = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(?string $name): static
	{
		$this->name = $name;
		return $this;
	}

	public function getBirthdate(): ?\DateTimeInterface
	{
		return $this->birthdate;
	}

	public function setBirthdate(?\DateTimeInterface $birthdate): static
	{
		$this->birthdate = $birthdate;

		return $this;
	}

	public function getGender(): ?Gender
	{
		return $this->gender;
	}

	public function setGender(?Gender $gender): static
	{
		$this->gender = $gender;

		return $this;
	}

	public function getLocation(): ?string
	{
		return $this->location;
	}

	public function setLocation(?string $location): static
	{
		$this->location = $location;

		return $this;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}

	public function setUser(User $user): static
	{
		$this->user = $user;

		return $this;
	}
}
