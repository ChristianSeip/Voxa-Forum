<?php

namespace App\Repository;

use App\Entity\Forum;
use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Topic>
 */
class TopicRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Topic::class);
	}

	public function countTopicsPerForum(): array
	{
		$qb = $this->createQueryBuilder('t')
			->select('IDENTITY(t.forum) as forum_id, COUNT(t.id) as topic_count')
			->groupBy('t.forum');
		$result = $qb->getQuery()->getResult();
		$counts = [];
		foreach ($result as $row) {
			$counts[(int)$row['forum_id']] = (int)$row['topic_count'];
		}
		return $counts;
	}

	public function countPostsPerForum(array $forumIds): array
	{
		$qb = $this->createQueryBuilder('t')
			->select('IDENTITY(t.forum) AS forumId, SUM(t.postCount) AS postCount')
			->where('t.forum IN (:ids)')
			->setParameter('ids', $forumIds)
			->groupBy('t.forum');
		return array_column($qb->getQuery()->getResult(), 'postCount', 'forumId');
	}

	public function findByForum(Forum $forum, int $limit = 20, int $offset = 0): array
	{
		return $this->createQueryBuilder('t')
			->andWhere('t.forum = :forum')
			->setParameter('forum', $forum)
			->orderBy('t.stickyStatus', 'DESC')
			->addOrderBy('t.lastPostAt', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->getQuery()
			->getResult();
	}
}
