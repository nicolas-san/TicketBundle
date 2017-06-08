<?php

namespace Hackzilla\Bundle\TicketBundle;

final class TicketEvents
{
    /**
     * The hackzilla.ticket.create event is thrown each time an ticket is created
     * in the system.
     *
     * The hackzilla.ticket.update event is thrown each time an ticket is updated
     * in the system.
     *
     * The hackzilla.ticket.delete event is thrown each time an ticket is deleted
     * in the system.
     *
     * The event listeners receives an
     * Hackzilla\Bundle\TicketBundle\Event\TicketEvent instance.
     *
     * @var string
     */
    const TICKET_CREATE = 'hackzilla.ticket.create';
    const TICKET_CREATE_FROM_MAIL = 'hackzilla.ticket.create.from.mail';
    const TICKET_UPDATE = 'hackzilla.ticket.update';
    const TICKET_UPDATE_FROM_MAIL = 'hackzilla.ticket.update.from.mail';
    const TICKET_DELETE = 'hackzilla.ticket.delete';
}
