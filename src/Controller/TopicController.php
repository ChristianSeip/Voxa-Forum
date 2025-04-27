<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Post;
use App\Entity\Topic;
use App\Form\TopicCreateFormType;
use App\Form\TopicEditFormType;
use App\Service\BBCodeService;
use App\Service\TopicService;
use App\Service\UserService;
use App\Service\PermissionService;
use App\Twig\BreadcrumbExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TopicController extends AbstractController
{
	public function __construct(
		private readonly UserService       $userService,
		private readonly PermissionService $permissionService,
		private readonly BBCodeService     $bbcodeService,
		private readonly TopicService      $topicService,
	)
	{
	}

	/**
	 * Displays a topic and paginated posts if user has forum access.
	 */
	#[Route('/topic/{id}-{slug}', name: 'app_topic_view')]
	public function view(Topic $topic, Request $request, TranslatorInterface $translator, EntityManagerInterface $em, BreadcrumbExtension $breadcrumbs): Response
	{
		$user = $this->userService->getCurrentUser();
		$forum = $topic->getForum();
		if (!$this->permissionService->hasPermission($user, 'can_view_forum', $forum)) {
			throw $this->createAccessDeniedException($translator->trans('forum.access_denied', [], 'messages'));
		}
		$this->topicService->countView($topic->getId());
		$breadcrumbs->buildForTopic($topic);
		$page = max(1, (int)$request->query->get('page', 1));
		$limit = 20;
		if ($postId = $request->query->get('post')) {
			$page = $this->getPageOfPost((int)$postId, $topic, $em, $limit) ?? $page;
		}
		$offset = ($page - 1) * $limit;
		$posts = $em->getRepository(Post::class)->findBy(['topic' => $topic], ['createdAt' => 'ASC'], $limit, $offset);
		$totalPosts = $em->getRepository(Post::class)->count(['topic' => $topic]);
		$totalPages = max(1, ceil($totalPosts / $limit));
		return $this->render('topic/view.html.twig', [
			'topic'         => $topic,
			'forum'         => $forum,
			'posts'         => $posts,
			'page'          => $page,
			'totalPages'    => $totalPages,
			'bbcodeService' => $this->bbcodeService
		]);
	}

	/**
	 * Calculates the page number for a given post inside a topic.
	 *
	 * @return int|null Page number or null if post not found or doesn't belong to topic
	 */
	private function getPageOfPost(int $postId, Topic $topic, EntityManagerInterface $em, int $limit = 20): ?int
	{
		$post = $em->getRepository(Post::class)->find($postId);
		if (!$post || $post->getTopic()?->getId() !== $topic->getId()) {
			return null;
		}
		$position = $em->createQueryBuilder()
			->select('COUNT(p.id)')
			->from(Post::class, 'p')
			->where('p.topic = :topic')
			->andWhere('p.createdAt < :createdAt')
			->setParameter('topic', $topic)
			->setParameter('createdAt', $post->getCreatedAt())
			->getQuery()
			->getSingleScalarResult();
		return (int)ceil(($position + 1) / $limit);
	}

	/**
	 * Displays and handles the form for creating a new topic in a forum.
	 * Also creates the first post within the topic.
	 */
	#[Route('/forum/{id}/create-topic', name: 'app_topic_create')]
	public function create(Forum $forum, Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
	{
		$user = $this->userService->getCurrentUser();
		if (!$this->permissionService->hasPermission($user, 'can_create_topic', $forum)) {
			throw $this->createAccessDeniedException($translator->trans('forum.create_denied', [], 'messages'));
		}
		$topic = new Topic();
		$form = $this->createForm(TopicCreateFormType::class, $topic);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$post = new Post();
			$now = new \DateTimeImmutable();
			$topic->setForum($forum)
				->setCreatedAt($now)
				->setAuthor($user)
				->setLastPostAt($now)
				->setLastPoster($user)
				->setTitle($form->get('title')->getData());

			$post->setTopic($topic)
				->setAuthor($user)
				->setCreatedAt($now)
				->setContent($this->bbcodeService->convertToBBCode($form->get('message')->getData()))
				->setUpdatedAt($now)
				->setIpAddress($request->getClientIp());

			$em->persist($topic);
			$em->persist($post);
			$em->flush();

			return $this->redirectToRoute('app_topic_view', [
				'id'   => $topic->getId(),
				'slug' => $topic->getSlug()
			]);
		}

		return $this->render('topic/create.html.twig', [
			'forum' => $forum,
			'form'  => $form->createView(),
		]);
	}

	/**
	 * Allows authorized users to edit the title of a topic.
	 * Checks both global and own-topic editing permissions with time constraints.
	 */
	#[Route('/topic/{id}/edit', name: 'app_topic_edit')]
	public function edit(Topic $topic, Request $request, EntityManagerInterface $em, TranslatorInterface $translator, BreadcrumbExtension $breadcrumbs): Response
	{
		$user = $this->userService->getCurrentUser();
		$canEditTopic = $this->permissionService->hasPermission($user, 'can_edit_topic', $topic->getForum());
		$canEditOwn = ($topic->getAuthor() && $user->getId() === $topic->getAuthor()->getId() && $this->permissionService->hasPermission($user, 'can_edit_own_post', $topic->getForum()));
		$editWindowMinutes = $this->permissionService->getPermission($user, 'edit_own_post_timer', $topic->getForum());
		$editWindow = $topic->getCreatedAt()->modify("+$editWindowMinutes minutes");
		$now = new \DateTimeImmutable();
		if (!$canEditTopic && (!$canEditOwn || $now > $editWindow)) {
			throw $this->createAccessDeniedException($translator->trans('topic.edit.denied', [], 'messages'));
		}
		$breadcrumbs->buildForTopic($topic);
		$form = $this->createForm(TopicEditFormType::class, $topic);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$em->flush();
			$this->addFlash('success', $translator->trans('topic.edit.success'));
			return $this->redirectToRoute('app_topic_view', [
				'id'   => $topic->getId(),
				'slug' => $topic->getSlug(),
			]);
		}
		return $this->render('topic/edit.html.twig', [
			'form'  => $form->createView(),
			'topic' => $topic,
		]);
	}

	/**
	 * Deletes a topic if the user has the appropriate permission and valid CSRF token.
	 */
	#[Route('/topic/{id}/delete', name: 'app_topic_delete', methods: ['POST'])]
	public function delete(Topic $topic, Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
	{
		$user = $this->userService->getCurrentUser();
		$forum = $topic->getForum();
		if (!$this->permissionService->hasPermission($user, 'can_delete_topic', $forum)) {
			throw $this->createAccessDeniedException($translator->trans('topic.delete.denied', [], 'messages'));
		}
		if (!$this->isCsrfTokenValid('delete_topic_' . $topic->getId(), $request->request->get('_token'))) {
			throw $this->createAccessDeniedException('Invalid CSRF token');
		}
		$em->remove($topic);
		$em->flush();
		$this->addFlash('success', $translator->trans('topic.delete.success', [], 'messages'));
		return $this->redirectToRoute('app_forum_view', [
			'id'   => $forum->getId(),
			'slug' => $forum->getSlug()
		]);
	}
}
