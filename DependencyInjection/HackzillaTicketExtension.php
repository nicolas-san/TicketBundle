<?php

namespace Hackzilla\Bundle\TicketBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 */
class HackzillaTicketExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(self::bundleDirectory().'/Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('hackzilla_ticket.model.user.class', $config['user_class']);
        $container->setParameter('hackzilla_ticket.model.ticket.class', $config['ticket_class']);
        $container->setParameter('hackzilla_ticket.model.message.class', $config['message_class']);
        $container->setParameter('hackzilla_ticket.model.show.ticket.to.all.admin', $config['show_tickets_to_all_admin']);
        $container->setParameter('hackzilla_ticket.model.allow.delete.ticket.from.list', $config['allow_delete_ticket_from_list']);
        $container->setParameter('hackzilla_ticket.model.allow.reopenning.ticket', $config['allow_reopennig_ticket']);
        $container->setParameter('hackzilla_ticket.model.ticket_per_page', $config['ticket_per_page']);

        $container->setParameter('hackzilla_ticket.features', $config['features']);
        $container->setParameter('hackzilla_ticket.templates', $config['templates']);

        //imported from the flodaq work https://github.com/flodaq/TicketNotificationBundle/
        $container->setParameter('hackzilla_ticket.notification.emails', $config['notification']['emails']);
        $container->setParameter('hackzilla_ticket.notification.templates', $config['notification']['templates']);

        //added for from_mail feature
        $container->setParameter('hackzilla_ticket.from_mail', $config['from_mail']);
    }

    public static function bundleDirectory()
    {
        return realpath(__DIR__.'/..');
    }
}
