<?php

namespace App\Installer;

use App\Entity\Post;
use App\Entity\Topic;
use App\Repository\ForumRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ContentInstaller
{
	public function __construct(private EntityManagerInterface $em, private UserRepository $userRepository, private ForumRepository $forumRepository)
	{
	}

	public function run(SymfonyStyle $io): void
	{
		$io->section('7. Create Welcome Topic');
		$admin = $this->userRepository->find(2);
		if (!$admin) {
			$io->error('Admin user with ID 2 not found.');
			return;
		}

		$forum = $this->forumRepository->findOneBy(['name' => 'Welcome']);
		if (!$forum) {
			$io->error('Target forum "Welcome" not found.');
			return;
		}

		$topic = new Topic();
		$topic->setTitle('Welcome to Your Forum');
		$topic->setForum($forum);
		$topic->setAuthor($admin);
		$topic->setCreatedAt(new \DateTimeImmutable());
		$topic->setLastPostAt(new \DateTimeImmutable());
		$topic->setLastPoster($admin);
		$topic->setPostCount(1);
		$topic->setViewCount(0);
		$this->em->persist($topic);

		$post = new Post();
		$post->setTopic($topic);
		$post->setAuthor($admin);
		$post->setContent("This is your first post![br][br]Feel free to edit or delete this topic.");
		$post->setCreatedAt(new \DateTimeImmutable());
		$post->setIpAddress('::1');
		$this->em->persist($post);


		$io->success('Welcome topic and post created successfully.');
	}
}
