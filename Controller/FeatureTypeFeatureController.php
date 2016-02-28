<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Controller;

use FeatureType\Model\FeatureTypeQuery;
use Thelia\Core\HttpFoundation\Response;
use FeatureType\Event\FeatureTypeEvents;
use FeatureType\Event\FeatureTypeEvent;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\FeatureQuery;
use Thelia\Core\Translation\Translator;
use FeatureType\FeatureType as FeatureTypeCore;

/**
 * Class FeatureTypeFeatureController
 * @package FeatureType\Controller
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeFeatureController extends FeatureTypeController
{
    /**
     * @param int $feature_type_id
     * @param int $feature_id
     * @return Response
     */
    public function associateAction($feature_type_id, $feature_id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::FEATURE), null, AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm('feature_type.associate');

        try {
            $this->validateForm($form, 'POST');

            $this->dispatch(
                FeatureTypeEvents::FEATURE_TYPE_ASSOCIATE,
                $this->getEventAssociation($feature_type_id, $feature_id)
            );

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
     * @param int $feature_type_id
     * @param int $feature_id
     * @return Response
     */
    public function dissociateAction($feature_type_id, $feature_id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::FEATURE), null, AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm('feature_type.dissociate');

        try {
            $this->validateForm($form, 'POST');

            $this->dispatch(
                FeatureTypeEvents::FEATURE_TYPE_DISSOCIATE,
                $this->getEventAssociation($feature_type_id, $feature_id)
            );

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
     * @param int $feature_type_id
     * @param int $feature_id
     * @return FeatureTypeEvent
     * @throws \Exception
     */
    protected function getEventAssociation($feature_type_id, $feature_id)
    {
        if (null === $feature = FeatureQuery::create()->findPk($feature_id)) {
            throw new \Exception(Translator::getInstance()->trans(
                "Feature not found",
                array(),
                FeatureTypeCore::MODULE_DOMAIN
            ));
        }

        if (null === $featureType = FeatureTypeQuery::create()->findPk($feature_type_id)) {
            throw new \Exception(Translator::getInstance()->trans(
                "Feature type not found",
                array(),
                FeatureTypeCore::MODULE_DOMAIN
            ));
        }

        $event = new FeatureTypeEvent($featureType);
        $event->setFeature($feature);

        return $event;
    }
}
