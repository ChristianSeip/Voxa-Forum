<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use App\Service\BBCodeService;
use App\Service\PermissionService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostController extends AbstractController
{
	public function __construct(private readonly UserService $userService, private readonly PermissionService $permissionService, private readonly BBCodeService $bbcodeService)
	{
	}

	/**
	 * Handles the reply form to a given post in a topic.
	 * Supports quoting another post and processes form submission.
	 */
	#[Route('/topic/{id}/reply', name: 'app_post_reply', requirements: ['quote' => '\d+'], defaults: ['quote' => null])]
	public function reply(Post $replyTo, Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
	{
		$user = $this->userService->getCurrentUser();
		$topic = $replyTo->getTopic();
		$forum = $topic->getForum();
		if (!$this->permissionService->hasPermission($user, 'can_reply_topic', $forum)) {
			throw $this->createAccessDeniedException($translator->trans('post.reply.denied', [], 'messages'));
		}
		$post = new Post();
		$form = $this->createForm(PostFormType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$now = new \DateTimeImmutable();
			$post->setTopic($topic)
				->setAuthor($user)
				->setCreatedAt($now)
				->setUpdatedAt($now)
				->setIpAddress($request->getClientIp())
				->setContent($this->bbcodeService->convertToBBCode($form->get('content')->getData()));
			$topic->setLastPostAt($now)
				->setLastPoster($user)
				->setPostCount($topic->getPostCount() + 1);
			$em->persist($post);
			$em->flush();
			$url = $this->generateUrl('app_topic_view', [
					'id'   => $topic->getId(),
					'slug' => $topic->getSlug(),
					'post' => $post->getId(),
				]) . '#p' . $post->getId();
			return $this->redirect($url);
		}
		return $this->render('post/editor.html.twig', [
			'form'         => $form->createView(),
			'topic'        => $topic,
			'title'        => $translator->trans('post.reply.title'),
			'heading'      => $translator->trans('post.reply.heading', ['%topic%' => $topic->getTitle()]),
			'button_label' => $translator->trans('post.reply.submit')
		]);
	}

	/**
	 * Allows a user to edit a post if they have the appropriate permissions.
	 * Handles permission checks, time limits, and updates post content.
	 */
	#[Route('/post/{id}/edit', name: 'app_post_edit')]
	public function edit(Post $post, Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
	{
		$user = $this->userService->getCurrentUser();
		$forum = $post->getTopic()->getForum();
		$hasPermission = $this->permissionService->hasPermission($user, 'can_edit_post', $forum);
		$isAuthor = $post->getAuthor()?->getId() === $user?->getId();
		$ownEditAllowed = $isAuthor && $this->permissionService->hasPermission($user, 'can_edit_own_post', $forum);
		$timeLimit = $this->permissionService->getPermission($user, 'edit_own_post_timer', $forum);
		$withinTime = (new \DateTimeImmutable())->getTimestamp() < $post->getCreatedAt()->getTimestamp() + ($timeLimit * 60);
		if (!$hasPermission && !($ownEditAllowed && $withinTime)) {
			throw $this->createAccessDeniedException($translator->trans('post.edit.denied'));
		}
		$post->setContent($this->bbcodeService->convertToHTML($post->getContent()));
		$form = $this->createForm(PostFormType::class, $post);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$post->setUpdatedAt(new \DateTimeImmutable());
			$post->setEditor($user);
			$post->setContent($this->bbcodeService->convertToBBCode($form->get('content')->getData()));
			$em->flush();
			$url = $this->generateUrl('app_topic_view', [
					'id'   => $post->getTopic()->getId(),
					'slug' => $post->getTopic()->getSlug(),
					'post' => $post->getId(),
				]) . '#p' . $post->getId();
			return $this->redirect($url);
		}
		return $this->render('post/editor.html.twig', [
			'form'         => $form->createView(),
			'title'        => $translator->trans('post.edit.title'),
			'heading'      => $translator->trans('post.edit.heading'),
			'button_label' => $translator->trans('post.edit.submit'),
		]);
	}

	/**
	 * Deletes a post or marks it as deleted based on user permissions.
	 * If the last post of a topic is deleted, the entire topic is removed.
	 */
	#[Route('/post/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
	public function delete(Post $post, Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
	{
		$user = $this->userService->getCurrentUser();
		$forum = $post->getTopic()->getForum();
		$isAuthor = $post->getAuthor()?->getId() === $user?->getId();
		$ownEditAllowed = $isAuthor && $this->permissionService->hasPermission($user, 'can_delete_own_post', $forum);
		$timeLimit = $this->permissionService->getPermission($user, 'edit_own_post_timer', $forum);
		$withinTime = (new \DateTimeImmutable())->getTimestamp() < $post->getCreatedAt()->getTimestamp() + ($timeLimit * 60);
		if (!$this->permissionService->hasPermission($user, 'can_delete_post', $forum) && !($ownEditAllowed && $withinTime)) {
			throw $this->createAccessDeniedException($translator->trans('post.delete.denied', [], 'messages'));
		}
		if (!$this->isCsrfTokenValid('delete_post_' . $post->getId(), $request->request->get('_token'))) {
			throw $this->createAccessDeniedException('Invalid CSRF token');
		}
		$topic = $post->getTopic();
		$allPosts = $topic->getPosts()->count();
		if ($allPosts <= 1) {
			$em->remove($topic);
			$this->addFlash('success', $translator->trans('topic.delete.success', [], 'messages'));
		}
		else {
			if ($isAuthor) {
				$topic->setPostCount($topic->getPostCount() - 1);
				$em->remove($post);
				$lastPost = $em->getRepository(Post::class)
					->findOneBy(['topic' => $topic], ['createdAt' => 'DESC']);
				$topic->setLastPoster($lastPost?->getAuthor());
				$lastCreatedAt = $lastPost?->getCreatedAt();
				$topic->setLastPostAt($lastCreatedAt ? \DateTimeImmutable::createFromMutable($lastCreatedAt) : null);
			}
			else {
				$post->setIsDeleted(true);
				$post->setDeletedBy($user);
			}
			$this->addFlash('success', $translator->trans('post.delete.success', [], 'messages'));
		}
		$em->flush();
		if ($allPosts <= 1) {
			return $this->redirectToRoute('app_forum_view', [
				'id'   => $forum->getId(),
				'slug' => $forum->getSlug()
			]);
		}
		$url = $this->generateUrl('app_topic_view', [
				'id'   => $topic->getId(),
				'slug' => $topic->getSlug(),
				'post' => $post->getId(),
			]) . '#p' . $post->getId();
		return $this->redirect($url);
	}
}