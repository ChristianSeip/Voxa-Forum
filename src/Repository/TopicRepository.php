<?php

namespace App\Repository;

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
}
