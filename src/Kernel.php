<?php

declare(strict_types=1);

namespace App;

use App\DBAL\Types\AbstractEnumType;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function process(ContainerBuilder $container): void
    {
        $typesDefinition = [];
        if ($container->hasParameter('doctrine.dbal.connection_factory.types')) {
            /** @var array $typesDefinition */
            $typesDefinition = $container->getParameter('doctrine.dbal.connection_factory.types');
        }

        $taggedEnums = $container->findTaggedServiceIds('app.doctrine_enum_type');

        foreach ($taggedEnums as $enumType => $definition) {
            /** @var AbstractEnumType $enumType */
            $typesDefinition[$enumType::getEnumClass()] = ['class' => $enumType];
        }

        $container->setParameter('doctrine.dbal.connection_factory.types', $typesDefinition);
    }
}
