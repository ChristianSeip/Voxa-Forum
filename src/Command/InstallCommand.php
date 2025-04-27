<?php


namespace App\Command;

use App\Installer\MigrationInstaller;
use App\Installer\RoleInstaller;
use App\Installer\UserInstaller;
use App\Installer\ForumInstaller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
	name: 'app:install',
	description: 'Performs the initial installation of the software.'
)]
class InstallCommand extends Command
{
	public function __construct(
		private readonly MigrationInstaller $migrationInstaller,
		private readonly RoleInstaller      $roleInstaller,
		private readonly UserInstaller      $userInstaller,
		private readonly ForumInstaller     $forumInstaller,
		private EntityManagerInterface $em
	)
	{
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);
		$io->title('Install routine started');
		$this->migrationInstaller->run($io);
		$this->roleInstaller->run($io);
		$this->userInstaller->run($io);
		$this->forumInstaller->run($io);
		$this->em->flush();
		$io->success('Install routine finished!');
		return Command::SUCCESS;
	}
}