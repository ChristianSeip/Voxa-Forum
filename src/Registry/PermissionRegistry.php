<?php

namespace App\Registry;

class PermissionRegistry
{
	/**
	 * Returns the complete list of defined permissions.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function getAll(): array
	{
		return [
			'can_see_forum'       => [
				'label'       => 'permission.can_see_forum',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'standard',
				'forumScoped' => true,
			],
			'can_view_forum'      => [
				'label'       => 'permission.can_view_forum',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'standard',
				'forumScoped' => true,
			],
			'can_create_topic'    => [
				'label'       => 'permission.can_create_topic',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'standard',
				'forumScoped' => true,
			],
			'can_reply_topic'     => [
				'label'       => 'permission.can_reply_topic',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'standard',
				'forumScoped' => true,
			],
			'can_edit_own_post'   => [
				'label'       => 'permission.can_edit_own_post',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'standard',
				'forumScoped' => true,
			],
			'edit_own_post_timer' => [
				'label'       => 'permission.edit_own_post_timer',
				'default'     => 15,
				'min'         => 0,
				'max'         => 1440,
				'category'    => 'standard',
				'forumScoped' => true,
			],
			'can_delete_own_post' => [
				'label'       => 'permission.can_delete_own_post',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'standard',
				'forumScoped' => true,
			],
			'can_use_search'      => [
				'label'       => 'permission.can_use_search',
				'default'     => 1,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'standard',
				'forumScoped' => false,
			],
			'can_edit_topic'      => [
				'label'       => 'permission.can_edit_topic',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => true,
			],
			'can_delete_topic'    => [
				'label'       => 'permission.can_delete_topic',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => true,
			],
			'can_edit_post'       => [
				'label'       => 'permission.can_edit_post',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => true,
			],
			'can_delete_post'     => [
				'label'       => 'permission.can_delete_post',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => true,
			],
			'can_bypass_privacy'  => [
				'label'       => 'permission.can_bypass_privacy',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
			'can_ban_user'        => [
				'label'       => 'permission.can_ban_user',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
			'can_edit_user'       => [
				'label'       => 'permission.can_edit_user',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
			'can_delete_user'     => [
				'label'       => 'permission.can_delete_user',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
			'can_access_acp'      => [
				'label'       => 'permission.can_access_acp',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
			'can_create_forum'    => [
				'label'       => 'permission.can_create_forum',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
			'can_edit_forum'      => [
				'label'       => 'permission.can_edit_forum',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
			'can_delete_forum'    => [
				'label'       => 'permission.can_delete_forum',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
			'can_create_role'     => [
				'label'       => 'permission.can_create_role',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
			'can_edit_role'       => [
				'label'       => 'permission.can_edit_role',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
			'can_delete_role'     => [
				'label'       => 'permission.can_delete_role',
				'default'     => 0,
				'min'         => -1,
				'max'         => 1,
				'category'    => 'admin',
				'forumScoped' => false,
			],
		];
	}

	public static function getKeys(): array
	{
		return array_keys(self::getAll());
	}

	public static function get(string $key): ?array
	{
		return self::getAll()[$key] ?? null;
	}
}
