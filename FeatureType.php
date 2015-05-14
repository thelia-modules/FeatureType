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
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Module\BaseModule;
use Thelia\Install\Database;

/**
 * Class FeatureType
 * @package FeatureType
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class FeatureType extends BaseModule
{
    const MODULE_DOMAIN = "featuretype";

    const RESERVED_SLUG = 'id,feature_id,id_translater,locale,title,chapo,description,postscriptum,position';

    /**
     * @param ConnectionInterface $con
     */
    public function postActivation(ConnectionInterface $con = null)
    {
        if (!self::getConfigValue('is_initialized', false)) {
            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/thelia.sql", __DIR__ . "/Config/insert.sql"]);
            self::setConfigValue('is_initialized', true);
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
}
