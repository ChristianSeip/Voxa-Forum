<?php


namespace App\Installer;

use App\Entity\Forum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ForumInstaller
{
	public function __construct(private EntityManagerInterface $em,)
	{
	}

	public function run(SymfonyStyle $io): void
	{
		$io->section('5. Create Dummy Forums');
		$category = new Forum();
		$category->setName('General Discussion');
		$category->setDescription('General discussion forums');
		$category->setIsHidden(false);
		$category->setLocked(false);
		$category->setPosition(1);
		$this->em->persist($category);

		$forum = new Forum();
		$forum->setName('Welcome');
		$forum->setDescription('Welcome to your new forum.');
		$forum->setIsHidden(false);
		$forum->setLocked(false);
		$forum->setPosition(1);
		$forum->setParent($category);
		$this->em->persist($forum);

		$io->success('Forums created successfully.');
	}
}
