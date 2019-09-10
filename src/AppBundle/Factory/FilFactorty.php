<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Fil;
use AppBundle\Form\FilType;
use DateTime;

class FilFactorty implements FactoryInterface
{
    use Factory;

    public function hydrate($fil = null, ?array $params, $k = null)
    {
        if(!$fil) {
            if (isset($params['id'])) {
                $fil = $this->entityManager->getRepository(Fil::class)->find($params['id']);
                if(!$fil)
                    $fil = new Fil();
            }
            else
                $fil = new Fil();
        }
        $request = $this->requestStack->getCurrentRequest();
        if($k !== null)
            $request->request->set("appbundle_fil",$request->request->get("appbundle_fils")[$k]);
        $form = $this->formFactory->create(FilType::class, $fil);
        $form->handleRequest($request);

        $this->validate($fil);

        return $fil;
    }
}