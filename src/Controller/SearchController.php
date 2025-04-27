<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Service\BBCodeService;
use App\Service\ForumAccessService;
use App\Service\PermissionService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchController extends AbstractController
{

	private User $user;

	public function __construct(
		private readonly PostRepository $postRepository,
		private readonly ForumAccessService $accessService,
		private readonly UserService $userService,
		private readonly BBCodeService $bbCodeService,
	) {
		$this->user = $this->userService->getCurrentUser();
	}

	#[Route('/search', name: 'app_search')]
	public function search(Request $request, TranslatorInterface $translator, PermissionService $permissionService): Response
	{
		if (!$permissionService->hasPermission($this->user, 'can_use_search')) {
			throw $this->createAccessDeniedException($translator->trans('search.access_denied', [], 'messages'));
		}
		$page = max(1, (int)$request->query->get('page', 1));
		$limit = 20;
		$mode = $request->query->get('mode', 'text');
		$query = trim((string) $request->query->get('q', ''));
		$days = (int) $request->query->get('days', 365);
		$selectedForumIds = $request->query->all('forums') ?? [];
		$accessibleForumIds = $this->accessService->getAccessibleForumIds($this->user);
		$forumIds = !empty($selectedForumIds)
			? array_filter($selectedForumIds, fn($id) => in_array($id, $accessibleForumIds))
			: $accessibleForumIds;
		[$posts, $total] = $this->performSearch($mode, $query, $forumIds, $days, $limit, $page, $translator);
		$allForums = $this->accessService->getAccessibleForums($this->user);
		$forumOptions = array_map(fn($f) => ['id' => $f->getId(), 'name' => $f->getName()], $allForums);
		return $this->render('search/index.html.twig', [
			'mode' => $mode,
			'query' => $query,
			'forums' => $forumOptions,
			'selectedForumIds' => $selectedForumIds ?: array_map(fn($f) => $f->getId(), $allForums),
			'days' => $days,
			'results' => array_map([$this, 'formatSearchPostResult'], $posts),
			...$this->getPaginationData($total, $limit, $page),
		]);
	}

	private function performSearch(string $mode, string $query, array $forumIds, int $days, int $limit, int $page, TranslatorInterface $translator): array {
		$offset = ($page - 1) * $limit;
		if (mb_strlen($query) < 3) {
			$this->addFlash('warning', $translator->trans('search.query_too_short'));
			return [[], 0];
		}
		return $this->postRepository->search($mode, $query, $forumIds, $days, $limit, $offset);
	}

	private function formatSearchPostResult(Post $post): array
	{
		$content = strip_tags($this->bbCodeService->convertToSafeHTML($post->getContent()), '<strong><em><u><del><pre><code><blockquote><br>');
		return [
			'type' => 'post',
			'title' => $post->getTopic()->getTitle(),
			'content' => mb_substr($content, 0, 200) . '...',
			'forum' => $post->getTopic()->getForum()->getName(),
			'url' => $this->generateUrl('app_topic_view', [
					'id' => $post->getTopic()->getId(),
					'slug' => $post->getTopic()->getSlug(),
					'post' => $post->getId()
				]) . '#p' . $post->getId()
		];
	}

	private function getPaginationData(int $total, int $limit, int $currentPage): array
	{
		return [
			'totalEntries' => $total,
			'totalPages' => (int) ceil($total / $limit),
			'currentPage' => $currentPage,
		];
	}
}
