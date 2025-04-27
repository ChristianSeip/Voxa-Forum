<?php

namespace App\Repository;

use App\Entity\ForumPermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ForumPermission>
 */
class ForumPermissionRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ForumPermission::class);
	}

	//    /**
	//     * @return ForumPermission[] Returns an array of ForumPermission objects
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

	//    public function findOneBySomeField($value): ?ForumPermission
	//    {
	//        return $this->createQueryBuilder('f')
	//            ->andWhere('f.exampleField = :val')
	//            ->setParameter('val', $value)
	//            ->getQuery()
	//            ->getOneOrNullResult()
	//        ;
	//    }
}
