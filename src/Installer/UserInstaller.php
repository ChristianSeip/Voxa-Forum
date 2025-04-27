<?php

namespace App\Installer;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Entity\UserRole;
use App\Entity\UserSettings;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserInstaller
{
	public function __construct(
		private EntityManagerInterface $em,
		private RoleRepository $roleRepository,
		private UserPasswordHasherInterface $passwordHasher
	) {}

	public function run(SymfonyStyle $io): void
	{
		$io->section('4. Create Guest User');

		$guest = new User();
		$guest->setUsername('Guest');
		$guest->setEmail('guest@example.com');
		$guest->setPassword('$2y$DONOTDELETETHISUSER');
		$guest->setIsVerified(true);
		$guest->setLocale('en');
		$guest->setTimezone('UTC');
		$this->em->persist($guest);

		$guestProfile = new UserProfile();
		$guestProfile->setUser($guest);
		$this->em->persist($guestProfile);

		$guestSettings = new UserSettings();
		$guestSettings->setUser($guest);
		$guestSettings->setShowBirthdate(true);
		$guestSettings->setShowEmail(true);
		$guestSettings->setShowGender(true);
		$this->em->persist($guestSettings);

		$gRole = $this->roleRepository->findOneBy(['name' => 'Guest']);
		$guestRole = new UserRole();
		$guestRole->setRole($gRole);
		$guestRole->setUser($guest);
		$this->em->persist($guestRole);

		$io->success('Guest user created.');

		$io->section('5. Create Admin User');

		$username = $io->ask('Admin username');
		do {
			$email = $io->ask('Admin email');
		} while (!filter_var($email, FILTER_VALIDATE_EMAIL));

		$password = $io->askHidden('Admin password', function (?string $value) {
			if (empty($value)) {
				throw new \RuntimeException('Password cannot be empty.');
			}
			return $value;
		});

		$admin = new User();
		$admin->setUsername($username);
		$admin->setEmail($email);
		$admin->setPassword($this->passwordHasher->hashPassword($admin, $password));
		$admin->setIsVerified(true);
		$admin->setLocale('en');
		$admin->setTimezone('UTC');

		$this->em->persist($admin);

		$adminProfile = new UserProfile();
		$adminProfile->setUser($admin);
		$this->em->persist($adminProfile);

		$adminSettings = new UserSettings();
		$adminSettings->setUser($admin);
		$adminSettings->setShowBirthdate(true);
		$adminSettings->setShowEmail(true);
		$adminSettings->setShowGender(true);
		$this->em->persist($adminSettings);

		$uRole = $this->roleRepository->findOneBy(['name' => 'User']);
		$aRole = $this->roleRepository->findOneBy(['name' => 'Administrator']);

		$userRole = new UserRole();
		$userRole->setUser($admin);
		$userRole->setRole($aRole);
		$this->em->persist($userRole);

		$userRole = new UserRole();
		$userRole->setUser($admin);
		$userRole->setRole($uRole);
		$this->em->persist($userRole);

		$io->success('Admin user created.');
	}
}
