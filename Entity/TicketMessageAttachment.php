<?php
/**
 * @author Bouteillier Nicolas <contact@kaizendo.fr>
 * Date: 09/06/17
 */

namespace Hackzilla\Bundle\TicketBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Attachment
 *
 * @Vich\Uploadable
 */

class TicketMessageAttachment
{
    /**
     * @var int
     *
     */
    private $id;

    /**
     */
    private $message;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="ticket_message_attachment", fileNameProperty="attachmentFilename")
     *
     * @var File
     */
    protected $attachmentFile;

    /**
     * @var string
     */
    protected $attachmentFilename;

    /**
     */
    private $updatedAt;

    /**
     * @return File
     */
    public function getAttachmentFile()
    {
        return $this->attachmentFile;
    }

    /**
     * @param File $attachmentFile
     */
    public function setAttachmentFile($attachmentFile)
    {
        $this->attachmentFile = $attachmentFile;
    }

    /**
     * @return string
     */
    public function getAttachmentFilename()
    {
        return $this->attachmentFilename;
    }

    /**
     * @param string $attachmentFilename
     */
    public function setAttachmentFilename($attachmentFilename)
    {
        $this->attachmentFilename = $attachmentFilename;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}