<?php

namespace App\Twig;

use App\Entity\Forum;
use App\Service\PermissionService;
use App\Service\UserService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PermissionExtension extends AbstractExtension
{
	public function __construct(private readonly PermissionService $permissionService, private readonly UserService $userService)
	{
	}

	/**
	 * Registers custom Twig functions.
	 *
	 * @return TwigFunction[] An array of available Twig functions.
	 */
	public function getFunctions(): array
	{
		return [
			new TwigFunction('has_forum_permission', [$this, 'hasForumPermission']),
			new TwigFunction('has_global_permission', [$this, 'hasGlobalPermission']),
			new TwigFunction('get_permission', [$this, 'getPermission']),
		];
	}

	/**
	 * Checks whether the current user has the given permission for a specific forum.
	 *
	 * @param string $permissionKey The permission key to check.
	 * @param Forum  $forum         The forum context.
	 *
	 * @return bool True if the user has the permission, false otherwise.
	 */
	public function hasForumPermission(string $permissionKey, Forum $forum): bool
	{
		$user = $this->userService->getCurrentUser();
		return $this->permissionService->hasPermission($user, $permissionKey, $forum);
	}

	/**
	 * Checks whether the current user has the given global permission.
	 *
	 * @param string $permissionKey The permission key to check.
	 *
	 * @return bool True if the user has the global permission, false otherwise.
	 */
	public function hasGlobalPermission(string $permissionKey): bool
	{
		$user = $this->userService->getCurrentUser();
		return $this->permissionService->hasPermission($user, $permissionKey);
	}

	/**
	 * Returns the numeric permission value for the current user.
	 * Forum-specific permissions override global ones if specified.
	 *
	 * @param string     $permissionKey The permission key to retrieve.
	 * @param Forum|null $forum         Optional forum context.
	 *
	 * @return int The permission value (-1 = denied, 0 = default, >0 = granted).
	 */
	public function getPermission(string $permissionKey, ?Forum $forum = null): int
	{
		return $this->permissionService->getPermission($this->userService->getCurrentUser(), $permissionKey, $forum);
	}
}