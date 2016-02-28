<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
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
use Thelia\Core\Template\Loop\FeatureValue;
use Thelia\Model\FeatureProduct;
use Thelia\Model\Map\FeatureAvTableMap;

/**
 * Class FeatureValueExtendLoop
 * @package FeatureType\Loop
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureValueExtendLoop extends FeatureValue implements PropelSearchLoopInterface
{
    /**
     * @param LoopResult $loopResult
     * @return array|mixed|\Propel\Runtime\Collection\ObjectCollection
     */
    protected function getFeaturesMeta(LoopResult $loopResult)
    {
        $featureAvIds = array();

        /** @var FeatureProduct $featureValue */
        foreach ($loopResult->getResultDataCollection() as $featureValue) {
            $featureAvIds[] = $featureValue->getFeatureAvId();
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
            ->filterByLocale($this->locale)
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
    protected function formatSlug($slug)
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

        /** @var FeatureProduct $featureValue */
        foreach ($loopResult->getResultDataCollection() as $featureValue) {
            $loopResultRow = new LoopResultRow($featureValue);

            $loopResultRow
                ->set("ID", $featureValue->getId())
                ->set("PRODUCT", $featureValue->getProductId())
                ->set("FEATURE_AV_ID", $featureValue->getFeatureAvId())
                ->set("FREE_TEXT_VALUE", $featureValue->getFreeTextValue())
                ->set("IS_FREE_TEXT", is_null($featureValue->getFeatureAvId()) ? 1 : 0)
                ->set("IS_FEATURE_AV", is_null($featureValue->getFeatureAvId()) ? 0 : 1)
                ->set("LOCALE", $this->locale)
                ->set("TITLE", $featureValue->getVirtualColumn(FeatureAvTableMap::TABLE_NAME . '_i18n_TITLE'))
                ->set("CHAPO", $featureValue->getVirtualColumn(FeatureAvTableMap::TABLE_NAME . '_i18n_CHAPO'))
                ->set("DESCRIPTION", $featureValue->getVirtualColumn(FeatureAvTableMap::TABLE_NAME . '_i18n_DESCRIPTION'))
                ->set("POSTSCRIPTUM", $featureValue->getVirtualColumn(FeatureAvTableMap::TABLE_NAME . '_i18n_POSTSCRIPTUM'))
                ->set("POSITION", $featureValue->getPosition())
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
                if ($featureMeta->getFeatureAvId() === $featureValue->getFeatureAvId()) {
                    $loopResultRow->set(
                        self::formatSlug(
                            $featureMeta->getVirtualColumn('SLUG')
                        ),
                        $featureMeta->getValue()
                    );
                }
            }

            $this->addOutputFields($loopResultRow, $featureValue);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
