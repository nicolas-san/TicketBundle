<?php

namespace Hackzilla\Bundle\TicketBundle\Model\TicketFeature;

use Hackzilla\Bundle\TicketBundle\Model\TicketMessageInterface;
use Symfony\Component\HttpFoundation\File\File;

interface MessageAttachmentFromMailInterface extends TicketMessageInterface
{
    /**
     * @return \DateTime
     */
    public function getMailDate();

    /**
     * @param \DateTime $mailDate
     */
    public function setMailDate($mailDate);

    /**
     * @return string
     */
    public function getMessagePlain();

    /**
     * @param string $messagePlain
     */
    public function setMessagePlain($messagePlain);

    /**
     * @return string
     */
    public function getMessageHtml();

    /**
     * @param string $messageHtml
     */
    public function setMessageHtml($messageHtml);

    /**
     * @return string
     */
    public function getHeaderRaw();

    /**
     * @param string $headerRaw
     */
    public function setHeaderRaw($headerRaw);

    /**
     * @return object
     */
    public function getFrom();

    /**
     * @param object $from
     */
    public function setFrom($from);

    /**
     * @return object
     */
    public function getReplyTo();

    /**
     * @param object $replyTo
     */
    public function setReplyTo($replyTo);
}
