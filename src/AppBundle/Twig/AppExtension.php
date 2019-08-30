<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Document;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Visite;
use DateTime;
use Exception;
use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {

        return array(
            new \Twig_SimpleFilter('values', array($this, 'values')),
            new \Twig_SimpleFilter('age', array($this, 'getAge')),
            new \Twig_SimpleFilter('nbInclusions', array($this, 'nbInclusions')),
            new \Twig_SimpleFilter('visiteClass', array($this, 'visiteClass')),
            new \Twig_SimpleFilter('getUrlDocument', array($this, 'getUrlDocument')),
        );
    }

    /**
     * @param $array
     * @return array
     */
    public function values(array $array): array
    {

        return array_values($array);
    }

    /**
     * @param DateTime|null $date
     * @param DateTime|null $deces
     * @return int|null
     * @throws Exception
     */
    public function getAge(?DateTime $date, ?DateTime $deces): ?int
    {
        if (!$date instanceof DateTime) {
            return null;
        }

        if ($deces instanceof DateTime) {
            $referenceDate = $deces->format("d-m-Y");
        } else {
            $referenceDate = date('d-m-Y');
        }

        $referenceDateTimeObject = new DateTime($referenceDate);

        $diff = $referenceDateTimeObject->diff($date);

        return $diff->y;
    }

    /**
     * @param Essais[] $array
     * @return int
     */
    public function nbInclusions($array): int
    {
        $sum = 0;
        foreach ($array as $essai) {
            $sum += count($essai->getInclusions());
        }

        return $sum;
    }

    /**
     * @param Visite $visite
     * @return string
     * @throws Exception
     */
    public function visiteClass(Visite $visite): string
    {
        $date = $visite->getDate();
        if ($date == null) {
            return "";
        }

        $now = new DateTime();
        $interval = $now->diff($date);
        if ($interval->format('%R%a') > 30) {
            return "more-30-days";
        }
        if ($interval->format('%R%a') < 30 && $interval->format('%R%a') > 0) {
            return "less-30-days";
        } else {
            return "past";
        }
    }

    /**
     * @param Document $document
     * @param $directory
     * @return null|string
     */
    public function getUrlDocument(Document $document, $directory): ?string
    {
        if ($document->getFile()) {
            $file_path = '/' . $directory . '/inclusion/' . $document->getInclusion()->getId() . '/' . $document->getFile();
        } else {
            $file_path = null;
        }

        return $file_path;
    }
}