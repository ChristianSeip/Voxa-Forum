<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;

class UserService
{
	public function __construct(private Security $security, private UserRepository $userRepository)
	{
	}

	/**
	 * Retrieves the current authenticated user with full security-related data loaded.
	 * If no user is authenticated, the guest user (ID = 1) is returned instead.
	 *
	 * @return User The fully loaded user entity.
	 */
	public function getCurrentUser(): User
	{
		$user = $this->security->getUser();
		if ($user instanceof User) {
			return $this->userRepository->getFullUserSecurity($user->getId());
		}
		// Load Guest User
		return $this->userRepository->getFullUserSecurity(1);
	}

	public function anonymizeUser(User $user): void
	{
		if ($user->getId() === 1) {
			throw new \LogicException('User cannot be anonymized.');
		}
		$user->setUsername('deleted#' . $user->getId());
		$user->setEmail('deleted_user_' . $user->getId() . '@example.com');
		$user->setPassword(bin2hex(random_bytes(32)));
		$user->setIsVerified(false);
		$user->getUserProfile()->setName(null);
		$user->getUserProfile()->setBirthdate(null);
		$user->getUserProfile()->setGender(null);
		$user->getUserProfile()->setLocation(null);
	}
}
