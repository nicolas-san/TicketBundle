<?php

namespace Hackzilla\Bundle\TicketBundle\Entity;

use Hackzilla\Bundle\TicketBundle\Entity\Traits\TicketFeature\MessageAttachmentTrait;
use Hackzilla\Bundle\TicketBundle\Entity\Traits\TicketFeature\MessageFromMailTrait;
use Hackzilla\Bundle\TicketBundle\Entity\Traits\TicketMessageTrait;
use Hackzilla\Bundle\TicketBundle\Model\TicketFeature\MessageAttachmentInterface;
use Hackzilla\Bundle\TicketBundle\Model\TicketMessageInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Ticket Message.
 * @Vich\Uploadable
 */
class TicketMessageWithAttachmentFromMail implements TicketMessageInterface, MessageAttachmentInterface
{
    use TicketMessageTrait;
    use MessageAttachmentTrait;
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
