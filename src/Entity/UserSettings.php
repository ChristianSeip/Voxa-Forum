<?php

namespace App\Entity;

use App\Repository\UserSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSettingsRepository::class)]
class UserSettings
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\OneToOne(inversedBy: 'settings', cascade: ['persist', 'remove'])]
	#[ORM\JoinColumn(nullable: false)]
	private ?User $user = null;

	#[ORM\Column]
	private bool $showEmail = false;

	#[ORM\Column]
	private bool $showGender = false;

	#[ORM\Column]
	private bool $showBirthdate = false;

	public function getId(): ?int
	{
		return $this->id;
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

	public function isShowEmail(): bool
	{
		return $this->showEmail;
	}

	public function setShowEmail(bool $showEmail): static
	{
		$this->showEmail = $showEmail;

		return $this;
	}

	public function isShowGender(): bool
	{
		return $this->showGender;
	}

	public function setShowGender(bool $showGender): static
	{
		$this->showGender = $showGender;

		return $this;
	}

	public function isShowBirthdate(): bool
	{
		return $this->showBirthdate;
	}

	public function setShowBirthdate(bool $showBirthdate): static
	{
		$this->showBirthdate = $showBirthdate;
		return $this;
	}
}
