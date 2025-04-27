<?php

namespace App\Service;

use App\Entity\Forum;
use App\Entity\User;
use App\Repository\ForumRepository;

class ForumAccessService
{

	public function __construct(
		private ForumRepository   $forumRepository,
		private PermissionService $permissionService
	)
	{
	}

	/**
	 * Returns all categories that are visible to the given user.
	 *
	 * @param User|null $user The current user or null for guests.
	 *
	 * @return Forum[] An array of visible top-level forum categories.
	 */
	public function getVisibleCategories(?User $user): array
	{
		return array_filter(
			$this->forumRepository->getCategoryInfos(),
			fn (Forum $forum) => $this->canUserSeeForum($user, $forum)
		);
	}

	/**
	 * Returns all visible categories and filters their child forums
	 * to only include those also visible to the user.
	 *
	 * @param User|null $user The current user or null for guests.
	 *
	 * @return Forum[] An array of visible categories with filtered children.
	 */
	public function getVisibleCategoriesWithChildren(?User $user): array
	{
		$categories = $this->getVisibleCategories($user);
		foreach ($categories as $category) {
			$filtered = $category->getChildren()->filter(
				fn (Forum $child) => $this->canUserSeeForum($user, $child)
			);
			$category->getChildren()->clear();
			foreach ($filtered as $child) {
				$category->getChildren()->add($child);
			}
		}
		return $categories;
	}

	/**
	 * Checks whether a user has permission to see a specific forum.
	 * Forum-specific overrides global permission if set.
	 *
	 * @param User|null $user  The current user or null for guests.
	 * @param Forum     $forum The forum being checked.
	 *
	 * @return bool True if the forum is visible, false otherwise.
	 */
	private function canUserSeeForum(?User $user, Forum $forum): bool
	{
		$global = $this->permissionService->getPermission($user, 'can_see_forum');
		if ($global === -1) {
			return false;
		}
		$forumValue = $this->permissionService->getForumPermissionOnly($user, $forum, 'can_see_forum');
		if ($forumValue !== null) {
			if ($forumValue === -1) {
				return false;
			}
			return $forumValue > 0;
		}
		return $global > 0;
	}

	/**
	 * Returns the IDs of all forums the given user is allowed to see.
	 * Optimized for use in DB queries (e.g., search).
	 *
	 * @param User|null $user
	 *
	 * @return int[]
	 */
	public function getAccessibleForumIds(?User $user): array
	{
		return $this->forumRepository->findAccessibleForumIds($user);
	}

	/**
	 * Returns all forums the given user is allowed to see.
	 *
	 * @param User|null $user
	 *
	 * @return Forum[]
	 */
	public function getAccessibleForums(?User $user): array
	{
		$forums = $this->forumRepository->findAll();
		return array_filter($forums, fn (Forum $forum) => $this->canUserSeeForum($user, $forum));
	}

}