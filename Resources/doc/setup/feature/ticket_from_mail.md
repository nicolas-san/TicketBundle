# Optional Feature

## Ticket from mail

Allow the bundle to connect to an imap server, get the messages and create one ticket with one message, 
or a new message in a valid ticket.

Ticket from mail feature __REQUIRE__ the notification feature enabled.

If you use it __with__ the attachment feature:
```yaml
hackzilla_ticket:
  ticket_class:           Hackzilla\Bundle\TicketBundle\Entity\TicketWithAttachmentFromMail
  message_class:          Hackzilla\Bundle\TicketBundle\Entity\TicketMessageWithAttachmentFromMail
```
If you use it __without__ the attachment feature:
```yaml
hackzilla_ticket:
  ticket_class:           Hackzilla\Bundle\TicketBundle\Entity\TicketFromMail
  message_class:          Hackzilla\Bundle\TicketBundle\Entity\TicketFromMail
```
Enable features, and add configuration values:  
```yaml
  features:
    from_mail: true
    notification: true
  from_mail:
    imap_login: 'user@domain.tld'
    imap_pwd: 'passwd'
    imap_server_address: 'imap.domain.tld'
    imap_server_port: '993'
    imap_validate_crt: 'false' #if you need to test on self signed cert for example
  notification:
    emails:
        sender_email:   'sender@doamin.tld'
        sender_name:    'Support team'
    templates:
        new_html:       'YourBundle:Emails:ticket.new.html.twig'
        new_txt:        'YourBundle:Emails:ticket.new.txt.twig'
        update_html:    'YourBundle:Emails:ticket.update.html.twig'
        update_txt:     'YourBundle:Emails:ticket.update.txt.twig'

```

You can override the base templates.