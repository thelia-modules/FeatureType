<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

/**
 * Class ConfigurationHook
 * @package FeatureType\Hook
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class ConfigurationHook extends BaseHook
{
    public static function getSubscribedHooks(): array
    {
        return [
            'configuration.catalog-top' => [
                ['type' => 'back', 'method' => 'onConfigurationCatalogTop'],
            ],
        ];
    }

    public function onConfigurationCatalogTop(HookRenderEvent $event): void
    {
        $event->add($this->render('FeatureType/hook/configuration-catalog.html.twig'));
    }
}
