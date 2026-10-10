<?php

declare(strict_types=1);

namespace Stejac\MiteBundle;

use Stejac\MiteBundle\Http\MiteClient;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class MiteBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $children = $definition->rootNode()->children();
        // Format checks for domain happen in MiteClient at runtime: env placeholders are not the real value here.
        $children->scalarNode('domain')->isRequired()->cannotBeEmpty()->info('Account sub-domain, e.g. "acme" for acme.mite.de.');
        $children->scalarNode('api_key')->isRequired()->cannotBeEmpty()->info('mite API key; use an env var.');
        $children->scalarNode('user_agent')->defaultValue('stejac/mite-bundle')->cannotBeEmpty()->info('Identifies your app to mite, ideally with a contact.');
        $children->scalarNode('http_client')->defaultNull()->info('Optional service id of a Symfony HttpClientInterface.');
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $httpClient = $config['http_client'] ?? null;
        \assert(null === $httpClient || \is_string($httpClient));

        $container->services()
            ->set(MiteClient::class)
            ->args([
                $config['domain'],
                $config['api_key'],
                $config['user_agent'],
                null === $httpClient ? service('http_client')->nullOnInvalid() : service($httpClient),
            ]);
    }
}
