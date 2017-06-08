<?php
/**
 * @License MIT License
 *
 * @Copyright (c) 2016 Florent DAQUET
 * @Copyright (c) 2017 Nicolas Bouteillier
 *
 */

namespace Hackzilla\Bundle\TicketBundle\EventListener;

use Hackzilla\Bundle\TicketBundle\Mailer\Mailer;
use Hackzilla\Bundle\TicketBundle\Event\TicketEvent;
use Hackzilla\Bundle\TicketBundle\TicketEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class TicketSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     *
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            TicketEvents::TICKET_CREATE => 'ticketNotification',
            TicketEvents::TICKET_CREATE_FROM_MAIL => 'ticketNotification',
            TicketEvents::TICKET_UPDATE => 'ticketNotification',
        );
    }

    /**
     * Send a notification e-mail message when a ticket has been created|modified.
     *
     * @param TicketEvent $event
     * @param string $eventName
     */
    public function ticketNotification(TicketEvent $event, $eventName)
    {
        $ticketFeature = $this->container->get('hackzilla_ticket.features');
        $mailer = false;

        if ($ticketFeature->hasFeature('notification')) {
            $mailer = $this->container->get('hackzilla_ticket.notification.mailer');
            /** @var Mailer $mailer */
            $mailer->sendTicketNotificationEmailMessage($event->getTicket(), $eventName);
        }
        //if the ticket from mail feature is activated we send a mail to the user
        //if it's a new ticket from mail
        if (TicketEvents::TICKET_CREATE_FROM_MAIL == $event) {
            //no need to test if the feature is enabled, if not this event never occurs
            if (!$mailer) {
                //get the mailer, is it better to get the mailer outside the ifs, all the time instead of doing $mailer = false, and this if ?
                $mailer = $this->container->get('hackzilla_ticket.notification.mailer');
            }

            /** @var Mailer $mailer */
            $mailer->sendTicketUserNotificationEmailMessage($event->getTicket(), $eventName);
        }
    }

}