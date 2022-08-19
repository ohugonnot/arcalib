<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Log;
use AppBundle\Services\LogManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;

class DoctrineEventListener implements EventSubscriber
{
    const UPDATE = "edition";
    const PERSIST = "ajout";
    const REMOVE = "suppression";

    /**
     * @var LogManager
     */
    private $logManager;

    private $changeSet;

    /**
     * @var RequestStack
     */
    private $requestStack;


    public function __construct(LogManager $logManager, RequestStack $requestStack)
    {
        $this->logManager = $logManager;
        $this->requestStack = $requestStack;
    }

    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postUpdate',
            'preUpdate',
            'preRemove',
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Log || isset($this->changeSet["lastLogin"]))
            return;
        $reflection = new \ReflectionClass($entity);
        $type = $reflection->getShortName();

        $this->logManager->save(
            ucfirst($type),
            $entity,
            self::PERSIST,
            $this->info($type, $entity, self::PERSIST)
        );
    }

    private function info($type, $entity, $action)
    {
        return ucfirst($action) . " " . strtolower($type) . " [" . $this->getName($entity) . "]";
    }

    private function getName($entity)
    {
        if (method_exists($entity, "getNumInc") && $entity->getNumInc())
            return $entity->getNumInc();
        if (method_exists($entity, "getNomPrenom"))
            return $entity->getNomPrenom();
        if (method_exists($entity, "getNom") && method_exists($entity, "getPrenom"))
            return $entity->getNom() . " " . $entity->getPrenom();
        if (method_exists($entity, "getNom") && $entity->getNom())
            return $entity->getNom();
        if (method_exists($entity, "getNumero") && $entity->getNumero())
            return $entity->getNumero();
        if (method_exists($entity, "getLibCourt") && $entity->getLibCourt())
            return $entity->getLibCourt();
        if (method_exists($entity, "getTitre") && $entity->getTitre())
            return $entity->getTitre();
        if (method_exists($entity, "getUsername") && $entity->getUsername())
            return $entity->getUsername();
        if (method_exists($entity, "getId"))
            return $entity->getId();
        return '';
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Log || isset($this->changeSet["lastLogin"]))
            return;

        $reflection = new \ReflectionClass($entity);
        $type = $reflection->getShortName();

        if ($entity instanceof Log)
            return;

        $this->logManager->save(
            ucfirst($type),
            $entity,
            self::REMOVE,
            $this->info($type, $entity, self::REMOVE)
        );
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Log || isset($this->changeSet["lastLogin"]))
            return;

        $this->changeSet = $args->getEntityChangeSet();
        foreach ($this->changeSet as $key => $param) {

            foreach ($param as $key2 => $value)
                if ($value instanceof \DateTime) {
                    /** @var \DateTime $value */
                    $time = $value->format('Hi');
                    if ($time == '0000')
                        $this->changeSet[$key][$key2] = $value->format("d-m-Y");
                    else
                        $this->changeSet[$key][$key2] = $value->format("d-m-Y H:i");
                }
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Log || isset($this->changeSet["lastLogin"]))
            return;
        
        $request = $this->requestStack->getCurrentRequest();
        $route = $request->get("_route");
        if ($route === "updateDuree")
            return;

        $reflection = new \ReflectionClass($entity);
        $type = $reflection->getShortName();

        $this->logManager->save(
            ucfirst($type),
            $entity,
            self::UPDATE,
            $this->info($type, $entity, self::UPDATE),
            json_encode($this->changeSet, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

}