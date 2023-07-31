<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DependencyInjection\CompilerPass;

use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WhiteDigital\SiteTree\Contracts\ContentTypeFinderInterface;
use WhiteDigital\SiteTree\Service\ContentTypeFinder;

final readonly class ContentTypeFinderCompilerPass implements CompilerPassInterface
{
    /**
     * @throws ReflectionException
     *
     * @noinspection PhpUndefinedMethodInspection
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ContentTypeFinderInterface::class)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(ContentTypeFinderInterface::class);

        $maxPriority = ContentTypeFinder::getDefaultPriority();
        $highestPriorityService = null;
        foreach ($taggedServices as $service => $ignored) {
            $priority = $container->getReflectionClass($service)->getName()::getDefaultPriority();

            if ($maxPriority < $priority) {
                $maxPriority = $priority;
                $highestPriorityService = $service;
            }
        }

        if (null !== $highestPriorityService) {
            $container->setDefinition(ContentTypeFinderInterface::class, $container->getDefinition($highestPriorityService));
        }
    }
}
