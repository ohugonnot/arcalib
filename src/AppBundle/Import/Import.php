<?php

namespace AppBundle\Import;

use AppBundle\Services\CsvToArray;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

trait Import
{
	/**
	 * @var CsvToArray
	 */
	private $csvToArray;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var KernelInterface
	 */
	private $kernel;

	/**
	 * Import constructor.
	 * @param CsvToArray $csvToArray
	 * @param EntityManagerInterface $entityManager
	 * @param KernelInterface $kernel
	 */
	public function __construct(CsvToArray $csvToArray, EntityManagerInterface $entityManager, KernelInterface $kernel)
	{
		$this->csvToArray = $csvToArray;
		$this->entityManager = $entityManager;
		$this->kernel = $kernel;
	}
}