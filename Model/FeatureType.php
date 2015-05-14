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

namespace FeatureType\Model;

use FeatureType\Model\Base\FeatureType as BaseFeatureType;
use FeatureType\Model\Map\FeatureFeatureTypeTableMap;
use FeatureType\Model\Map\FeatureTypeAvMetaTableMap;
use FeatureType\Model\Map\FeatureTypeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\Model\Map\FeatureAvTableMap;

/**
 * Class FeatureType
 * @package FeatureType\Model
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class FeatureType extends BaseFeatureType
{
    /**
     * Returns a value based on the slug, feature_av_id and locale
     *
     * <code>
     * $value  = FeatureType::getValue('color', 2);
     * </code>
     *
     * @param string $slug
     * @param int $featureId
     * @param string $locale
     * @return string
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public static function getValue($slug, $featureId, $locale = 'en_US')
    {
        return self::getValues([$slug], [$featureId], $locale)[$slug][$featureId];
    }

    /**
     * Returns a set of values
     * If the value does not exist, it is replaced by null
     *
     * <code>
     * $values = FeatureType::getValue(['color','texture'], [4,7]);
     * </code>
     *
     * <sample>
     *  array(
     *  'color' => [4 => '#00000', 7 => '#FFF000'],
     *  'texture' => [4 => null, 7 => 'lines.jpg']
     * )
     * </sample>
     *
     * @param array $slugs[]
     * @param array $featureIds[]
     * @param string $locale
     * @return string
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public static function getValues(array $slugs, array $featureIds, $locale = 'en_US')
    {
        $return = array();

        foreach ($slugs as $slug) {
            $return[$slug] = array();
            foreach ($featureIds as $featureId) {
                $return[$slug][$featureId] = null;
            }
        }

        $query = FeatureTypeAvMetaQuery::create()
            ->filterByLocale($locale)
            ->filterByFeatureAvId($featureIds, Criteria::IN);

        self::addJoinFeatureFeatureType($query);
        self::addJoinFeatureType($query);
        self::addJoinFeatureAv($query);

        $in = implode(
            ',',
            array_map(
                function($v) {
                    return "'" . $v . "'";
                },
                $slugs
            )
        );

        $query
            ->addJoinCondition('feature_type', "`feature_type`.`SLUG` IN (" . $in . ")")
            ->addJoinCondition('feature_av', "`feature_av`.`ID` = `feature_type_av_meta`.`FEATURE_AV_ID`")
            ->withColumn('`feature_type`.`SLUG`', 'SLUG')
            ->withColumn('`feature_av`.`ID`', 'FEATURE_AV_ID');

        $results = $query->find();

        foreach ($results as $result) {
            $return[$result->getVirtualColumn('SLUG')][$result->getVirtualColumn('FEATURE_AV_ID')]
                = $result->getValue();
        }

        return $return;
    }

    /**
     * @param Criteria $query
     */
    protected static function addJoinFeatureFeatureType(Criteria & $query)
    {
        $join = new Join();

        $join->addExplicitCondition(
            FeatureTypeAvMetaTableMap::TABLE_NAME,
            'FEATURE_FEATURE_TYPE_ID',
            null,
            FeatureFeatureTypeTableMap::TABLE_NAME,
            'ID',
            null
        );

        $join->setJoinType(Criteria::INNER_JOIN);

        $query->addJoinObject($join, 'feature_type_av_meta');
    }

    /**
     * @param Criteria $query
     */
    protected static function addJoinFeatureType(Criteria & $query)
    {
        $join = new Join();

        $join->addExplicitCondition(
            FeatureFeatureTypeTableMap::TABLE_NAME,
            'FEATURE_TYPE_ID',
            null,
            FeatureTypeTableMap::TABLE_NAME,
            'ID',
            null
        );

        $join->setJoinType(Criteria::INNER_JOIN);

        $query->addJoinObject($join, 'feature_type');
    }

    /**
     * @param Criteria $query
     * @return $this
     */
    protected static function addJoinFeatureAv(Criteria & $query)
    {
        $join = new Join();

        $join->addExplicitCondition(
            FeatureFeatureTypeTableMap::TABLE_NAME,
            'FEATURE_ID',
            null,
            FeatureAvTableMap::TABLE_NAME,
            'FEATURE_ID',
            null
        );

        $join->setJoinType(Criteria::INNER_JOIN);

        $query->addJoinObject($join, 'feature_av');
    }
}
