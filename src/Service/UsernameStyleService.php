<?php

namespace App\Service;

use App\Entity\Forum;
use App\Entity\User;
use App\Repository\ForumModeratorRepository;

class UsernameStyleService
{
	public function __construct(private ForumModeratorRepository $moderatorRepo)
	{
	}

	/**
	 * Generates a list of CSS classes based on the user's roles and forum-specific status.
	 *
	 * - Always includes "user".
	 * - Adds "mod" if the user is a moderator in the given forum.
	 * - Adds any custom styles defined in the user's roles.
	 *
	 * @param User       $user  The user whose display class should be determined.
	 * @param Forum|null $forum Optional forum context to check for moderator status.
	 *
	 * @return string A space-separated list of CSS class names.
	 */
	public function getCssClass(User $user, ?Forum $forum = null): string
	{
		$classes = ['user'];
		if ($forum !== null && $this->moderatorRepo->findOneBy(['user' => $user, 'forum' => $forum])) {
			$classes[] = 'mod';
		}
		foreach ($user->getRolesAsObjects() as $role) {
			if ($role->getStyle()) {
				$classes[] = $role->getStyle();
			}
		}
		return implode(' ', $classes);
	}
}
