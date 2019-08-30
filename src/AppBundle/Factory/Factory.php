<?php

namespace AppBundle\Factory;

use AppBundle\Services\ValidatorToArray;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\ConstraintViolation;
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
    public function validate($entity)
    {
        $errors = $this->validator->validate($entity);

        if ($errors->count()) {
            $this->logger->error(get_class($entity)." non cohérent", $this->validatorToArray->toArray($errors));
        }

        $errorsMessage = [];
        /* @var ConstraintViolation $error */
        foreach ($errors as $error) {
            if ($error->getConstraint() instanceof File)
                continue;
            $errorsMessage[] = $error->getMessage();
        }
        $entity->errorsMessage = implode("<br>",$errorsMessage);
    }
}