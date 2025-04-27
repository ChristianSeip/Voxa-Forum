<?php

namespace App\Repository;

use App\Entity\ForumModerator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ForumModerator>
 */
class ForumModeratorRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ForumModerator::class);
	}

	//    /**
	//     * @return ForumModerator[] Returns an array of ForumModerator objects
	//     */
	//    public function findByExampleField($value): array
	//    {
	//        return $this->createQueryBuilder('f')
	//            ->andWhere('f.exampleField = :val')
	//            ->setParameter('val', $value)
	//            ->orderBy('f.id', 'ASC')
	//            ->setMaxResults(10)
	//            ->getQuery()
	//            ->getResult()
	//        ;
	//    }

	//    public function findOneBySomeField($value): ?ForumModerator
	//    {
	//        return $this->createQueryBuilder('f')
	//            ->andWhere('f.exampleField = :val')
	//            ->setParameter('val', $value)
	//            ->getQuery()
	//            ->getOneOrNullResult()
	//        ;
	//    }
}
