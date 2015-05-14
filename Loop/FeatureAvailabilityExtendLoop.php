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

namespace FeatureType\Loop;

use FeatureType\Model\FeatureTypeAvMeta;
use FeatureType\Model\FeatureTypeAvMetaQuery;
use FeatureType\Model\Map\FeatureFeatureTypeTableMap;
use FeatureType\Model\Map\FeatureTypeAvMetaTableMap;
use FeatureType\Model\Map\FeatureTypeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\FeatureAvailability;
use Thelia\Model\FeatureAv;

/**
 * Class FeatureAvailabilityExtendLoop
 * @package FeatureType\Loop
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class FeatureAvailabilityExtendLoop extends FeatureAvailability implements PropelSearchLoopInterface
{
    /**
     * @param LoopResult $loopResult
     * @return array|mixed|\Propel\Runtime\Collection\ObjectCollection
     */
    private function getFeaturesMeta(LoopResult $loopResult)
    {
        $featureAvIds = array();
        $locale = null;

        /** @var FeatureAV $featureAv */
        foreach ($loopResult->getResultDataCollection() as $featureAv) {
            $featureAvIds[] = $featureAv->getId();
            if ($locale === null) {
                $locale = $featureAv->getLocale();
            }
        }

        $joinFeatureFeatureType = new Join();

        $joinFeatureFeatureType->addExplicitCondition(
            FeatureTypeAvMetaTableMap::TABLE_NAME,
            'FEATURE_FEATURE_TYPE_ID',
            null,
            FeatureFeatureTypeTableMap::TABLE_NAME,
            'ID',
            null
        );

        $joinFeatureFeatureType->setJoinType(Criteria::INNER_JOIN);

        $joinFeatureType = new Join();

        $joinFeatureType->addExplicitCondition(
            FeatureFeatureTypeTableMap::TABLE_NAME,
            'FEATURE_TYPE_ID',
            null,
            FeatureTypeTableMap::TABLE_NAME,
            'ID',
            null
        );

        $joinFeatureType->setJoinType(Criteria::INNER_JOIN);

        $query = FeatureTypeAvMetaQuery::create()
            ->filterByLocale($locale)
            ->filterByFeatureAvId($featureAvIds, Criteria::IN)
            ->addJoinObject($joinFeatureFeatureType)
            ->addJoinObject($joinFeatureType);

        $query->withColumn('`feature_type`.`SLUG`', 'SLUG');

        return $query->find();
    }

    /**
     * @param string $slug
     * @return string
     */
    private function formatSlug($slug)
    {
        return strtoupper(str_replace('-', '_', $slug));
    }

    /**
     * @param LoopResult $loopResult
     * @return LoopResult
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function parseResults(LoopResult $loopResult)
    {
        $featuresMeta = self::getFeaturesMeta($loopResult);

        $slugs = array();

        /** @var FeatureTypeAvMeta $featureMeta */
        foreach ($featuresMeta as $featureMeta) {
            $slugs[$featureMeta->getVirtualColumn('SLUG')] = true;
        }

        /** @var FeatureAV $featureAv */
        foreach ($loopResult->getResultDataCollection() as $featureAv) {
            $loopResultRow = new LoopResultRow($featureAv);
            $loopResultRow
                ->set("ID", $featureAv->getId())
                ->set("FEATURE_ID", $featureAv->getFeatureId())
                ->set("IS_TRANSLATED", $featureAv->getVirtualColumn('IS_TRANSLATED'))
                ->set("LOCALE", $this->locale)
                ->set("TITLE", $featureAv->getVirtualColumn('i18n_TITLE'))
                ->set("CHAPO", $featureAv->getVirtualColumn('i18n_CHAPO'))
                ->set("DESCRIPTION", $featureAv->getVirtualColumn('i18n_DESCRIPTION'))
                ->set("POSTSCRIPTUM", $featureAv->getVirtualColumn('i18n_POSTSCRIPTUM'))
                ->set("POSITION", $featureAv->getPosition())
            ;

            // init slug variable
            foreach ($slugs as $slug => $bool) {
                $loopResultRow->set(
                    self::formatSlug(
                        $slug
                    ),
                    null
                );
            }

            /** @var FeatureTypeAvMeta $featureMeta */
            foreach ($featuresMeta as $featureMeta) {
                if ($featureMeta->getFeatureAvId() === $featureAv->getId()) {
                    $loopResultRow->set(
                        self::formatSlug(
                            $featureMeta->getVirtualColumn('SLUG')
                        ),
                        $featureMeta->getValue()
                    );
                }
            }

            $this->addOutputFields($loopResultRow, $featureAv);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
