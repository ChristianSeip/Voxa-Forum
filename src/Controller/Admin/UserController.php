<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Entity\UserSettings;
use App\Form\Admin\UserFormType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\PermissionService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/users')]
class UserController extends AbstractController
{
	private User $user;

	public function __construct(
		private UserService         $userService,
		private PermissionService   $permissionService,
		private TranslatorInterface $translator
	)
	{
		$this->user = $this->userService->getCurrentUser();
		if (!$this->permissionService->hasPermission($this->user, 'can_access_acp')) {
			throw $this->createAccessDeniedException($this->translator->trans('access_denied'));
		}
	}

	#[Route('', name: 'admin_user_index')]
	public function index(Request $request, UserRepository $userRepository): Response
	{
		$query = trim((string)$request->query->get('q')) ?: null;
		$page = max(1, $request->query->getInt('page', 1));
		$limit = 20;
		$offset = ($page - 1) * $limit;
		$result = $userRepository->searchUsersByNameAndMail($query, $offset, $limit);
		return $this->render('admin/user/search.html.twig', [
			'users' => $result['items'],
			'total' => $result['total'],
			'page'  => $page,
			'limit' => $limit,
			'query' => $query,
		]);
	}

	#[Route('/{id}/edit', name: 'admin_user_edit')]
	public function edit(
		User                        $user,
		Request                     $request,
		EntityManagerInterface      $em,
		UserPasswordHasherInterface $passwordHasher,
		RoleRepository              $roleRepository
	): Response
	{
		if ($user->getId() === 1) {
			$this->addFlash('warning', $this->translator->trans('admin.user.edit.forbidden'));
			return $this->redirectToRoute('admin_user_index');
		}
		$form = $this->createForm(UserFormType::class, $user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->handlePasswordUpdate($form, $user, $passwordHasher);
			$this->syncUserRoles($form->get('roles')->getData(), $user, $roleRepository, $em);
			$em->persist($user);
			$em->flush();
			$this->addFlash('success', $this->translator->trans('admin.user.edit.success'));
			return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
		}
		return $this->render('admin/user/edit.html.twig', [
			'user'     => $user,
			'userForm' => $form->createView(),
		]);
	}

	#[Route('/create', name: 'admin_user_create')]
	public function create(
		Request                     $request,
		EntityManagerInterface      $em,
		UserPasswordHasherInterface $passwordHasher,
		RoleRepository              $roleRepository
	): Response
	{
		$user = new User();
		$profile = (new UserProfile())->setUser($user);
		$settings = (new UserSettings())->setUser($user);
		$user->setUserProfile($profile);
		$user->setSettings($settings);
		$form = $this->createForm(UserFormType::class, $user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$password = $form->get('plainPassword')->getData() ?: bin2hex(random_bytes(6));
			$user->setPassword($passwordHasher->hashPassword($user, $password));
			$em->persist($user);
			$em->persist($profile);
			$em->persist($settings);
			$this->syncUserRoles($form->get('roles')->getData(), $user, $roleRepository, $em);
			$em->flush();
			$this->addFlash('success', $this->translator->trans('admin.user.edit.success'));
			return $this->redirectToRoute('admin_user_index');
		}
		return $this->render('admin/user/edit.html.twig', [
			'userForm' => $form->createView(),
			'user'     => $user,
		]);
	}

	#[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
	public function delete(User $user, Request $request, EntityManagerInterface $em): RedirectResponse
	{
		if ($user->getId() === 1) {
			$this->addFlash('danger', $this->translator->trans('admin.user.delete.forbidden'));
			return $this->redirectToRoute('admin_user_index');
		}
		if (!$this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
			$this->addFlash('danger', 'CSRF Token invalid.');
			return $this->redirectToRoute('admin_user_index');
		}
		if (!$this->permissionService->hasPermission($this->user, 'can_delete_user')) {
			throw $this->createAccessDeniedException($this->translator->trans('missing_permission'));
		}
		$this->userService->anonymizeUser($user);
		$em->flush();
		$this->addFlash('success', $this->translator->trans('admin.user.delete.success'));
		return $this->redirectToRoute('admin_user_index');
	}

	private function handlePasswordUpdate($form, User $user, UserPasswordHasherInterface $passwordHasher): void
	{
		$password = $form->get('plainPassword')->getData();
		if ($password) {
			$user->setPassword($passwordHasher->hashPassword($user, $password));
		}
	}

	private function syncUserRoles(array $submittedRoleIds, User $user, RoleRepository $roleRepository, EntityManagerInterface $em): void
	{
		$existingRoles = $user->getRolesAsObjects();
		foreach ($existingRoles as $existingRole) {
			if (!in_array($existingRole->getId(), $submittedRoleIds, true)) {
				$user->removeRole($existingRole);
			}
		}
		foreach ($submittedRoleIds as $roleId) {
			$role = $roleRepository->find($roleId);
			if ($role && !in_array($role, $existingRoles, true)) {
				$user->addRole($role);
				$em->persist($role);
			}
		}
	}
}
