<?php

namespace App\Repository;

use App\Entity\Forum;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Forum>
 */
class ForumRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Forum::class);
	}

	public function findAllWithChildren(): array
	{
		return $this->createQueryBuilder('f')
			->leftJoin('f.children', 'c')
			->addSelect('c')
			->where('f.parent IS NULL')
			->orderBy('f.position', 'ASC')
			->getQuery()
			->getResult();
	}

	public function getCategoryInfos(): array
	{
		return $this->createQueryBuilder('f')
			->leftJoin('f.children', 'c')
			->addSelect('c')
			->where('f.parent IS NULL')
			->orderBy('f.position', 'ASC')
			->addOrderBy('c.position', 'ASC')
			->getQuery()
			->getResult();
	}

	public function findAccessibleForumIds(?User $user): array
	{
		$conn = $this->getEntityManager()->getConnection();
		$userId = $user?->getId() ?? 0;
		$sql = <<<SQL
					SELECT DISTINCT f.id
					FROM forum f
					WHERE NOT EXISTS (
							SELECT 1
							FROM forum_permission fp_deny
							JOIN user_role ug_deny ON ug_deny.role_id = fp_deny.role_id
							WHERE fp_deny.forum_id = f.id
								AND fp_deny.permission = 'can_view_forum'
								AND fp_deny.value = -1
								AND ug_deny.user_id = :userId
					)
					AND EXISTS (
							SELECT 1
							FROM user_role ug_allow
							LEFT JOIN forum_permission fp_allow
								ON fp_allow.role_id = ug_allow.role_id
							 AND fp_allow.forum_id = f.id
							 AND fp_allow.permission = 'can_view_forum'
							LEFT JOIN role_permission gp_allow
								ON gp_allow.role_id = ug_allow.role_id
							 AND gp_allow.name = 'can_view_forum'
							WHERE ug_allow.user_id = :userId
								AND (fp_allow.value = 1 OR (fp_allow.value IS NULL AND gp_allow.value = 1))
					)
					SQL;
		$stmt = $conn->prepare($sql);
		$result = $stmt->executeQuery(['userId' => $userId]);
		return array_column($result->fetchAllAssociative(), 'id');
	}

	public function findAllExcluding(?int $excludedId): array
	{
		$qb = $this->createQueryBuilder('f');
		if ($excludedId !== null) {
			$qb->where('f.id != :id')
				->setParameter('id', $excludedId);
		}
		return $qb->orderBy('f.name', 'ASC')
			->getQuery()
			->getResult();
	}
}
