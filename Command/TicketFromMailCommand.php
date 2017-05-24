<?php

namespace Hackzilla\Bundle\TicketBundle\Command;

use Hackzilla\Bundle\TicketBundle\Event\TicketEvent;
use Hackzilla\Bundle\TicketBundle\Model\TicketMessageInterface;
use Hackzilla\Bundle\TicketBundle\TicketEvents;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TicketFromMailCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ticket:create_from_mail')
            ->setDescription('Create Ticket from emails')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //set local from parameters
        $locale = $this->getContainer()->getParameter('locale');
        $this->getContainer()->get('translator')->setLocale($locale);

        //set the user manager with parameter ? Or a special proxy service who load the available user_manager ?
        $userManager = $this->getContainer()->get('fos_user.user_manager');

        $ticketManager = $this->getContainer()->get('hackzilla_ticket.ticket_manager');

        if ($this->getContainer()->getParameter('hackzilla_ticket.from_mail')['imap_validate_crt'] == 'false') {
            $noValidateCert = '/novalidate-cert';
        } else {
            $noValidateCert = '';
        }

        $mailbox = new \PhpImap\Mailbox('{' . $this->getContainer()->getParameter('hackzilla_ticket.from_mail')['imap_server_address'] . ':' . $this->getContainer()->getParameter('hackzilla_ticket.from_mail')['imap_server_port'] . '/imap/ssl' . $noValidateCert . '}INBOX', $this->getContainer()->getParameter('hackzilla_ticket.from_mail')['imap_login'], $this->getContainer()->getParameter('hackzilla_ticket.from_mail')['imap_pwd'], $this->getContainer()->getParameter('vich_uploader.mappings')['ticket_message_attachment']['upload_destination']);

        // Read all messaged into an array:
        $mailsIds = $mailbox->searchMailbox('ALL');

        if (!$mailsIds) {
            //do nothing, but display a message ?
        } else {

            //we search for a system user, or a ticket_system user, or the first user with the TICKET_ADMIN_ROLE
            $owner = $userManager->findUserByUsername('system');
            if (!$owner) {
                $owner = $userManager->findUserByUsername('ticket_system');
                if (!$owner) {
                    //could be bad if we have lot of users
                    $users = $userManager->findUsers();

                    // Add every user with the ROLE_TICKET_ADMIN role
                    /** @var User $user */
                    foreach ($users as $user) {
                        if ($user->hasRole('ROLE_TICKET_ADMIN') || $user->hasRole('ROLE_ADMIN')) {
                            $owner = $user;
                            break;
                        }
                    }
                }
            }

            //for each email
            foreach ($mailsIds as $mailsId) {
                // Get the first message and save its attachment(s) to disk:
                $mail = $mailbox->getMail($mailsId);

                dump($mail->headers->subject);
                //resolv utf8 issues
                $mail->headers->subject = imap_utf8($mail->headers->subject);

                //replyTo or mailFrom = mailTo
                if ($mail->headers->reply_to[0]) {
                    $mailTo = $mail->headers->reply_to[0]->mailbox . "@" . $mail->headers->reply_to[0]->host;
                } else {
                    $mailTo = $mail->headers->from[0]->mailbox . "@" . $mail->headers->from[0]->host;
                }

                //check the mail subject (\[#[0-9]*[\]])
                $result = preg_match('(\[#[0-9]*[\]])', $mail->headers->subject, $ticketRef);

                dump($mail->headers->subject);
                //I find a ticket ref [#xxxx] in the subject, I have to extract the id, and verify if the ticket exists
                if ($result > 0) {
                    //search the ref
                    preg_match('([#]\d*)', $ticketRef[0], $ticketId);
                    //get the number
                    $ticketId = explode('#', $ticketId[0]);
                    dump($ticketId);
                    //check if the ticket exists
                    $ticket = $ticketManager->getTicketById($ticketId[1]);

                    //todo: check and do something with $ticketError ? Could be hack attemps
                    if (!$ticket) {
                        $ticketIdError = true;
                        $newTicket = true;
                    } else {
                        $ticketIdError = false;
                        $newTicket = false;
                    }

                } else {
                    $newTicket = true;
                }

                if ($newTicket) {
                    $ticket = $ticketManager->createTicket();
                    $ticket->setSubject($mail->headers->subject);
                    //we need to link a message in the new ticket
                    $message = $ticketManager->createMessage($ticket);
                }

                $ticket->setUserCreated($owner);
                $ticket->setLastUser($owner);

                //ticket user should be the user owner of the mail, if it's in the db, else we can use the owner
                if ($messageOwner = $userManager->findUserBy(['email' => $mailTo])) {
                    //do nothing because the assignation is done in the if condition, but good practice or not ?
                } else {
                    //reuse current owner of the ticket
                    $messageOwner = $owner;
                }

                $message->setStatus(TicketMessageInterface::STATUS_OPEN)
                    ->setUser($messageOwner)
                    ->setMailDate(new \DateTime($mail->headers->date));

                //update the ticket once, to have a ticket ID if it's a new one, needed for the attachment
                $ticketManager->updateTicket($ticket, $message);

                if ($mail->textPlain) {
                    $message->setMessage($mail->textPlain);
                } else {
                    $message->setMessage(addslashes(strip_tags($mail->textHtml)));
                }
                $message->setMessagePlain($mail->textPlain);
                $message->setMessageHtml($mail->textHtml);
                $message->setHeaderRaw($mail->headersRaw);
                $message->setFrom($mail->headers->from[0]);
                $message->setReplyTo($mail->headers->reply_to[0]);

                //add a listener to create users with minimal infos ? Sort of pre registration

                $message->setUser($messageOwner);

                //managing attchments
                foreach ($mail->getAttachments() as $attachment) {
                    //set the attachment name
                    $message->setAttachmentName($attachment->name);
                    //set the attachemnt file, vich require an UploadedFile object
                    //https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/known_issues.md#no-upload-is-triggered-when-manually-injecting-an-instance-of-symfonycomponenthttpfoundationfilefile
                    $message->setAttachmentName(new UploadedFile($attachment->filePath, $attachment->name));
                }

                //add this message to the current ticket
                $ticket->addMessage($message);

                $ticketManager->updateTicket($ticket, $message);

                $this->getContainer()->get('event_dispatcher')->dispatch(TicketEvents::TICKET_CREATE, new TicketEvent($ticket));

                //mark this mail for deletion
                $mailbox->deleteMail($mailsId);

            } //end of for each loop on mails

            //expunge mails
            $mailbox->expungeDeletedMails();
        }
    } //end else, mailbox is not empty
}
