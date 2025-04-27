<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use App\Service\ForumAccessService;
use App\Service\PermissionService;
use App\Service\UserService;
use App\Twig\BreadcrumbExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ForumController extends AbstractController
{

	public function __construct(private UserService $userService, private PostRepository $postRepository, private TopicRepository $topicRepository)
	{
	}

	/**
	 * Displays all accessible categories and forums with post counts and latest posts.
	 */
	#[Route(['/forums', '/'], name: 'app_forums')]
	public function index(ForumAccessService $accessService): Response
	{
		$user = $this->userService->getCurrentUser();
		$categories = $accessService->getVisibleCategoriesWithChildren($user);
		return $this->render('forum/forums.html.twig', [
			'categories' => $categories,
			'postCount'  => $this->countEntries($categories, $this->postRepository->countPostsPerForum()),
			'lastPosts'  => $this->getLastPost($categories),
		]);
	}

	/**
	 * Displays the category overview with counts and last posts.
	 */
	#[Route(path: ['/categories'], name: 'app_categories')]
	public function categories(ForumAccessService $accessService): Response
	{
		$user = $this->userService->getCurrentUser();
		$categories = $accessService->getVisibleCategories($user);
		$forumCount = [];
		foreach ($categories as $category) {
			$forumCount[$category->getId()] = $category->getChildren()->count();
		}
		return $this->render('forum/categories.html.twig', [
			'categories' => $categories,
			'forumCount' => $forumCount,
			'topicCount' => $this->countEntries($categories, $this->topicRepository->countTopicsPerForum()),
			'postCount'  => $this->countEntries($categories, $this->extractPostCountsFromTopics($categories)),
			'lastPosts'  => $this->getLastPost($categories),
		]);
	}

	/**
	 * Displays a specific forum, verifying user permission.
	 */
	#[Route('/forum/{id}-{slug}', name: 'app_forum_view')]
	public function view(Forum $forum, PermissionService $permissionService, TranslatorInterface $translator, BreadcrumbExtension $breadcrumbs): Response
	{
		$user = $this->userService->getCurrentUser();
		if (!$permissionService->hasPermission($user, 'can_view_forum', $forum)) {
			throw $this->createAccessDeniedException($translator->trans('forum.access_denied', [], 'messages'));
		}
		$breadcrumbs->buildForForum($forum);
		$categories[] = $forum;
		return $this->render('forum/view.html.twig', [
			'forum'      => $forum,
			'topicCount' => $this->countEntries($categories, $this->topicRepository->countTopicsPerForum()),
			'postCount'  => $this->countEntries($categories, $this->extractPostCountsFromTopics($categories)),
			'lastPosts'  => $this->getLastPost($categories),
		]);
	}

	/**
	 * Recursively calculates aggregated counts per forum from raw count data.
	 *
	 * @param Forum[]         $forums
	 * @param array<int, int> $entries forumId => count
	 *
	 * @return array<int, int> forumId => total count (including children)
	 */
	private function countEntries(array $forums, array $entries): array
	{
		$counts = [];
		foreach ($forums as $forum) {
			$counts[$forum->getId()] = $this->collectPostCountsRecursive($forum, $entries);
			foreach ($forum->getChildren() as $child) {
				$counts[$child->getId()] = $this->collectPostCountsRecursive($child, $entries);
			}
		}
		return $counts;
	}

	/**
	 * Recursively sums up counts for a forum and its children.
	 *
	 * @param array<int, int> $rawCounts
	 */
	private function collectPostCountsRecursive(Forum $forum, array $rawCounts): int
	{
		$count = $rawCounts[$forum->getId()] ?? 0;
		foreach ($forum->getChildren() as $child) {
			$count += $this->collectPostCountsRecursive($child, $rawCounts);
		}
		return $count;
	}

	/**
	 * Gathers direct post counts from all topics, recursively.
	 *
	 * @param Forum[] $forums
	 *
	 * @return array<int, int> forumId => postCount
	 */
	private function extractPostCountsFromTopics(array $forums): array
	{
		$rawCounts = [];
		foreach ($forums as $forum) {
			$this->collectDirectPostCounts($forum, $rawCounts);
		}
		return $rawCounts;
	}

	/**
	 * Recursively collects post counts for a forum's own topics.
	 *
	 * @param array<int, int> $rawCounts
	 */
	private function collectDirectPostCounts(Forum $forum, array &$rawCounts): void
	{
		$count = 0;
		foreach ($forum->getTopics() as $topic) {
			$count += $topic->getPostCount();
		}
		$rawCounts[$forum->getId()] = $count;
		foreach ($forum->getChildren() as $child) {
			$this->collectDirectPostCounts($child, $rawCounts);
		}
	}

	/**
	 * Collects last posts for all forums and maps the first post per category.
	 *
	 * @param Forum[] $categories
	 *
	 * @return array<int, \App\Entity\Post> forumId or categoryId => Post
	 */
	private function getLastPost(array $categories): array
	{
		$forumIds = [];
		$categoryMap = [];
		foreach ($categories as $category) {
			$categoryId = $category->getId();
			$forumIds[] = $categoryId;
			$categoryMap[$categoryId] = [];
			foreach ($category->getChildren() as $child) {
				$this->collectForumIdsRecursive($child, $forumIds);
				$categoryMap[$categoryId][] = $child->getId();
			}
		}
		$lastPosts = $this->postRepository->getLastPostsForAllForums($forumIds);
		$mapped = $lastPosts;
		foreach ($categoryMap as $categoryId => $childIds) {
			$latestPost = null;
			foreach ($childIds as $childId) {
				if (isset($lastPosts[$childId])) {
					$post = $lastPosts[$childId];
					if ($latestPost === null || $post->getCreatedAt() > $latestPost->getCreatedAt()) {
						$latestPost = $post;
					}
				}
			}
			if ($latestPost !== null) {
				$mapped[$categoryId] = $latestPost;
			}
		}
		return $mapped;
	}

	/**
	 * Recursively collects all forum IDs including children.
	 *
	 * @param array<int> $ids
	 */
	private function collectForumIdsRecursive(Forum $forum, array &$ids): void
	{
		$ids[] = $forum->getId();
		foreach ($forum->getChildren() as $child) {
			$this->collectForumIdsRecursive($child, $ids);
		}
	}
}
