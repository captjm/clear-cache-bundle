<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CaptJM\ClearCacheBundle\Command\MakeCacheClearControllerCommand;
use EasyCorp\Bundle\EasyAdminBundle\Maker\ClassMaker;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()->private()
        ->set(MakeCacheClearControllerCommand::class)->public()
            ->arg(0, service(ClassMaker::class))
            ->arg(1, param('kernel.project_dir'))
            ->tag('console.command')
    ;
};
