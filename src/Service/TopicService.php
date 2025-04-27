<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class TopicService
{
	public function __construct(private readonly EntityManagerInterface $em)
	{
	}

	public function countView(int $topicId): void
	{
		$this->em->createQuery('UPDATE App\Entity\Topic t SET t.viewCount = t.viewCount + 1 WHERE t.id = :id')
			->setParameter('id', $topicId)
			->execute();
	}
}