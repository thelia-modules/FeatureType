<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Event;

/**
 * Class FeatureTypeEvents
 * @package FeatureType\Event
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class FeatureTypeEvents
{
    const FEATURE_TYPE_CREATE = "feature.type.create";
    const FEATURE_TYPE_UPDATE = "feature.type.update";
    const FEATURE_TYPE_DELETE = "feature.type.delete";
    const FEATURE_TYPE_ASSOCIATE = 'feature.type.associate';
    const FEATURE_TYPE_DISSOCIATE = 'feature.type.dissociate';
    const FEATURE_TYPE_AV_META_UPDATE = 'feature.type.av.meta.update';
    const FEATURE_TYPE_AV_META_CREATE = 'feature.type.av.meta.create';
}
