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
     * Send a notification by e-mail to the ROLE_TICKET_ADMIN
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

        // Prepare the email according to the message type, "from mail" or not, it's the create or update action we need to choose the tpl
        switch ($eventName) {
            case TicketEvents::TICKET_CREATE_FROM_MAIL:
            case TicketEvents::TICKET_CREATE:
                $subject = $this->container->get('translator')->trans('emails.ticket.new.subject', array(
                    '%number%' => $ticket->getId(),
                    '%sender%' => $creator->getUsername(),
                ));
                $templateHTML = $this->container->getParameter('hackzilla_ticket.notification.templates')['new_html'];
                $templateTxt = $this->container->getParameter('hackzilla_ticket.notification.templates')['new_txt'];
                break;
            case TicketEvents::TICKET_UPDATE:
            case TicketEvents::TICKET_UPDATE_FROM_MAIL:
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

        //message with attachemnt ?
        if ($message->getAttachmentFile() != null) {
            $attachmentPath = $message->getAttachmentFile()->getRealPath();
        } else {
            $attachmentPath = false;
        }

        /** @var UserManager $userManager */
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        // Prepare the recipients
        // At least the ticket's owner must receive the notification
        $recipients = array();
        $recipientsBcc = array();
        $mailsNotAllowed = $this->container->getParameter('hackzilla_ticket.from_mail')['mails_out_not_allowed'];

        if ($eventName != TicketEvents::TICKET_CREATE_FROM_MAIL && $eventName != TicketEvents::TICKET_UPDATE_FROM_MAIL) {
            //if it's not a ticket by mail, we can notify the user, if it's not the creator
            if ($message->getUser() !== $creator->getId() and false === in_array($creator->getEmail(), $mailsNotAllowed )) {
                $recipients[] = $creator->getEmail();
            }
            $firstMessage = $ticket->getMessages()->first();
            //we have to send to the emails collected in the first sended message from the user
            //replyTo or mailFrom = mailTo
            if ($firstMessage->getReplyTo()) {
                $mailTo = $firstMessage->getReplyTo()->mailbox . "@" . $firstMessage->getReplyTo()->host;
                //add the user to the recipients list
                if (!in_array($mailTo, $mailsNotAllowed)) {
                    $recipientsBcc[] = $mailTo;
                }
            } elseif ($firstMessage->getFrom()) {
                $mailTo = $firstMessage->getFrom()->mailbox . "@" . $firstMessage->getFrom()->host;
                //add the user to the recipients list
                if (!in_array($mailTo, $mailsNotAllowed)) {
                    $recipientsBcc[] = $mailTo;
                }
            }
            //in the case of we have a reply_to or a from mail in the first message of the ticket, we use it to send a notification
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

        //recipient could be false ?
        if (false === $recipients && false === $recipientsBcc) {
            return -1;
        }
        elseif (false === $recipients) {
            $recipients = $recipientsBcc;
        }
        // Prepare email headers
        $mail = $this->prepareEmailMessage(
            $subject,
            $recipients,
            $recipientsBcc,
            $attachmentPath
        );

        // Prepare template args
        $args = array(
            'ticket' => $ticket
        );

        // Create the message body in HTML
        $format = 'text/html';
        $this->addMessagePart($mail, $templateHTML, $args, $format);

        // Create the message body in plain text
        $format = 'text/plain';
        $this->addMessagePart($mail, $templateTxt, $args, $format);

        // Finally send the message
        $this->sendEmailMessage($mail);

        return null;
    }

    /**
     * Send a notification by e-mail to the ticket user
     *
     * @param TicketWithAttachment $ticket
     * @param string $eventName
     * @return null
     */
    public function sendTicketUserNotificationEmailMessage(TicketInterface $ticket)
    {
        // Retrieve the creator
        /** @var User $creator */
        //creator is system or the first user with role_ticket_admnin
        $creator = $ticket->getUserCreatedObject();

        $subject = $this->container->get('translator')->trans('emails.ticket.from.mail.new.subject', array(
            '%number%' => $ticket->getId(),
            '%sender%' => $creator->getUsername(),
        ));
        $templateHTML = $this->container->getParameter('hackzilla_ticket.from_mail')['templates']['new_html'];
        $templateTxt = $this->container->getParameter('hackzilla_ticket.from_mail')['templates']['new_txt'];

        /** @var TicketMessage $message */
        //we take the first here, to have the mail headers from the first sended message
        $firstMessage = $ticket->getMessages()->first();

        /** @var UserManager $userManager */
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        // Prepare the recipients
        // At least the ticket's owner must receive the notification
        $recipients = false;
        $recipientsBcc = false;

        $mailsNotAllowed = $this->container->getParameter('hackzilla_ticket.from_mail')['mails_out_not_allowed'];

        if ($firstMessage->getUser() !== $creator->getId() and false === in_array($creator->getEmail(), $mailsNotAllowed ) ) {
            $recipientsBcc[] = $creator->getEmail();
        } else {
            //we have to send to the emails collected in the first sended message from the user
            //replyTo or mailFrom = mailTo
            if ($firstMessage->getReplyTo() and ('noreply' != $firstMessage->getReplyTo()->mailbox or 'no-reply' != $firstMessage->getReplyTo()->mailbox) ) {
                $mailTo = $firstMessage->getReplyTo()->mailbox . "@" . $firstMessage->getReplyTo()->host;
                //add the user to the recipients list
                if (!in_array($mailTo, $mailsNotAllowed)) {
                    $recipients[] = $mailTo;
                }
            } elseif ($firstMessage->getFrom() and ('noreply' != $firstMessage->getFrom()->mailbox or 'no-reply' != $firstMessage->getFrom()->mailbox) ) {
                $mailTo = $firstMessage->getFrom()->mailbox . "@" . $firstMessage->getFrom()->host;
                //add the user to the recipients list
                if (!in_array($mailTo, $mailsNotAllowed)) {
                    $recipients[] = $mailTo;
                }
            }
            //if we dont have the reply_to or the from we do nothing
        }

        //we always send the messages to the user whe send the first email, and BCC to others
        //recipient could be false ?
        if (false === $recipients && false === $recipientsBcc) {
            return -1;
        }
        elseif (false === $recipients) {
            $recipients = $recipientsBcc;
        }

        $message = $this->prepareEmailMessage(
            $subject,
            $recipients,
            $recipientsBcc
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
     * @param $bcc
     *
     * @return \Swift_Mime_SimpleMessage
     */
    private function prepareEmailMessage($subject, $to, $bcc = false, $attachmentPath = false)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array(
                $this->container->getParameter('hackzilla_ticket.notification.emails')['sender_email']
                => $this->container->getParameter('hackzilla_ticket.notification.emails')['sender_name']
            ))
            ->setTo($to);

        if (false !== $bcc) {
            $message->setBcc($bcc);
        }

        if (false !== $attachmentPath) {
            $message->attach(\Swift_Attachment::fromPath($attachmentPath));
        }
        // Prepare a confirmation e-mail
        return $message;
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