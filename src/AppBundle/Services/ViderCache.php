<?php

namespace AppBundle\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ViderCache
{

	public function viderCache()
	{
		$process = new Process('chmod -R 777 ./../var/cache');
		$process->run();
		$message[] = $process->getOutput();

		$process = new Process('php ./../bin/console cache:clear');
		$process->run();
		if (!$process->isSuccessful())
			throw new ProcessFailedException($process);
		$message[] = $process->getOutput();

		$process = new Process('php ./../bin/console cache:clear --env=prod --no-debug');
		$process->run();
		if (!$process->isSuccessful())
			throw new ProcessFailedException($process);
		$message[] = $process->getOutput();

		$process = new Process('chmod -R 777 ./../var/cache');
		$process->run();
		$message[] = $process->getOutput();

		return $message;
	}
}