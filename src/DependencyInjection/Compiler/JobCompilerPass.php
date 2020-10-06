<?php

namespace SfCod\QueueBundle\DependencyInjection\Compiler;

use SfCod\QueueBundle\Base\JobResolverInterface;
use SfCod\QueueBundle\Service\JobQueue;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class JobCompilerPass
 * @author Virchenko Maksim <muslim1992@gmail.com>
 * @package SfCod\QueueBundle\DependencyInjection\Compiler
 */
class JobCompilerPass implements CompilerPassInterface
{
    /**
     * Find all job handlers and mark them as public in case to work properly with job queue
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(JobResolverInterface::class)) {
            return;
        }

        $jobResolver = $container->getDefinition(JobResolverInterface::class);
        $taggedServices = $container->findTaggedServiceIds('sfcod.jobqueue.job');

        foreach ($taggedServices as $id => $tags) {
            $jobResolver->addMethodCall('addJob', [$id, new Reference($id)]);
        }
    }
}
