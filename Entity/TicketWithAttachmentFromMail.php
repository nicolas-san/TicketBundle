<?php

namespace Hackzilla\Bundle\TicketBundle\Entity;

use Hackzilla\Bundle\TicketBundle\Entity\Traits\TicketTrait;
use Hackzilla\Bundle\TicketBundle\Model\TicketInterface;

/**
 * Ticket.
 */
class TicketWithAttachmentFromMail implements TicketInterface
{
    use TicketTrait;

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
