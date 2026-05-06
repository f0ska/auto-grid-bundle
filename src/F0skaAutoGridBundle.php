<?php
/*
 * This file is part of the F0ska/AutoGrid package.
 *
 * (c) Victor Shvets
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace F0ska\AutoGridBundle;

use F0ska\AutoGridBundle\Action\ActionInterface;
use F0ska\AutoGridBundle\ActionParameter\ActionParameterInterface;
use F0ska\AutoGridBundle\Condition\FilterConditionInterface;
use F0ska\AutoGridBundle\Customization\CustomizationInterface;
use F0ska\AutoGridBundle\DependencyInjection\F0skaAutoGridExtension;
use F0ska\AutoGridBundle\RowActionPermission\RowActionPermissionInterface;
use F0ska\AutoGridBundle\View\ViewServiceInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class F0skaAutoGridBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(ActionInterface::class)
            ->addTag('autogrid.action')
        ;

        $container
            ->registerForAutoconfiguration(ActionParameterInterface::class)
            ->addTag('autogrid.action.parameter')
        ;

        $container
            ->registerForAutoconfiguration(FilterConditionInterface::class)
            ->addTag('autogrid.filter_condition')
        ;

        $container
            ->registerForAutoconfiguration(ViewServiceInterface::class)
            ->addTag('autogrid.view_service')
        ;

        $container
            ->registerForAutoconfiguration(CustomizationInterface::class)
            ->addTag('autogrid.customization')
        ;

        $container
            ->registerForAutoconfiguration(RowActionPermissionInterface::class)
            ->addTag('autogrid.row_action_permission')
        ;
    }

    public function getContainerExtension(): F0skaAutoGridExtension
    {
        return new F0skaAutoGridExtension();
    }
}
