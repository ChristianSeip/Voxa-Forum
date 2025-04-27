<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Entity\UserRole;
use App\Entity\UserSettings;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
	public function __construct(private EmailVerifier $emailVerifier, private TranslatorInterface $translator, private string $defaultFromAddress, private string $defaultFromName)
	{
	}

	/**
	 * Handles the registration process, including user creation and email verification.
	 */
	#[Route('/register', name: 'app_register')]
	public function register(Request $request, UserPasswordHasherInterface $hasher, Security $security, EntityManagerInterface $em): Response
	{
		$user = new User();
		$form = $this->createForm(RegistrationFormType::class, $user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
			$user->setTimezone(date_default_timezone_get());
			$this->initializeUserProfile($user);
			$this->initializeUserSettings($user);
			$em->persist($user);
			$this->assignDefaultRole($user, $em);
			$em->flush();
			$this->sendConfirmationEmail($user);
			$this->addFlash('success', $this->translator->trans('registration.success', [], 'messages'));
			return $this->redirectToRoute('app_login');
		}
		return $this->render('registration/register.html.twig', [
			'registrationForm' => $form,
		]);
	}

	/**
	 * Verifies user email using token link sent to the user.
	 */
	#[Route('/verify/email', name: 'app_verify_email')]
	public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
	{
		$id = $request->query->get('id');
		if (null === $id) {
			return $this->redirectToRoute('app_register');
		}
		$user = $userRepository->find($id);
		if (null === $user) {
			return $this->redirectToRoute('app_register');
		}
		try {
			$this->emailVerifier->handleEmailConfirmation($request, $user);
		}
		catch (VerifyEmailExceptionInterface $exception) {
			$this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
			return $this->redirectToRoute('app_register');
		}
		$this->addFlash('success', $this->translator->trans('registration.confirmed', [], 'messages'));
		return $this->redirectToRoute('app_login');
	}

	/**
	 * Assigns the default role (e.g., USER) to the newly registered user.
	 */
	private function assignDefaultRole(User $user, EntityManagerInterface $em): void
	{
		$defaultRole = $em->getRepository(Role::class)->findOneBy(['name' => 'USER']);
		if ($defaultRole) {
			$userRole = new UserRole();
			$userRole->setUser($user);
			$userRole->setRole($defaultRole);
			$em->persist($userRole);
		}
	}

	/**
	 * Initializes a user profile for the newly registered user.
	 */
	private function initializeUserProfile(User $user): void
	{
		$profile = new UserProfile();
		$profile->setUser($user);
		$user->setUserProfile($profile);
	}

	/**
	 * Initializes user settings for the newly registered user.
	 */
	private function initializeUserSettings(User $user): void
	{
		$settings = new UserSettings();
		$settings->setUser($user);
		$user->setSettings($settings);
	}

	/**
	 * Sends a confirmation email to the newly registered user.
	 */
	private function sendConfirmationEmail(User $user): void
	{
		$this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
			(new TemplatedEmail())
				->from(new Address($this->defaultFromAddress, $this->defaultFromName))
				->to((string)$user->getEmail())
				->subject($this->translator->trans('registration.mail_subject', [], 'messages'))
				->htmlTemplate('registration/confirmation_email.html.twig')
		);
	}
}
