<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'user.username.unique')]
#[UniqueEntity(fields: ['email'], message: 'user.email.unique')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 65, unique: true)]
	#[Assert\NotBlank]
	#[Assert\Length(min: 3, max: 65)]
	#[Assert\Regex(pattern: '/^[A-Za-z0-9-]+(?: [A-Za-z0-9-]+)*$/')]
	private ?string $username = null;

	#[ORM\Column(length: 100, unique: true)]
	#[Assert\Email(message: 'user.email.invalid', normalizer: 'trim')]
	private ?string $email = null;

	/**
	 * @var string The hashed password
	 */
	#[ORM\Column]
	private ?string $password = null;

	#[ORM\Column(type: "boolean")]
	private bool $isVerified = false;

	/**
	 * @var Collection<int, UserRole>
	 */
	#[ORM\OneToMany(targetEntity: UserRole::class, mappedBy: 'user', cascade: ['persist'], orphanRemoval: true)]
	private Collection $roles;

	#[ORM\Column(length: 5, options: ['default' => 'en'])]
	private string $locale = 'en';

	#[ORM\Column(length: 64, options: ['default' => 'Europe/Berlin'])]
	private string $timezone = 'Europe/Berlin';

	#[ORM\OneToOne(targetEntity: UserProfile::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
	private ?UserProfile $userProfile = null;

	#[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
	private ?UserSettings $settings = null;

	#[ORM\OneToMany(mappedBy: 'user', targetEntity: ForumModerator::class, orphanRemoval: true)]
	private Collection $moderatedForums;

	/**
	 * @var Collection<int, Post>
	 */
	#[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author')]
	private Collection $posts;

	/**
	 * @var Collection<int, Topic>
	 */
	#[ORM\OneToMany(targetEntity: Topic::class, mappedBy: 'author')]
	private Collection $topics;

	public function __construct()
	{
		$this->roles = new ArrayCollection();
		$this->moderatedForums = new ArrayCollection();
		$this->posts = new ArrayCollection();
		$this->topics = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUsername(): ?string
	{
		return $this->username;
	}

	public function setUsername(string $username): static
	{
		$this->username = $username;

		return $this;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUserIdentifier(): string
	{
		return (string)$this->username;
	}

	public function getEmail(): ?string
	{
		return $this->email;
	}

	public function setEmail(string $email): static
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * @see PasswordAuthenticatedUserInterface
	 */
	public function getPassword(): ?string
	{
		return $this->password;
	}

	public function setPassword(string $password): static
	{
		$this->password = $password;

		return $this;
	}

	public function isVerified(): bool
	{
		return $this->isVerified;
	}

	public function setIsVerified(bool $isVerified): static
	{
		$this->isVerified = $isVerified;
		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials(): void
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

	public function getRoles(): array
	{
		return $this->roles->map(fn (UserRole $userRole) => $userRole->getRole()->getName())->toArray();
	}

	public function getRolesAsObjects(): array
	{
		return $this->roles->map(fn (UserRole $userRole) => $userRole->getRole())->toArray();
	}

	public function addRole(Role $role): static
	{
		foreach ($this->roles as $userRole) {
			if ($userRole->getRole() === $role) {
				return $this;
			}
		}
		$userRole = new UserRole();
		$userRole->setUser($this);
		$userRole->setRole($role);
		$this->roles->add($userRole);
		return $this;
	}

	public function removeRole(Role $role): static
	{
		foreach ($this->roles as $userRole) {
			if ($userRole->getRole() === $role) {
				$this->roles->removeElement($userRole);
				if ($userRole->getUser() === $this) {
					$userRole->setUser(null);
				}
				break;
			}
		}
		return $this;
	}

	public function getLocale(): string
	{
		return $this->locale;
	}

	public function setLocale(string $locale): static
	{
		$this->locale = $locale;
		return $this;
	}

	public function getTimezone(): string
	{
		return $this->timezone;
	}

	public function setTimezone(string $timezone): static
	{
		$this->timezone = $timezone;
		return $this;
	}

	public function getUserProfile(): ?UserProfile
	{
		return $this->userProfile;
	}

	public function setUserProfile(UserProfile $userProfile): static
	{
		// set the owning side of the relation if necessary
		if ($userProfile->getUser() !== $this) {
			$userProfile->setUser($this);
		}

		$this->userProfile = $userProfile;

		return $this;
	}

	public function getSettings(): ?UserSettings
	{
		return $this->settings;
	}

	public function setSettings(UserSettings $settings): static
	{
		$this->settings = $settings;
		return $this;
	}

	public function getModeratedForums(): Collection
	{
		return $this->moderatedForums;
	}

	public function addModeratedForum(ForumModerator $moderator): static
	{
		if (!$this->moderatedForums->contains($moderator)) {
			$this->moderatedForums->add($moderator);
			$moderator->setUser($this);
		}
		return $this;
	}

	public function removeModeratedForum(ForumModerator $moderator): static
	{
		if ($this->moderatedForums->removeElement($moderator)) {
			if ($moderator->getUser() === $this) {
				$moderator->setUser(null);
			}
		}
		return $this;
	}

	/**
	 * @return Collection<int, Post>
	 */
	public function getPosts(): Collection
	{
		return $this->posts;
	}

	public function addPost(Post $post): static
	{
		if (!$this->posts->contains($post)) {
			$this->posts->add($post);
			$post->setAuthor($this);
		}

		return $this;
	}

	public function removePost(Post $post): static
	{
		if ($this->posts->removeElement($post)) {
			// set the owning side to null (unless already changed)
			if ($post->getAuthor() === $this) {
				$post->setAuthor(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, Topic>
	 */
	public function getTopics(): Collection
	{
		return $this->topics;
	}

	public function addTopic(Topic $topic): static
	{
		if (!$this->topics->contains($topic)) {
			$this->topics->add($topic);
			$topic->setAuthor($this);
		}

		return $this;
	}

	public function removeTopic(Topic $topic): static
	{
		if ($this->topics->removeElement($topic)) {
			// set the owning side to null (unless already changed)
			if ($topic->getAuthor() === $this) {
				$topic->setAuthor(null);
			}
		}

		return $this;
	}
}
