<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Forum;

class PermissionService
{

	/**
	 * Checks whether the user has the given permission key with a value greater than 0.
	 * Forum-specific permissions override global permissions if set.
	 *
	 * @param User       $user          The user whose permissions are checked.
	 * @param string     $permissionKey The key of the permission to check.
	 * @param Forum|null $forum         Optional forum context for forum-specific permissions.
	 *
	 * @return bool True if the permission is granted, false otherwise.
	 */
	public function hasPermission(User $user, string $permissionKey, ?Forum $forum = null): bool
	{
		$value = $this->getPermission($user, $permissionKey, $forum);
		return $value > 0;
	}

	/**
	 * Returns the numeric permission value for a user.
	 * Forum-specific permissions override global ones if available.
	 * A value of -1 means explicitly denied.
	 *
	 * @param User       $user          The user whose permissions are checked.
	 * @param string     $permissionKey The key of the permission.
	 * @param Forum|null $forum         Optional forum context.
	 *
	 * @return int The permission value (e.g., 0 = denied, >0 = granted, -1 = explicitly denied).
	 */
	public function getPermission(User $user, string $permissionKey, ?Forum $forum = null): int
	{
		$global = $this->getGlobalPermission($user, $permissionKey);
		if ($global === -1) {
			return -1;
		}
		if ($forum !== null) {
			$forumValue = $this->getForumPermissionOnly($user, $forum, $permissionKey);
			if ($forumValue !== null) {
				return $forumValue === -1 ? -1 : $forumValue;
			}
		}
		return $global;
	}

	/**
	 * Returns the forum-specific permission value without considering global permissions.
	 *
	 * @param User       $user  The user whose forum-specific permissions are checked.
	 * @param Forum|null $forum The forum to check against.
	 * @param string     $key   The permission key.
	 *
	 * @return int|null The forum-specific permission value or null if not defined.
	 */
	public function getForumPermissionOnly(User $user, ?Forum $forum, string $key): ?int
	{
		if (!$forum) {
			return null;
		}
		$value = null;
		foreach ($forum->getForumPermissions() as $fp) {
			if ($fp->getPermission() !== $key) {
				continue;
			}
			if (!in_array($fp->getRole(), $user->getRolesAsObjects(), true)) {
				continue;
			}

			if ($fp->getValue() === -1) {
				return -1;
			}
			$value = max($value ?? 0, $fp->getValue());
		}
		return $value !== null ? $value : null;
	}

	/**
	 * Returns the highest global permission value for the given key among all user roles.
	 * A value of -1 overrides all and indicates explicit denial.
	 *
	 * @param User   $user The user whose global permission is determined.
	 * @param string $key  The permission key.
	 *
	 * @return int The maximum permission value among all roles (or -1 if denied).
	 */
	private function getGlobalPermission(User $user, string $key): int
	{
		$max = 0;
		foreach ($user->getRolesAsObjects() as $role) {
			$value = $role->getPermissionValue($key);
			if ($value === -1) {
				return -1;
			}
			$max = max($max, $value);
		}
		return $max;
	}
}
