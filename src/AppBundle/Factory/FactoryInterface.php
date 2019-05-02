<?php

namespace AppBundle\Factory;


interface FactoryInterface
{
	public function hydrate($entity, ?array $params);
	public function validate($entity);
}