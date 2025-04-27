<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, User::class);
	}

	/**
	 * Used to upgrade (rehash) the user's password automatically over time.
	 */
	public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
	{
		if (!$user instanceof User) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
		}

		$user->setPassword($newHashedPassword);
		$this->getEntityManager()->persist($user);
		$this->getEntityManager()->flush();
	}

	public function getFullUserSecurity(int $userId): ?User
	{
		return $this->createQueryBuilder('u')
			->leftJoin('u.roles', 'ur')->addSelect('ur')
			->leftJoin('ur.role', 'r')->addSelect('r')
			->leftJoin('r.rolePermissions', 'rp')->addSelect('rp')
			->where('u.id = :id')
			->setParameter('id', $userId)
			->getQuery()
			->getOneOrNullResult();
	}

	public function searchUsersByNameAndMail(?string $query, int $offset, int $limit): array
	{
		$qb = $this->createQueryBuilder('u')
			->where('u.id != 1');
		if ($query) {
			$qb
				->andWhere('LOWER(u.username) LIKE :q OR LOWER(u.email) LIKE :q')
				->setParameter('q', '%' . mb_strtolower($query) . '%');
		}
		$items = $qb
			->orderBy('u.id', 'ASC')
			->setFirstResult($offset)
			->setMaxResults($limit)
			->getQuery()
			->getResult();
		$countQb = clone $qb;
		$total = (int) $countQb
			->select('COUNT(u.id)')
			->resetDQLPart('orderBy')
			->getQuery()
			->getSingleScalarResult();
		return [
			'items' => $items,
			'total' => $total,
		];
	}
}
