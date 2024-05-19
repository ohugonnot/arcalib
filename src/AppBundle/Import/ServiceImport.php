<?php

namespace AppBundle\Import;

use AppBundle\Entity\Service;

class ServiceImport implements ImportInterface
{
    use Import;

    public function import(bool $checkIfExist = true, bool $truncate = false): void
    {
        $emService = $this->entityManager->getRepository(Service::class);

        if ($truncate) {
            $this->entityManager->createQuery('DELETE AppBundle:Service s')->execute();
        }

        $file = $this->kernel->getRootDir() . '/../bdd/service.csv';
        if (!file_exists($file)) {
            echo "Pas de service a importé<br>";
            return;
        }
        $services = $this->csvToArray->convert($file, ";");

        $bulkSize = 500;
        $i = 0;
        foreach ($services as $s) {
            $i++;
            $service = false;

            if (empty($s["SERVICE"])) {
                continue;
            }

            foreach ($s as $k => $v) {
                $s[$k] = trim($v);
            }

            if ($checkIfExist) {
                $exist = $emService->findOneBy(["nom" => $s["SERVICE"]]);
                if ($exist) {
                    $service = $exist;
                }
            }

            if (!$service) {
                $service = new Service();
            }

            $service->setNom($s["SERVICE"]);
            $this->entityManager->persist($service);

            if ($i % $bulkSize == 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}