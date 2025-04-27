<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Post::class);
	}

	public function countPostsPerForum(): array
	{
		$qb = $this->createQueryBuilder('p')
			->select('IDENTITY(t.forum) as forum_id, COUNT(p.id) as post_count')
			->join('p.topic', 't')
			->groupBy('t.forum');
		$result = $qb->getQuery()->getResult();
		$counts = [];
		foreach ($result as $row) {
			$counts[$row['forum_id']] = $row['post_count'];
		}
		return $counts;
	}

	public function getLastPostsForAllForums(array $forumIds): array
	{
		if (empty($forumIds)) {
			return [];
		}
		$qb = $this->createQueryBuilder('p')
			->innerJoin('p.topic', 't')
			->innerJoin('t.forum', 'f')
			->where('f.id IN (:forumIds)')
			->setParameter('forumIds', $forumIds)
			->orderBy('p.createdAt', 'DESC');
		$posts = $qb->getQuery()->getResult();
		$lastPosts = [];
		foreach ($posts as $post) {
			$forumId = $post->getTopic()->getForum()->getId();
			if (!isset($lastPosts[$forumId])) {
				$lastPosts[$forumId] = $post;
			}
		}
		return $lastPosts;
	}

	public function countUserPosts(User $user): int
	{
		return (int)$this->createQueryBuilder('p')
			->select('COUNT(p.id)')
			->where('p.author = :user')
			->setParameter('user', $user)
			->getQuery()
			->getSingleScalarResult();
	}

	public function search(string $mode, string $query, array $forumIds, int $days = 0, int $limit = 20, int $offset = 0): array
	{
		if (empty($forumIds)) {
			return [[], 0];
		}
		$qb = $this->createQueryBuilder('p')
			->leftJoin('p.topic', 't')
			->leftJoin('t.forum', 'f')
			->addSelect('t', 'f');
		$this->buildSearchQuery($qb, $mode, $forumIds, $query, $days);
		$total = (clone $qb)
			->select('COUNT(p.id)')
			->resetDQLPart('orderBy')
			->getQuery()
			->getSingleScalarResult();
		$results = $qb->orderBy('p.createdAt', 'DESC')
			->setFirstResult($offset)
			->setMaxResults($limit)
			->getQuery()
			->getResult();
		dump($qb->getQuery()->getSQL());
		dump($qb->getParameters());
		return [$results, $total];
	}

	private function buildSearchQuery(QueryBuilder $qb, string $mode, array $forumIds, string $query, int $days): void
	{
		$expr = $qb->expr();
		if ($mode === 'user') {
			$qb->leftJoin('p.author', 'u')
				->addSelect('u')
				->andWhere($expr->like('u.username', ':query'));
		} elseif ($mode === 'title') {
			$qb->andWhere($expr->like('t.title', ':query'));
		} else {
			$qb->andWhere(
				$expr->orX(
					$expr->like('p.content', ':query'),
					$expr->like('t.title', ':query')
				)
			);
		}
		$qb->andWhere($expr->in('f.id', ':forumIds'))
			->setParameter('query', '%' . $query . '%')
			->setParameter('forumIds', $forumIds);
		if ($days > 0) {
			$qb->andWhere('p.createdAt >= :dateLimit')
				->setParameter('dateLimit', new \DateTimeImmutable("-$days days"));
		}
	}

}
