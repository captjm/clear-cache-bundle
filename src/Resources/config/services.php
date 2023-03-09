<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CaptJM\ClearCacheBundle\Command\MakeCacheClearControllerCommand;
use CaptJM\ClearCacheBundle\Maker\ClassMaker;
use Symfony\Component\HttpKernel\KernelInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()->private()

        ->set(ClassMaker::class)
        ->arg(0, service(KernelInterface::class))
        ->arg(1, param('kernel.project_dir'))

        ->set(MakeCacheClearControllerCommand::class)->public()
        ->arg(0, service(ClassMaker::class))
        ->arg(1, param('kernel.project_dir'))
        ->tag('console.command');
};
