<?php

namespace AppBundle\Services;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidatorToArray
{
	public function toArray(ConstraintViolationListInterface $list): array
	{
		$errorArray = array();

		foreach($list as $error)
		{
			$errorArray[$error->getPropertyPath()] = $error->getMessage();
		}

		return $errorArray;
	}
}