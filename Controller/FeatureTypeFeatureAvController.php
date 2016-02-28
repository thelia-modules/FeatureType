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

namespace FeatureType\Controller;

use FeatureType\Event\FeatureTypeEvents;
use FeatureType\Event\FeatureTypeAvMetaEvent;
use FeatureType\Model\FeatureFeatureType;
use FeatureType\Model\FeatureFeatureTypeQuery;
use FeatureType\Model\FeatureTypeAvMeta;
use FeatureType\Model\FeatureTypeAvMetaQuery;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;

/**
 * Class FeatureTypeFeatureAvController
 * @package FeatureType\Controller
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeFeatureAvController extends FeatureTypeController
{
    /** @var Lang[] */
    protected $langs = array();

    /** @var FeatureFeatureType[] */
    protected $featureFeatureTypes = array();

    /**
     * @param int $feature_id
     * @return null|\Symfony\Component\HttpFoundation\Response|\Thelia\Core\HttpFoundation\Response
     */
    public function updateMetaAction($feature_id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::FEATURE), null, AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm("feature_type_av_meta.update");

        try {
            $formUpdate = $this->validateForm($form);

            $featureAvs = $formUpdate->get('feature_av')->getData();

            foreach ($featureAvs as $featureAvId => $featureAv) {
                foreach ($featureAv['lang'] as $langId => $lang) {
                    foreach ($lang['feature_type'] as $featureTypeId => $value) {
                        $this->dispatchEvent(
                            $this->getFeatureFeatureType($featureTypeId, $feature_id),
                            $featureAvId,
                            $langId,
                            $value
                        );
                    }
                }
            }

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)),
                $e->getMessage(),
                $form
            );

            return $this->viewFeature($feature_id);
        }
    }

    /**
     * @param FeatureFeatureType $featureFeatureType
     * @param int $featureAvId
     * @param int $langId
     * @param string $value
     * @throws \Exception
     */
    protected function dispatchEvent(FeatureFeatureType $featureFeatureType, $featureAvId, $langId, $value)
    {
        $eventName = FeatureTypeEvents::FEATURE_TYPE_AV_META_UPDATE;

        $featureAvMeta = FeatureTypeAvMetaQuery::create()
            ->filterByFeatureAvId($featureAvId)
            ->filterByFeatureFeatureTypeId($featureFeatureType->getId())
            ->filterByLocale($this->getLocale($langId))
            ->findOne();

        // create if not exist
        if ($featureAvMeta === null) {
            $eventName = FeatureTypeEvents::FEATURE_TYPE_AV_META_CREATE;

            $featureAvMeta = (new FeatureTypeAvMeta())
                ->setFeatureAvId($featureAvId)
                ->setFeatureFeatureTypeId($featureFeatureType->getId())
                ->setLocale($this->getLocale($langId));
        }

        $featureAvMeta->setValue($value);

        $this->dispatch(
            $eventName,
            (new FeatureTypeAvMetaEvent($featureAvMeta))
        );
    }

    /**
     * @param int $featureTypeId
     * @param int $featureId
     * @return FeatureFeatureType
     * @throws \Exception
     */
    protected function getFeatureFeatureType($featureTypeId, $featureId)
    {
        if (!isset($this->featureFeatureTypes[$featureTypeId])) {
            $this->featureFeatureTypes[$featureTypeId] = FeatureFeatureTypeQuery::create()
                ->filterByFeatureTypeId($featureTypeId)
                ->filterByFeatureId($featureId)
                ->findOne();

            if ($this->featureFeatureTypes[$featureTypeId] === null) {
                throw new \Exception('FeatureFeatureType not found');
            }
        }

        return $this->featureFeatureTypes[$featureTypeId];
    }

    /**
     * @param int $langId
     * @return string
     * @throws \Exception
     */
    protected function getLocale($langId)
    {
        if (!isset($this->langs[$langId])) {
            $this->langs[$langId] = LangQuery::create()->findPk($langId);

            if ($this->langs[$langId] === null) {
                throw new \Exception('Lang not found');
            }
        }

        return $this->langs[$langId]->getLocale();
    }
}
