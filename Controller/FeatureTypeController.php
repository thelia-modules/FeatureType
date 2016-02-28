<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Controller;

use FeatureType\Model\FeatureTypeI18n;
use FeatureType\Model\FeatureTypeQuery;
use FeatureType\Event\FeatureTypeEvent;
use FeatureType\Event\FeatureTypeEvents;
use FeatureType\Model\FeatureType;
use FeatureType\FeatureType as FeatureTypeCore;
use Symfony\Component\Form\Form;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Model\LangQuery;
use Thelia\Core\HttpFoundation\Response;

/**
 * Class FeatureTypeController
 * @package FeatureType\Controller
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeController extends BaseAdminController
{
    protected $objectName = 'Feature type';

    /**
     * @param array $params
     * @return Response
     */
    public function viewAllAction($params = array())
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::VIEW)) {
            return $response;
        }

        return $this->render("feature-type/configuration", $params);
    }

    /**
     * @param int $id
     * @return Response
     * @throws \Exception
     */
    public function viewAction($id)
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::VIEW)) {
            return $response;
        }

        if (null === $featureType = FeatureTypeQuery::create()->findPk($id)) {
            throw new \Exception(Translator::getInstance()->trans(
                "Feature type not found",
                array(),
                FeatureTypeCore::MODULE_DOMAIN
            ));
        }

        $title = array();
        $description = array();

        /** @var FeatureTypeI18n $i18n */
        foreach ($featureType->getFeatureTypeI18ns() as $i18n) {
            if (null !== $lang = LangQuery::create()->findOneByLocale($i18n->getLocale())) {
                $title[$lang->getId()] = $i18n->getTitle();
                $description[$lang->getId()] = $i18n->getDescription();
            }
        }

        $form = $this->createForm('feature_type.update', 'form', array(
            'id' => $featureType->getId(),
            'slug' => $featureType->getSlug(),
            'pattern' => $featureType->getPattern(),
            'css_class' => $featureType->getCssClass(),
            'has_feature_av_value' => $featureType->getHasFeatureAvValue(),
            'is_multilingual_feature_av_value' => $featureType->getIsMultilingualFeatureAvValue(),
            'input_type' => $featureType->getInputType(),
            'min' => $featureType->getMin(),
            'max' => $featureType->getMax(),
            'step' => $featureType->getStep(),
            'title' => $title,
            'description' => $description
        ));

        $this->getParserContext()->addForm($form);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->render("feature-type/include/form-update");
        } else {
            return $this->viewAllAction(array(
                'feature_type_id' => $id
            ));
        }
    }

    /**
     * @return Response
     */
    public function createAction()
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm('feature_type.create');

        try {
            $this->dispatch(
                FeatureTypeEvents::FEATURE_TYPE_CREATE,
                new FeatureTypeEvent($this->hydrateFeatureTypeByForm(
                    $this->validateForm($form, 'POST')
                ))
            );

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)),
                $e->getMessage(),
                $form
            );

            return $this->viewAllAction();
        }
    }

    /**
     * @param int $id
     * @return Response
     */
    public function updateAction($id)
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm('feature_type.update');

        try {
            $this->dispatch(
                FeatureTypeEvents::FEATURE_TYPE_UPDATE,
                new FeatureTypeEvent(
                    $this->hydrateFeatureTypeByForm(
                        $this->validateForm($form, 'POST'),
                        $id
                    )
                )
            );

            return $this->generateSuccessRedirect($form);

        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)),
                $e->getMessage(),
                $form
            );

            return $this->viewAllAction(array(
                'feature_type_id' => $id
            ));
        }
    }

    /**
     * @param int $id
     * @return Response
     */
    public function deleteAction($id)
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::DELETE)) {
            return $response;
        }

        $form = $this->createForm('feature_type.delete');

        try {
            $this->validateForm($form, 'POST');

            if (null === $featureType = FeatureTypeQuery::create()->findPk($id)) {
                throw new \Exception(Translator::getInstance()->trans(
                    "Feature type not found",
                    array(),
                    FeatureTypeCore::MODULE_DOMAIN
                ));
            }

            $this->dispatch(
                FeatureTypeEvents::FEATURE_TYPE_DELETE,
                new FeatureTypeEvent($featureType)
            );

            return $this->generateSuccessRedirect($form);

        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)),
                $e->getMessage(),
                $form
            );

            return $this->viewAllAction();
        }
    }

    /**
     * @param int $id
     * @return Response
     * @throws \Exception
     */
    public function copyAction($id)
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::CREATE)) {
            return $response;
        }

        if (null === $featureType = FeatureTypeQuery::create()->findPk($id)) {
            throw new \Exception(Translator::getInstance()->trans(
                "Feature type not found",
                array(),
                FeatureTypeCore::MODULE_DOMAIN
            ));
        }

        $title = array();
        $description = array();

        /** @var FeatureTypeI18n $i18n */
        foreach ($featureType->getFeatureTypeI18ns() as $i18n) {
            if (null !== $lang = LangQuery::create()->findOneByLocale($i18n->getLocale())) {
                $title[$lang->getId()] = $i18n->getTitle();
                $description[$lang->getId()] = $i18n->getDescription();
            }
        }

        $form = $this->createForm('feature_type.create', 'form', array(
            'slug' => $featureType->getSlug() . '_' . Translator::getInstance()->trans(
                'copy',
                array(),
                FeatureTypeCore::MODULE_DOMAIN
            ),
            'pattern' => $featureType->getPattern(),
            'css_class' => $featureType->getCssClass(),
            'has_feature_av_value' => $featureType->getHasFeatureAvValue(),
            'is_multilingual_feature_av_value' => $featureType->getIsMultilingualFeatureAvValue(),
            'input_type' => $featureType->getInputType(),
            'min' => $featureType->getMin(),
            'max' => $featureType->getMax(),
            'step' => $featureType->getStep(),
            'title' => $title,
            'description' => $description
        ));

        $this->getParserContext()->addForm($form);

        return $this->render("feature-type/include/form-create");
    }

    /**
     * @param Form $form
     * @param int|null $id
     * @return FeatureType
     * @throws \Exception
     */
    protected function hydrateFeatureTypeByForm($form, $id = null)
    {
        $data = $form->getData();

        if ($id !== null) {
            if (null === $featureType = FeatureTypeQuery::create()->findPk($id)) {
                throw new \Exception(Translator::getInstance()->trans(
                    "Feature type not found",
                    array(),
                    FeatureTypeCore::MODULE_DOMAIN
                ));
            }
        } else {
            $featureType = new FeatureType();
        }

        $featureType
            ->setSlug($data['slug'])
            ->setPattern($data['pattern'])
            ->setCssClass($data['css_class'])
            ->setHasFeatureAvValue(isset($data['has_feature_av_value']) && (int) $data['has_feature_av_value'] ? 1 : 0)
            ->setIsMultilingualFeatureAvValue(isset($data['is_multilingual_feature_av_value']) && (int) $data['is_multilingual_feature_av_value'] ? 1 : 0)
            ->setInputType($data['input_type'])
            ->setMin($data['min'])
            ->setMax($data['max'])
            ->setStep($data['step']);

        foreach ($data['title'] as $langId => $title) {
            $featureType
                ->setLocale(LangQuery::create()->findPk($langId)->getLocale())
                ->setTitle($title)
                ->setDescription($data['description'][$langId]);
        }

        return $featureType;
    }

    /**
     * @param int $id
     * @return Response
     */
    protected function viewFeature($id)
    {
        return $this->render("feature-edit", array(
            'feature_id' => $id
        ));
    }
}
