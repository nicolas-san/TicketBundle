<?php

namespace Hackzilla\Bundle\TicketBundle\Event;

use Hackzilla\Bundle\TicketBundle\Model\TicketInterface;
use Hackzilla\Bundle\TicketBundle\Model\TicketMessageInterface;
use Symfony\Component\EventDispatcher\Event;

class TicketEvent extends Event
{
    protected $ticket;
    protected $message;

    public function __construct(TicketInterface $ticket, TicketMessageInterface $message = null)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    public function getTicket()
    {
        return $this->ticket;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
