<?php

namespace Hackzilla\Bundle\TicketBundle\Entity\Traits\TicketFeature;


/**
 * Class MessageFromMailTrait
 * @package Hackzilla\Bundle\TicketBundle\Entity\Traits\TicketFeature
 *
 * Trait to store mail details.
 *
 */
trait MessageFromMailTrait
{
    /**
     * @var \DateTime
     */
    private $mailDate;

    /**
     * @var string
     */
    private $messagePlain;

    /**
     * @var string
     */
    private $messageHtml;

    /**
     * @var string
     */
    private $headerRaw;

    /**
     * @var object
     */
    private $from;

    /**
     * @var object
     */
    private $replyTo;

    /**
     * @return \DateTime
     */
    public function getMailDate()
    {
        return $this->mailDate;
    }

    /**
     * @param \DateTime $mailDate
     */
    public function setMailDate($mailDate)
    {
        $this->mailDate = $mailDate;
    }

    /**
     * @return string
     */
    public function getMessagePlain()
    {
        return $this->messagePlain;
    }

    /**
     * @param string $messagePlain
     */
    public function setMessagePlain($messagePlain)
    {
        $this->messagePlain = $messagePlain;
    }

    /**
     * @return string
     */
    public function getMessageHtml()
    {
        return $this->messageHtml;
    }

    /**
     * @param string $messageHtml
     */
    public function setMessageHtml($messageHtml)
    {
        $this->messageHtml = $messageHtml;
    }

    /**
     * @return string
     */
    public function getHeaderRaw()
    {
        return $this->headerRaw;
    }

    /**
     * @param string $headerRaw
     */
    public function setHeaderRaw($headerRaw)
    {
        $this->headerRaw = $headerRaw;
    }

    /**
     * @return object
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param object $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return object
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @param object $replyTo
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
    }
}
