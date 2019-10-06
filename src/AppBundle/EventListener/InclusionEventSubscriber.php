<?php

namespace AppBundle\EventListener;

use AppBundle\Event\ArcalibEvents;
use AppBundle\Event\InclusionEvent;
use AppBundle\Services\LogManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InclusionEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var LogManager
     */
    private $logManager;

    public function __construct(LogManager $logManager)
    {

        $this->logManager = $logManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            ArcalibEvents::AFTER_DELETE_INCLUSION => 'onAfterDeleteInclusion',
            ArcalibEvents::ADD_INCLUSION => 'onAddInclusion',
            ArcalibEvents::EDIT_INCLUSION => 'onEditInclusion',
            ArcalibEvents::DOWNLOAD_INCLUSIONS => 'onDownloadInclusions'
        ];
    }

    public function onAddInclusion(InclusionEvent $event)
    {
    }

    public function onAfterDeleteInclusion(InclusionEvent $event)
    {
    }

    public function onDownloadInclusions()
    {
    }

    public function onEditInclusion(InclusionEvent $event)
    {
    }
}