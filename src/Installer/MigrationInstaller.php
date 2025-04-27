<?php

namespace App\Installer;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

class MigrationInstaller
{
	public function __construct(private KernelInterface $kernel)
	{
	}

	public function run(SymfonyStyle $io): void
	{
		$io->section('1. Database Migration');
		$application = new Application($this->kernel);
		$application->setAutoExit(false);
		$this->kernel->getContainer()->get('doctrine')->getConnection()->setNestTransactionsWithSavepoints(false);

		$input = new ArrayInput([
			'command'          => 'doctrine:migrations:migrate',
			'--no-interaction' => true,
		]);
		$returnCode = $application->run($input, $io);
		if ($returnCode !== 0) {
			$io->error('Migration failed with exit code ' . $returnCode . '. Please check your configuration.');
			throw new \RuntimeException('Migration failed.');
		}
		$io->success('Migration succeeded.');
	}
}