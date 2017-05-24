<?php

namespace Hackzilla\Bundle\TicketBundle\Entity;

use Hackzilla\Bundle\TicketBundle\Entity\Traits\TicketFeature\MessageFromMailTrait;
use Hackzilla\Bundle\TicketBundle\Entity\Traits\TicketMessageTrait;
use Hackzilla\Bundle\TicketBundle\Model\TicketMessageInterface;

/**
 * Ticket Message.
 */
class TicketMessageFromMail implements TicketMessageInterface
{
    use TicketMessageTrait;
    use MessageFromMailTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
