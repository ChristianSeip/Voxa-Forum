<?php

namespace App\Controller;

use App\Form\EditProfileFormType;
use App\Form\ProfilePreferencesFormType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\PermissionService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/profile')]
class ProfileController extends AbstractController
{

	public function __construct(private PermissionService $permissionService, private UserService $userService)
	{
	}

	/**
	 * Displays and processes the user profile preferences form.
	 */
	#[Route('/preferences', name: 'app_profile_preferences')]
	public function preferences(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
	{
		$user = $this->userService->getCurrentUser();
		$form = $this->createForm(ProfilePreferencesFormType::class, $user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$em->flush();
			$this->addFlash('success', $translator->trans('profile.preferences.saved'));
			return $this->redirectToRoute('app_profile_preferences');
		}
		return $this->render('profile/preferences.html.twig', [
			'preferencesForm' => $form->createView(),
		]);
	}

	/**
	 * Displays and processes the edit profile form, including password change.
	 */
	#[Route('/edit', name: 'app_profile_edit')]
	public function edit(
		Request                     $request,
		EntityManagerInterface      $em,
		UserPasswordHasherInterface $passwordHasher,
		TranslatorInterface         $translator
	): Response
	{
		$user = $this->userService->getCurrentUser();
		$form = $this->createForm(EditProfileFormType::class, $user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$newPassword = $form->get('plainPassword')->getData();
			if ($newPassword) {
				$hashed = $passwordHasher->hashPassword($user, $newPassword);
				$user->setPassword($hashed);
			}
			$em->flush();
			$this->addFlash('success', $translator->trans('profile.updated', [], 'messages'));
			return $this->redirectToRoute('app_profile_edit');
		}
		return $this->render('profile/edit.html.twig', [
			'editForm' => $form->createView(),
		]);
	}

	/**
	 * Displays the profile of a user by ID or username. If no identifier is given, the viewer's own profile is shown.
	 *
	 * @param string|null $identifier User ID or username
	 */
	#[Route('/view/{identifier}', name: 'app_profile_view', defaults: ['identifier' => null])]
	public function view(
		UserRepository      $userRepo,
		TranslatorInterface $translator,
		PostRepository      $postRepo,
		?string             $identifier = null,
	): Response
	{
		$viewer = $this->userService->getCurrentUser();
		if ($identifier === null) {
			$user = $viewer;
		}
		else if (ctype_digit($identifier)) {
			$user = $userRepo->find((int)$identifier);
		}
		else {
			$user = $userRepo->findOneBy(['username' => $identifier]);
		}
		if (!$user) {
			throw $this->createNotFoundException($translator->trans('profile.not_found_exception', [], 'messages'));
		}
		$isSelf = $user === $viewer;
		$canBypassPrivacy = $this->permissionService->hasPermission($viewer, 'can_bypass_privacy');
		$profile = $user->getUserProfile();
		$settings = $user->getSettings();
		return $this->render('profile/user.html.twig', [
			'profileUser'      => $user,
			'profile'          => $profile,
			'settings'         => $settings,
			'isSelf'           => $isSelf,
			'canBypassPrivacy' => $canBypassPrivacy,
			'postCount'        => $postRepo->countUserPosts($user),
		]);
	}
}