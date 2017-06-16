<?php

namespace Hackzilla\Bundle\TicketBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder->root('hackzilla_ticket')
            ->children()
                ->booleanNode('show_tickets_to_all_admin')->defaultFalse()->end()
                ->booleanNode('ticket_per_page')->defaultValue(10)->end()
                ->scalarNode('user_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('ticket_class')->cannotBeEmpty()->defaultValue('Hackzilla\Bundle\TicketBundle\Entity\Ticket')->end()
                ->scalarNode('message_class')->cannotBeEmpty()->defaultValue('Hackzilla\Bundle\TicketBundle\Entity\TicketMessage')->end()
                    ->arrayNode('features')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('attachment')->defaultTrue()->end()
                        ->booleanNode('from_mail')->defaultFalse()->end()
                        ->booleanNode('notification')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('index')->defaultValue('HackzillaTicketBundle:Ticket:index.html.twig')->end()
                        ->scalarNode('new')->defaultValue('HackzillaTicketBundle:Ticket:new.html.twig')->end()
                        ->scalarNode('prototype')->defaultValue('HackzillaTicketBundle:Ticket:prototype.html.twig')->end()
                        ->scalarNode('show')->defaultValue('HackzillaTicketBundle:Ticket:show.html.twig')->end()
                        ->scalarNode('show_attachment')->defaultValue('HackzillaTicketBundle:Ticket:show_attachment.html.twig')->end()
                        ->scalarNode('macros')->defaultValue('HackzillaTicketBundle:Macros:macros.html.twig')->end()
                    ->end()
                ->end()
                ->arrayNode('from_mail')
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('imap_login')->end()
                        ->scalarNode('imap_pwd')->end()
                        ->scalarNode('imap_server_address')->end()
                        ->scalarNode('imap_server_port')->end()
                        ->scalarNode('imap_validate_crt')->end()
                        ->booleanNode('check_email_from_controller')->defaultFalse()->end()
                        ->arrayNode('templates')
                            ->addDefaultsIfNotSet()
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('new_html')->defaultValue('HackzillaTicketBundle:Ticket:ticket.new.from.mail.html.twig')->end()
                                ->scalarNode('new_txt')->defaultValue('HackzillaTicketBundle:Ticket:ticket.new.from.mail.txt.twig')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('notification')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('emails')
                            ->children()
                                ->scalarNode('sender_email')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('sender_name')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                        ->arrayNode('templates')
                            ->children()
                                ->scalarNode('new_html')->defaultValue('HackzillaTicketBundle:Emails:ticket.new.html.twig')->end()
                                ->scalarNode('new_txt')->defaultValue('HackzillaTicketBundle:Emails:ticket.new.txt.twig')->end()
                                ->scalarNode('update_html')->defaultValue('HackzillaTicketBundle:Emails:ticket.update.html.twig')->end()
                                ->scalarNode('update_txt')->defaultValue('HackzillaTicketBundle:Emails:ticket.update.txt.twig')->end()
                            ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
