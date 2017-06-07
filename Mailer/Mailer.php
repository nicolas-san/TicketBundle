<?php

/**
 * @License MIT License
 *
 * @Copyright (c) 2016 Florent DAQUET
 */

namespace Hackzilla\Bundle\TicketBundle\Mailer;

use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Model\User;
use Hackzilla\Bundle\TicketBundle\Entity\TicketMessage;
use Hackzilla\Bundle\TicketBundle\Entity\TicketWithAttachment;
use Hackzilla\Bundle\TicketBundle\Model\TicketInterface;
use Hackzilla\Bundle\TicketBundle\TicketEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Hackzilla\Bundle\TicketBundle\Component\TicketFeatures;

/**
 * Class Mailer
 *
 */
class Mailer
{
    /**
     * @var ContainerInterface
     */
    private $container;
    private $features;

    /**
     * Mailer constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, TicketFeatures $features)
    {
        $this->container = $container;
        $this->features = $features;
    }

    /**
     * Send a notification by e-mail to the concerned users when a ticket has been created|modified|deleted.
     * Send e-mail needed for the fromMail feature, if enabled
     *
     * @param TicketWithAttachment $ticket
     * @param string $eventName
     * @return null
     */
    public function sendTicketNotificationEmailMessage(TicketInterface $ticket, $eventName)
    {
        // Retrieve the creator
        /** @var User $creator */
        $creator = $ticket->getUserCreatedObject();

        // Prepare the email according to the message type
        switch ($eventName) {
            case TicketEvents::TICKET_CREATE:
                $subject = $this->container->get('translator')->trans('emails.ticket.new.subject', array(
                    '%number%' => $ticket->getId(),
                    '%sender%' => $creator->getUsername(),
                ));
                $templateHTML = $this->container->getParameter('hackzilla_ticket.notification.templates')['new_html'];
                $templateTxt = $this->container->getParameter('hackzilla_ticket.notification.templates')['new_txt'];
                break;
            case TicketEvents::TICKET_UPDATE:
                $subject = $this->container->get('translator')->trans('emails.ticket.update.subject', array(
                    '%number%' => $ticket->getId(),
                    '%sender%' => $creator->getUsername(),
                ));
                $templateHTML = $this->container->getParameter('hackzilla_ticket.notification.templates')['update_html'];
                $templateTxt = $this->container->getParameter('hackzilla_ticket.notification.templates')['update_txt'];
                break;
            default:
                return null;
        }

        /** @var TicketMessage $message */
        $message = $ticket->getMessages()->last();

        /** @var UserManager $userManager */
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        // Prepare the recipients
        // At least the ticket's owner must receive the notification
        $recipients = array();

        if ($message->getUser() !== $creator->getId()) {
            $recipients[] = $creator->getEmail();
        }

        // Add every user with the ROLE_TICKET_ADMIN role
        /** @var User $user */
        foreach ($users as $user) {
            if ($user->hasRole('ROLE_TICKET_ADMIN')) {
                if (!in_array($user->getEmail(), $recipients) && $message->getUser() !== $user->getId()) {
                    $recipients[] = $user->getEmail();
                }
            }
        }

        //if the from_mail functionality is active, we send the ticket messages to the user email who open the ticket
        if ($this->features->hasFeature('from_mail')) {
            //always take the first message, because it has the mail adresses of the user
            $firstMessage = $ticket->getMessages()->first();

            //replyTo or mailFrom = mailTo
            if ($firstMessage->getReplyTo()) {
                $mailTo = $firstMessage->getReplyTo()->mailbox . "@" . $firstMessage->getReplyTo()->host;
                //add the user to the recipients list
                $recipients[] = $mailTo;
            } elseif ($firstMessage->getFrom()) {
                $mailTo = $firstMessage->getFrom()->mailbox . "@" . $firstMessage->getFrom()->host;
                //add the user to the recipients list
                $recipients[] = $mailTo;
            }
            //if we dont have the reply_to or the from we do nothing
        }

        // Prepare email headers
        $message = $this->prepareEmailMessage(
            $subject,
            $recipients
        );

        // Prepare template args
        $args = array(
            'ticket' => $ticket
        );

        // Create the message body in HTML
        $format = 'text/html';
        $this->addMessagePart($message, $templateHTML, $args, $format);

        // Create the message body in plain text
        $format = 'text/plain';
        $this->addMessagePart($message, $templateTxt, $args, $format);

        // Finally send the message
        $this->sendEmailMessage($message);

        return null;
    }

    /**
     * Prepare an e-mail message.
     *
     * @param $subject
     * @param $to
     * @return \Swift_Mime_SimpleMessage
     */
    private function prepareEmailMessage($subject, $to)
    {
        // Prepare a confirmation e-mail
        return \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array(
                $this->container->getParameter('hackzilla_ticket.notification.emails')['sender_email']
                => $this->container->getParameter('hackzilla_ticket.notification.emails')['sender_name']
            ))
            ->setTo($to);
    }

    /**
     * Add content to the e-mail message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @param $template
     * @param $args
     * @param $format
     */
    private function addMessagePart(\Swift_Mime_SimpleMessage &$message, $template, $args, $format)
    {
        switch ($format) {
            case 'text/plain':
                $message->addPart(
                    $this->container->get('twig')->render($template, $args),
                    $format
                );
                break;
            case 'text/html':
            default:
                $message->setBody(
                    $this->container->get('twig')->render($template, $args),
                    $format
                );
                break;
        }
    }

    /**
     * Send the e-mail message.
     *
     * @param $message
     */
    private function sendEmailMessage($message)
    {
        $this->container->get('mailer')->send($message);
    }
}