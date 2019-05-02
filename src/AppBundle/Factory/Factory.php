<?php
/**
 * Created by PhpStorm.
 * User: folken
 * Date: 02/05/2019
 * Time: 17:08
 */

namespace AppBundle\Factory;


use AppBundle\Services\ValidatorToArray;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait Factory
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var ValidatorInterface
	 */
	private $validator;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var ValidatorToArray
	 */
	private $validatorToArray;

	/**
	 * @var FormFactoryInterface
	 */
	private $formFactory;

	/**
	 * @var RequestStack
	 */
	private $requestStack;


	public function __construct(EntityManagerInterface $entityManager,
	                            ValidatorInterface $validator,
	                            LoggerInterface $logger,
	                            ValidatorToArray $validatorToArray,
	                            FormFactoryInterface $formFactory,
	                            RequestStack $requestStack)
	{

		$this->entityManager = $entityManager;
		$this->validator = $validator;
		$this->logger = $logger;
		$this->validatorToArray = $validatorToArray;
		$this->formFactory = $formFactory;;
		$this->requestStack = $requestStack;
	}
}