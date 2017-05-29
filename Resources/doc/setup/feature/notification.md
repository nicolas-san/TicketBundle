# Optional Feature

## Notification

From original addon [flodaq/ticket-notification-bundle]https://github.com/flodaq/TicketNotificationBundle


Enable notification feature, and add some configuration values:

```yaml
hackzilla_ticket:
  features:
    notification: true
  notification:
    emails:
        sender_email:   'sender@domain.tld'
        sender_name:    'Support team'
    templates:
        new_html:       'YourBundle:Emails:ticket.new.html.twig'
        new_txt:        'YourBundle:Emails:ticket.new.txt.twig'
        update_html:    'YourBundle:Emails:ticket.update.html.twig'
        update_txt:     'YourBundle:Emails:ticket.update.txt.twig'

```

You can override the base templates.