<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use App\Entity\RolePermission;
use App\Form\Admin\RoleFormType;
use App\Registry\PermissionRegistry;
use App\Repository\RoleRepository;
use App\Service\PermissionService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/roles')]
class RoleController extends AbstractController
{
	private $user;

	public function __construct(private UserService $userService, private PermissionService $permissionService, private TranslatorInterface $translator)
	{
		$this->user = $this->userService->getCurrentUser();
		if (!$this->permissionService->hasPermission($this->user, 'can_access_acp')) {
			throw $this->createAccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
		}
	}

	#[Route('', name: 'admin_role_index')]
	public function index(RoleRepository $roleRepository)
	{
		$roles = $roleRepository->findAll();
		return $this->render('admin/role/index.html.twig', [
			'roles' => $roles,
		]);
	}

	#[Route('/create', name: 'admin_role_create')]
	public function create(Request $request, EntityManagerInterface $em): Response
	{
		if (!$this->permissionService->hasPermission($this->user, 'can_create_role')) {
			throw $this->createAccessDeniedException($this->translator->trans('missing_permission', [], 'messages'));
		}
		$role = new Role();
		$form = $this->createForm(RoleFormType::class, $role);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->processRoleForm($role, $form);
			$em->persist($role);
			$em->flush();
			$this->addFlash('success', $this->translator->trans('admin.role.edit.success'));
			return $this->redirectToRoute('admin_role_index');
		}
		return $this->render('admin/role/edit.html.twig', [
			'form' => $form->createView(),
			'role' => $role,
		]);
	}

	#[Route('/{id}/edit', name: 'admin_role_edit')]
	public function edit(Role $role, Request $request, EntityManagerInterface $em): Response
	{
		if (!$this->permissionService->hasPermission($this->user, 'can_edit_role')) {
			throw $this->createAccessDeniedException($this->translator->trans('missing_permission', [], 'messages'));
		}
		$form = $this->createForm(RoleFormType::class, $role);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->processRoleForm($role, $form);
			$em->persist($role);
			$em->flush();
			$this->addFlash('success', $this->translator->trans('admin.role.edit.success'));
			return $this->redirectToRoute('admin_role_edit', ['id' => $role->getId()]);
		}
		return $this->render('admin/role/edit.html.twig', [
			'form' => $form->createView(),
			'role' => $role,
		]);
	}

	#[Route('/{id}/delete', name: 'admin_role_delete', methods: ['POST'])]
	public function delete(Request $request, Role $role, EntityManagerInterface $em): RedirectResponse
	{
		if (!$this->isCsrfTokenValid('delete_role_' . $role->getId(), $request->request->get('_token'))) {
			$this->addFlash('danger', 'CSRF Token is invalid.');
			return $this->redirectToRoute('admin_role_index');
		}
		if (!$this->permissionService->hasPermission($this->user, 'can_delete_role')) {
			throw $this->createAccessDeniedException($this->translator->trans('missing_permission', [], 'messages'));
		}
		if ($role->isSystemRole()) {
			$this->addFlash('warning', $this->translator->trans('admin.role.delete.not_allowed', [], 'messages'));
			return $this->redirectToRoute('admin_role_index');
		}
		$em->remove($role);
		$em->flush();
		$this->addFlash('success', $this->translator->trans('admin.role.delete.success', [], 'messages'));
		return $this->redirectToRoute('admin_role_index');
	}

	private function processRoleForm(Role $role, FormInterface $form): void
	{
		foreach (PermissionRegistry::getAll() as $key => $meta) {
			$fieldName = 'permission_' . $key;
			if (!$form->has($fieldName)) {
				continue;
			}
			$field = $form->get($fieldName);
			$value = $field->getData();
			if ($value === null) {
				continue;
			}
			$min = $meta['min'] ?? null;
			$max = $meta['max'] ?? null;
			if ($min !== null && $value < $min) {
				$field->addError(new FormError(sprintf('Wert muss mindestens %s sein.', $min)));
				continue;
			}
			if ($max !== null && $value > $max) {
				$field->addError(new FormError(sprintf('Wert darf höchstens %s sein.', $max)));
				continue;
			}
			$permission = $role->getPermissionByName($key);
			if ($permission) {
				$permission->setValue($value);
			} else {
				$permission = new RolePermission();
				$permission->setName($key);
				$permission->setValue($value);
				$permission->setRole($role);
				$role->getRolePermissions()->add($permission);
			}
		}
	}

}

