<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Arc;
use AppBundle\Entity\EssaiDetail;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Tag;
use AppBundle\Form\EssaisType;

class EssaiFactory implements FactoryInterface
{

    use Factory;

    /**
     * @param Essais $essai
     * @param array|null $params
     * @return Essais
     */
    public function hydrate($essai, ?array $params = [])
    {
        $form = $this->formFactory->create(EssaisType::class, $essai);
        $form->handleRequest($this->requestStack->getCurrentRequest());

        if (!$params) {
            return $essai;
        }

        if (isset($params["detail"])) {
            $essaiDetail = $this->entityManager->getRepository(EssaiDetail::class)->find($params["detail"]["id"]);

            if (!$essaiDetail) {
                $essaiDetail = new EssaiDetail();
            }

            $essaiDetail->setCrInc(($params["detail"]["crInc"] != '') ? $params["detail"]["crInc"] : null);
            $essaiDetail->setCrNonInc(($params["detail"]["crNonInc"] != '') ? $params["detail"]["crNonInc"] : null);
            $essaiDetail->setObjectif(($params["detail"]["objectif"] != '') ? $params["detail"]["objectif"] : null);
            $essaiDetail->setCalendar(($params["detail"]["calendar"] != '') ? $params["detail"]["calendar"] : null);
            $essai->setDetail($essaiDetail);
        }

        if (isset($params["tags"]) && is_string($params["tags"][0])) {
            $essai->clearTags();

            foreach ($params["tags"] as $tag) {

                if ($tag != '' and $tag != null) {
                    $tagExist = $this->entityManager->getRepository(Tag::class)->findOneBy(["nom" => $tag]);

                    if (!$tagExist) {
                        $tagExist = new Tag();
                        $tagExist->setNom($tag);
                        $this->entityManager->persist($tagExist);
                        $this->entityManager->flush();
                    }

                    $essai->addTag($tagExist);
                }
            }
        } elseif (!isset($params["tags"])) {
            $essai->clearTags();
        }

        $medecin = $this->entityManager->getRepository(Medecin::class)->find(isset($params["medecin"]["id"]) ? $params["medecin"]["id"] : 0);
        $essai->setMedecin($medecin);

        $arc = $this->entityManager->getRepository(Arc::class)->find(isset($params["arc"]["id"]) ? $params["arc"]["id"] : 0);
        $essai->setArc($arc);

        $arcBackup = $this->entityManager->getRepository(Arc::class)->find(isset($params["arcBackup"]["id"]) ? $params["arcBackup"]["id"] : 0);
        $essai->setArcBackup($arcBackup);

        foreach ($params as $key => $value) {
            if (is_array($value) || $value == '') {
                unset($params[$key]);
            }
        }

        if (isset($params["eudraCtNd"]) and $params["eudraCtNd"] == "true") {
            $essai->setEudraCtNd(true);
        } else {
            $essai->setEudraCtNd(false);
        }

        if (isset($params["ctNd"]) and $params["ctNd"] == "true") {
            $essai->setCtNd(true);
        } else {
            $essai->setCtNd(false);
        }

        if (isset($params["cancer"]) and $params["cancer"] == "true") {
            $essai->setCancer(true);
        } else {
            $essai->setCancer(false);
        }

        if (isset($params["sigaps"]) and $params["sigaps"] == "true") {
            $essai->setSigaps(true);
        } else {
            $essai->setSigaps(false);
        }

        if (isset($params["sigrec"]) and $params["sigrec"] == "true") {
            $essai->setSigrec(true);
        } else {
            $essai->setSigrec(false);
        }

        if (isset($params["emrc"]) and $params["emrc"] == "true") {
            $essai->setEmrc(true);
        } else {
            $essai->setEmrc(false);
        }

        $this->validate($essai);

        return $essai;
    }
}