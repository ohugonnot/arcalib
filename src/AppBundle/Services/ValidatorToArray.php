<?php

namespace AppBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidatorToArray
{
	public function toArrayCollection(ConstraintViolationListInterface $list) : ArrayCollection
	{

		return new ArrayCollection($this->toArray($list));
	}
    public function toArray(ConstraintViolationListInterface $list) : array
    {
        $errorArray = [];

        foreach($list as $error)
        {
            $errorArray[$error->getPropertyPath()] = $error->getMessage();
        }

        return $errorArray;
    }
}