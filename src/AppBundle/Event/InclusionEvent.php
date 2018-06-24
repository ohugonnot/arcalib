<?php


namespace AppBundle\Event;

use AppBundle\Entity\Inclusion;
use Symfony\Component\EventDispatcher\Event;

class InclusionEvent extends Event
{

    /**
     * @var Inclusion
     */
    private $inclusion;

    public function __construct(Inclusion $inclusion = null)
    {
        $this->inclusion = $inclusion;
    }

    public function getInclusion()
    {
        return $this->inclusion;
    }
}