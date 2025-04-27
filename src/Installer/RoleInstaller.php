<?php

namespace App\Installer;

use App\Entity\Role;
use App\Entity\RolePermission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RoleInstaller
{
	public function __construct(private EntityManagerInterface $em)
	{
	}

	public function run(SymfonyStyle $io): void
	{
		$io->section('2. Setup roles & permissions');

		$rolesData = [
			'Guest' => [
				'description' => 'Non-logged-in visitors.',
				'isSystemRole' => true,
				'style' => null,
			],
			'User' => [
				'description' => 'Registered users',
				'isSystemRole' => true,
				'style' => null,
			],
			'Moderator' => [
				'description' => 'Forum moderators',
				'isSystemRole' => false,
				'style' => null,
			],
			'Administrator' => [
				'description' => 'Users with administrative access',
				'isSystemRole' => true,
				'style' => 'admin',
			],
		];

		$roleEntities = [];

		foreach ($rolesData as $name => $info) {
			$role = new Role();
			$role->setName($name);
			$role->setDescription($info['description']);
			$role->setIsSystemRole($info['isSystemRole']);
			$role->setStyle($info['style']);
			$this->em->persist($role);
			$roleEntities[$name] = $role;
			$io->text("Role '$name' has been created.");
		}

		$defaultPermissions = [
			'Guest'          => [
				'can_see_forum'       => 1,
				'can_view_forum'      => 1,
				'can_use_search'      => 1,
				'can_create_topic'    => 0,
				'can_reply_topic'     => 0,
				'can_edit_own_post'   => -1,
				'edit_own_post_timer' => 0,
				'can_delete_own_post' => -1,
				'can_edit_topic'      => -1,
				'can_delete_topic'    => -1,
				'can_edit_post'       => -1,
				'can_delete_post'     => -1,
				'can_bypass_privacy'  => -1,
				'can_ban_user'        => -1,
				'can_edit_user'       => -1,
				'can_delete_user'     => -1,
				'can_access_acp'      => -1,
				'can_create_forum'    => -1,
				'can_edit_forum'      => -1,
				'can_delete_forum'    => -1,
				'can_create_role'     => -1,
				'can_edit_role'       => -1,
				'can_delete_role'     => -1,
			],
			'User'          => [
				'can_see_forum'       => 1,
				'can_view_forum'      => 1,
				'can_use_search'      => 1,
				'can_create_topic'    => 1,
				'can_reply_topic'     => 1,
				'can_edit_own_post'   => 1,
				'edit_own_post_timer' => 6,
				'can_delete_own_post' => 1,
				'can_edit_topic'      => 0,
				'can_delete_topic'    => 0,
				'can_edit_post'       => 0,
				'can_delete_post'     => 0,
				'can_bypass_privacy'  => 0,
				'can_ban_user'        => 0,
				'can_edit_user'       => 0,
				'can_delete_user'     => 0,
				'can_access_acp'      => 0,
				'can_create_forum'    => 0,
				'can_edit_forum'      => 0,
				'can_delete_forum'    => 0,
				'can_create_role'     => 0,
				'can_edit_role'       => 0,
				'can_delete_role'     => 0,
			],
			'Moderator'     => [
				'can_see_forum'       => 1,
				'can_view_forum'      => 1,
				'can_use_search'      => 1,
				'can_create_topic'    => 1,
				'can_reply_topic'     => 1,
				'can_edit_own_post'   => 1,
				'edit_own_post_timer' => 6,
				'can_delete_own_post' => 1,
				'can_edit_topic'      => 1,
				'can_delete_topic'    => 1,
				'can_edit_post'       => 1,
				'can_delete_post'     => 1,
				'can_bypass_privacy'  => 0,
				'can_ban_user'        => 0,
				'can_edit_user'       => 0,
				'can_delete_user'     => 0,
				'can_access_acp'      => 0,
				'can_create_forum'    => 0,
				'can_edit_forum'      => 0,
				'can_delete_forum'    => 0,
				'can_create_role'     => 0,
				'can_edit_role'       => 0,
				'can_delete_role'     => 0,
			],
			'Administrator' => [
				'can_see_forum'       => 1,
				'can_view_forum'      => 1,
				'can_use_search'      => 1,
				'can_create_topic'    => 1,
				'can_reply_topic'     => 1,
				'can_edit_own_post'   => 1,
				'edit_own_post_timer' => 1440,
				'can_delete_own_post' => 1,
				'can_edit_topic'      => 1,
				'can_delete_topic'    => 1,
				'can_edit_post'       => 1,
				'can_delete_post'     => 1,
				'can_bypass_privacy'  => 1,
				'can_ban_user'        => 1,
				'can_edit_user'       => 1,
				'can_delete_user'     => 1,
				'can_access_acp'      => 1,
				'can_create_forum'    => 1,
				'can_edit_forum'      => 1,
				'can_delete_forum'    => 1,
				'can_create_role'     => 1,
				'can_edit_role'       => 1,
				'can_delete_role'     => 1,
			]
		];

		foreach ($defaultPermissions as $roleName => $permissions) {
			$role = $roleEntities[$roleName] ?? null;
			if (!$role) continue;

			foreach ($permissions as $permKey => $value) {
				$perm = new RolePermission();
				$perm->setRole($role);
				$perm->setName($permKey);
				$perm->setValue($value);
				$this->em->persist($perm);
			}
		}

		$this->em->flush();

		$io->success('Roles and permissions have been initialized.');
	}
}
