<?php

namespace App\DependencyInjection\Compiler;

use App\EventListener\FlashListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FlashListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('fos_user.listener.flash');
        $definition->setClass(FlashListener::class);
    }
}
