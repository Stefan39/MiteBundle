<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Kernel;

use Stejac\MiteBundle\MiteBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    private readonly string $cacheDir;

    /**
     * @param array<string, mixed>        $miteConfig
     * @param array<string, class-string> $extraServices service id => class without constructor arguments
     */
    public function __construct(private readonly array $miteConfig, private readonly array $extraServices = [])
    {
        parent::__construct('test', true);
        // A failed container build keeps its cache lock for the rest of the process, so every kernel gets its own directory.
        $this->cacheDir = sys_get_temp_dir().'/mite-bundle-tests/'.uniqid('', true);
    }

    public function registerBundles(): iterable
    {
        return [new FrameworkBundle(), new MiteBundle()];
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return $this->cacheDir.'/cache';
    }

    public function getLogDir(): string
    {
        return $this->cacheDir.'/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', ['secret' => 'test', 'test' => true, 'http_client' => ['enabled' => true]]);
        $container->extension('mite', $this->miteConfig);

        foreach ($this->extraServices as $id => $class) {
            $container->services()->set($id, $class)->public();
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
