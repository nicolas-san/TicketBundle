<?php
/**
 * @License MIT License
 *
 * @Copyright (c) 2016 Florent DAQUET
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

        if ($ticketFeature->hasFeature('from_mail') || $ticketFeature->hasFeature('notification')) {
            $mailer = $this->container->get('hackzilla_ticket.notification.mailer');
            //the mailer service send notification, fromMail feature mails or both, depends on configuration
            /** @var Mailer $mailer */
            $mailer->sendTicketNotificationEmailMessage($event->getTicket(), $eventName);
        }
    }

}