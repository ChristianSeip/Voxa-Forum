<?php

namespace App\Controller\Admin;

use App\Entity\Forum;
use App\Entity\ForumPermission;
use App\Entity\Role;
use App\Entity\User;
use App\Form\Admin\ForumFormType;
use App\Registry\PermissionRegistry;
use App\Repository\ForumRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\PermissionService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/forums')]
class ForumController extends AbstractController
{

	private User $user;

	public function __construct(
		private UserService $userService,
		private PermissionService $permissionService,
		private TranslatorInterface $translator,
		private PermissionRegistry $permissionRegistry,
	) {
		$this->user = $this->userService->getCurrentUser();
		if (!$this->permissionService->hasPermission($this->user, 'can_access_acp')) {
			throw $this->createAccessDeniedException($this->translator->trans('access_denied'));
		}
	}

	#[Route('', name: 'admin_forum_index')]
	public function index(ForumRepository $forumRepository): Response
	{
		$forums = $forumRepository->findBy(['parent' => null], ['position' => 'ASC']);
		return $this->render('admin/forum/index.html.twig', [
			'forums' => $forums,
		]);
	}

	#[Route('/create', name: 'admin_forum_create')]
	public function create(Request $request, EntityManagerInterface $em): Response
	{
		if (!$this->permissionService->hasPermission($this->user, 'can_create_forum')) {
			throw $this->createAccessDeniedException($this->translator->trans('access_denied'));
		}

		$forum = new Forum();
		$form = $this->createForm(ForumFormType::class, $forum);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->handleForumForm($forum, $form, $em);

			$this->addFlash('success', $this->translator->trans('admin.forum.edit.success'));
			return $this->redirectToRoute('admin_forum_edit', ['id' => $forum->getId()]);
		}

		return $this->render('admin/forum/edit.html.twig', [
			'forumForm' => $form->createView(),
			'forum'     => $forum,
		]);
	}

	#[Route('/{id}/edit', name: 'admin_forum_edit')]
	public function edit(Forum $forum, Request $request, EntityManagerInterface $em): Response
	{
		if (!$this->permissionService->hasPermission($this->user, 'can_edit_forum')) {
			throw $this->createAccessDeniedException($this->translator->trans('access_denied'));
		}

		$form = $this->createForm(ForumFormType::class, $forum);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->handleForumForm($forum, $form, $em);

			$this->addFlash('success', $this->translator->trans('admin.forum.edit.success'));
			return $this->redirectToRoute('admin_forum_edit', ['id' => $forum->getId()]);
		}

		return $this->render('admin/forum/edit.html.twig', [
			'forumForm' => $form->createView(),
			'forum'     => $forum,
		]);
	}

	#[Route('/{id}/delete', name: 'admin_forum_delete', methods: ['POST'])]
	public function delete(Forum $forum, Request $request, EntityManagerInterface $em): RedirectResponse
	{
		if (!$this->permissionService->hasPermission($this->user, 'can_delete_forum')) {
			throw $this->createAccessDeniedException($this->translator->trans('access_denied'));
		}
		if (!$this->isCsrfTokenValid('delete_forum_' . $forum->getId(), $request->request->get('_token'))) {
			$this->addFlash('danger', 'CSRF token invalid.');
			return $this->redirectToRoute('admin_forum_index');
		}
		$parent = $forum->getParent();
		foreach ($forum->getChildren() as $child) {
			$child->setParent($parent);
		}
		$em->remove($forum);
		$em->flush();
		$this->addFlash('success', $this->translator->trans('admin.forum.delete.success'));
		return $this->redirectToRoute('admin_forum_index');
	}

	#[Route('/admin/api/forum/{id}/moderator/add', name: 'api_forum_add_moderator', methods: ['POST'])]
	public function addModerator(Forum $forum, Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
	{
		if (!$this->permissionService->hasPermission($this->user, 'can_delete_forum')) {
			throw $this->createAccessDeniedException($this->translator->trans('access_denied'));
		}
		$data = json_decode($request->getContent(), true);
		$username = $data['username'] ?? '';
		$user = $userRepository->findOneBy(['username' => $username]);
		if (!$user) {
			return $this->json(['error' => $this->translator->trans('admin.forum.edit.unknown_user')], 404);
		}
		$forum->addModerator($user);
		$em->persist($forum);
		$em->flush();
		return $this->render('admin/forum/_moderator_list.html.twig', [
			'forum' => $forum,
		]);
	}

	#[Route('/admin/api/forum/{id}/moderator/{userId}/remove', name: 'api_forum_remove_moderator', methods: ['POST'])]
	public function removeModerator(Forum $forum, int $userId, EntityManagerInterface $em, UserRepository $userRepository): Response
	{
		if (!$this->permissionService->hasPermission($this->user, 'can_edit_forum')) {
			throw $this->createAccessDeniedException($this->translator->trans('access_denied'));
		}
		$user = $userRepository->find($userId);
		if ($user) {
			$forum->removeModerator($user);
			$em->flush();
		}
		return $this->render('admin/forum/_moderator_list.html.twig', [
			'forum' => $forum,
		]);
	}

	private function handleForumForm(Forum $forum, FormInterface $form, EntityManagerInterface $em): void
	{
		$em->persist($forum);

		$permissionData = $form->get('forumPermissions')->getData();
		foreach ($permissionData as $entry) {
			$role = $entry['role'] ?? null;
			if (!$role instanceof Role) {
				continue;
			}

			foreach ($this->permissionRegistry->getAll() as $key => $meta) {
				if (!($meta['forumScoped'] ?? false)) {
					continue;
				}
				$value = $entry[$key] ?? null;

				$perm = $forum->findForumPermission($key, $role);
				if ($value === null || $value === '') {
					if ($perm) {
						$forum->removeForumPermission($perm);
						$em->remove($perm);
					}
				} elseif (!$perm) {
					$perm = new ForumPermission();
					$perm->setForum($forum);
					$perm->setRole($role);
					$perm->setPermission($key);
					$perm->setValue((int) $value);
					$forum->addForumPermission($perm);
					$em->persist($perm);
				} else {
					$perm->setValue((int) $value);
				}
			}
		}

		$em->flush();
	}

}