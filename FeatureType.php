<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Module\BaseModule;
use Thelia\Install\Database;

/**
 * Class FeatureType
 * @package FeatureType
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureType extends BaseModule
{
    const MODULE_DOMAIN = "featuretype";

    const RESERVED_SLUG = 'id,feature_id,id_translater,locale,title,chapo,description,postscriptum,position';

    const FEATURE_TYPE_AV_IMAGE_FOLDER = 'feature_type_av_images';

    /** @var string */
    const UPDATE_PATH = __DIR__ . DS . 'Config' . DS . 'update';

    /**
     * @param ConnectionInterface $con
     */
    public function postActivation(ConnectionInterface $con = null): void
    {
        if (!self::getConfigValue('is_initialized', false)) {
            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/thelia.sql", __DIR__ . "/Config/insert.sql"]);
            self::setConfigValue('is_initialized', true);
        }
    }

    /**
     * @param $currentVersion
     * @param $newVersion
     * @param ConnectionInterface|null $con
     */
    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        $finder = (new Finder())->files()->name('#.*?\.sql#')->sortByName()->in(self::UPDATE_PATH);

        if ($finder->count() === 0) {
            return;
        }

        $database = new Database($con);

        /** @var \Symfony\Component\Finder\SplFileInfo $updateSQLFile */
        foreach ($finder as $updateSQLFile) {
            if (version_compare($currentVersion, str_replace('.sql', '', $updateSQLFile->getFilename()), '<')) {
                $database->insertSql(null, [$updateSQLFile->getPathname()]);
            }
        }
    }

    /**
     * @return array
     */
    public function getHooks()
    {
        return array(
            array(
                "type" => TemplateDefinition::BACK_OFFICE,
                "code" => "feature-type.form-top",
                "title" => array(
                    "fr_FR" => "Module Feature Type, form haut",
                    "en_US" => "Module Feature Type, form top",
                ),
                "description" => array(
                    "fr_FR" => "En haut du formulaire de création et de mise à jour",
                    "en_US" => "Top of creation form and update",
                ),
                "active" => true
            ),
            array(
                "type" => TemplateDefinition::BACK_OFFICE,
                "code" => "feature-type.form-bottom",
                "title" => array(
                    "fr_FR" => "Module Feature Type, form bas",
                    "en_US" => "Module Feature Type, form bottom",
                ),
                "description" => array(
                    "fr_FR" => "En bas du formulaire de création et de mise à jour",
                    "en_US" => "Top of creation form and update",
                ),
                "active" => true
            ),
            array(
                "type" => TemplateDefinition::BACK_OFFICE,
                "code" => "feature-type.configuration-top",
                "title" => array(
                    "fr_FR" => "Module Feature Type, configuration haut",
                    "en_US" => "Module Feature Type, configuration top",
                ),
                "description" => array(
                    "fr_FR" => "En haut du la page de configuration du module",
                    "en_US" => "At the top of the module's configuration page",
                ),
                "active" => true
            ),
            array(
                "type" => TemplateDefinition::BACK_OFFICE,
                "code" => "feature-type.configuration-bottom",
                "title" => array(
                    "fr_FR" => "Module Feature Type, configuration bas",
                    "en_US" => "Module Feature Type, configuration bottom",
                ),
                "description" => array(
                    "fr_FR" => "En bas du la page de configuration du module",
                    "en_US" => "At the bottom of the module's configuration page",
                ),
                "active" => true
            ),
            array(
                "type" => TemplateDefinition::BACK_OFFICE,
                "code" => "feature-type.list-action",
                "title" => array(
                    "fr_FR" => "Module Feature Type, list action",
                    "en_US" => "Module Feature Type, list action",
                ),
                "description" => array(
                    "fr_FR" => "Action de la liste des types de caractéristiques",
                    "en_US" => "Action from the list of features types",
                ),
                "active" => true
            ),
            array(
                "type" => TemplateDefinition::BACK_OFFICE,
                "code" => "feature-type.configuration-js",
                "title" => array(
                    "fr_FR" => "Module Feature Type, configuration js",
                    "en_US" => "Module Feature Type, configuration js",
                ),
                "description" => array(
                    "fr_FR" => "JS la page de configuration du module",
                    "en_US" => "JS of the module's configuration page",
                ),
                "active" => true
            )
        );
    }

    /**
     * Defines how services are loaded in your modules
     *
     * @param ServicesConfigurator $servicesConfigurator
     */
    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
