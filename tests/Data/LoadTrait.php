<?php

namespace SfCod\QueueBundleTests\Data;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use SfCod\QueueBundle\Base\MongoDriverInterface;
use SfCod\QueueBundle\DependencyInjection\QueueExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;

/**
 * Trait LoadTrait
 *
 * @package SfCod\QueueBundle\Tests\Data
 */
trait LoadTrait
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Configure container
     *
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $dotenv = new Dotenv(true);
        try {
            $dotenv->load(__DIR__ . '/../../.env');
        } catch (PathException $e) {
            // Nothing
        }

        $extension = new QueueExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', getenv('KERNEL_PROJECT_DIR'));
        $container->setParameter('kernel.root_dir', realpath(__DIR__ . '/../../../../SfCod/'));
        $container->set(LoggerInterface::class, new Logger('test'));

        $extension->load([
            0 => [
                'namespaces' => [
                    'SfCod\QueueBundle\Tests\Data',
                ],
            ],
            1 => [
                'connections' => [
                    'default' => [
                        'driver' => 'mongo-thread',
                        'collection' => 'queue_jobs',
                        'connection' => MongoDriverInterface::class,
                        'queue' => 'default',
                        'expire' => 60,
                        'limit' => 2,
                    ],
                ],
            ],
        ], $container);

        $this->container = $container;
    }
}
