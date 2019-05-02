<?php

namespace AppBundle\Import;

interface ImportInterface
{
	public function import(bool $checkIfExist = true, bool $truncate = true): void;
}