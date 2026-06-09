<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Controller;

use FeatureType\Form\FeatureTypeCreateForm;
use FeatureType\Form\FeatureTypeForm;
use FeatureType\Form\FeatureTypeUpdateForm;
use FeatureType\Model\FeatureTypeI18n;
use FeatureType\Model\FeatureTypeQuery;
use FeatureType\Event\FeatureTypeEvent;
use FeatureType\Event\FeatureTypeEvents;
use FeatureType\Model\FeatureType;
use FeatureType\FeatureType as FeatureTypeCore;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Form\Form;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Translation\Translator;
use Thelia\Model\FeatureAvI18n;
use Thelia\Model\FeatureAvI18nQuery;
use Thelia\Model\FeatureAvQuery;
use Thelia\Model\LangQuery;
use Thelia\Tools\URL;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class FeatureTypeController
 * @package FeatureType\Controller
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeController extends BaseAdminController
{
    protected $objectName = 'Feature type';

    protected ?Environment $twig = null;

    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    protected function renderTwig(string $template, array $context = []): Response
    {
        return new Response(
            $this->twig->render(
                '@FeatureTypeModule/backOffice/default-twig/' . $template . '.html.twig',
                $context
            )
        );
    }

    /**
     * Build the feature-type list (with the features each one is associated to)
     * displayed on the module configuration page.
     */
    protected function buildFeatureTypeList(): array
    {
        $locale = $this->getRequest()->getLocale();

        $featureTypes = FeatureTypeQuery::create()
            ->orderById()
            ->find();

        $list = [];
        foreach ($featureTypes as $featureType) {
            $featureType->setLocale($locale);

            $features = [];
            $featureFeatureTypes = \FeatureType\Model\FeatureFeatureTypeQuery::create()
                ->filterByFeatureTypeId($featureType->getId())
                ->find();

            foreach ($featureFeatureTypes as $featureFeatureType) {
                $feature = \Thelia\Model\FeatureQuery::create()
                    ->findPk($featureFeatureType->getFeatureId());

                if (null !== $feature) {
                    $feature->setLocale($locale);
                    $features[] = [
                        'id' => $feature->getId(),
                        'title' => $feature->getTitle(),
                    ];
                }
            }

            $list[] = [
                'id' => $featureType->getId(),
                'slug' => $featureType->getSlug(),
                'title' => $featureType->getTitle(),
                'description' => $featureType->getDescription(),
                'features' => $features,
            ];
        }

        return $list;
    }

    /**
     * @param array $params
     * @return Response
     */
    public function viewAllAction(Environment $twig, $params = array()): Response
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::VIEW)) {
            return $response;
        }

        $this->setTwig($twig);

        $createForm = $this->createForm(FeatureTypeCreateForm::getName(), data: ['has_feature_av_value' => 0]);

        return $this->renderTwig('feature-type/configuration', array_merge([
            'feature_types' => $this->buildFeatureTypeList(),
            'edit_language_id' => $this->resolveEditLanguageId(),
            'feature_type_id' => null,
            'create_form' => $createForm->createView()->getView(),
            'langs' => $this->buildLangList(),
        ], $params));
    }

    protected function buildLangList(): array
    {
        $langs = [];
        foreach (LangQuery::create()->filterByActive(1)->find() as $lang) {
            $langs[] = [
                'id' => $lang->getId(),
                'locale' => $lang->getLocale(),
                'code' => $lang->getCode(),
                'title' => $lang->getTitle(),
            ];
        }

        return $langs;
    }

    protected function resolveEditLanguageId(): ?int
    {
        $request = $this->getRequest();
        if (!$request->hasSession()) {
            return null;
        }

        $lang = $request->getSession()->get('thelia.admin.edition.lang');

        return $lang instanceof \Thelia\Model\Lang ? $lang->getId() : null;
    }

    /**
     * @param int $id
     * @return Response
     * @throws \Exception
     */
    public function viewAction(Environment $twig, $id): Response
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::VIEW)) {
            return $response;
        }

        $this->setTwig($twig);

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


        $form = $this->createForm(FeatureTypeUpdateForm::getName(), data: array(
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
            'image_max_width' => $featureType->getImageMaxWidth(),
            'image_max_height' => $featureType->getImageMaxHeight(),
            'image_ratio' => $featureType->getImageRatio(),
            'title' => $title,
            'description' => $description
        ));

        $this->getParserContext()->addForm($form);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->renderTwig('feature-type/include/form-update', [
                'form' => $form->createView()->getView(),
                'feature_type_id' => $featureType->getId(),
                'edit_language_id' => $this->resolveEditLanguageId(),
                'langs' => $this->buildLangList(),
            ]);
        }

        return $this->viewAllAction($twig, array(
            'feature_type_id' => $id,
            'update_form' => $form->createView()->getView(),
        ));
    }

    /**
     * @return Response
     */
    public function createAction(EventDispatcherInterface $eventDispatcher, Environment $twig): Response
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm(FeatureTypeCreateForm::getName());

        try {
            $eventDispatcher->dispatch(
                new FeatureTypeEvent($this->hydrateFeatureTypeByForm(
                    $this->validateForm($form, 'POST')
                )),
                FeatureTypeEvents::FEATURE_TYPE_CREATE
            );

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)),
                $e->getMessage(),
                $form
            );

            return $this->viewAllAction($twig);
        }
    }

    /**
     * @param int $id
     * @return Response
     */
    public function updateAction(EventDispatcherInterface $eventDispatcher, Environment $twig, $id): Response
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(FeatureTypeUpdateForm::getName());

        try {
            $eventDispatcher->dispatch(
                new FeatureTypeEvent(
                    $this->hydrateFeatureTypeByForm(
                        $this->validateForm($form, 'POST'),
                        $id
                    )
                ),
                FeatureTypeEvents::FEATURE_TYPE_UPDATE
            );

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)),
                $e->getMessage(),
                $form
            );

            return $this->viewAllAction($twig, array(
                'feature_type_id' => $id,
                'update_form' => $form->createView()->getView(),
            ));
        }
    }

    /**
     * @param int $id
     * @return Response
     */
    public function deleteAction(EventDispatcherInterface $eventDispatcher, Environment $twig, $id): Response
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::DELETE)) {
            return $response;
        }

        $form = $this->createForm(FeatureTypeForm::getName());

        try {
            $this->validateForm($form, 'POST');

            if (null === $featureType = FeatureTypeQuery::create()->findPk($id)) {
                throw new \Exception(Translator::getInstance()->trans(
                    "Feature type not found",
                    array(),
                    FeatureTypeCore::MODULE_DOMAIN
                ));
            }

            $eventDispatcher->dispatch(
                new FeatureTypeEvent($featureType),
                FeatureTypeEvents::FEATURE_TYPE_DELETE
            );

            return $this->generateSuccessRedirect($form);

        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)),
                $e->getMessage(),
                $form
            );

            return $this->viewAllAction($twig);
        }
    }

    /**
     * @param int $id
     * @return Response
     * @throws \Exception
     */
    public function copyAction(Environment $twig, $id): Response
    {
        if (null !== $response = $this->checkAuth(array(), 'FeatureType', AccessManager::CREATE)) {
            return $response;
        }

        $this->setTwig($twig);

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

        $form = $this->createForm(FeatureTypeCreateForm::getName(), data: array(
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
            'image_max_width' => $featureType->getImageMaxWidth(),
            'image_max_height' => $featureType->getImageMaxHeight(),
            'image_ratio' => $featureType->getImageRatio(),
            'title' => $title,
            'description' => $description
        ));

        $this->getParserContext()->addForm($form);

        return $this->renderTwig('feature-type/include/form-create', [
            'form' => $form->createView()->getView(),
            'feature_type_id' => null,
            'edit_language_id' => $this->resolveEditLanguageId(),
            'langs' => $this->buildLangList(),
        ]);
    }

    /**
     * @param Form $form
     * @param int|null $id
     * @return FeatureType
     * @throws \Exception
     */
    protected function hydrateFeatureTypeByForm($form, $id = null): FeatureType
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
            ->setStep($data['step'])
            ->setImageMaxWidth($data['image_max_width'])
            ->setImageMaxHeight($data['image_max_height'])
            ->setImageRatio($data['image_ratio']);

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
    protected function viewFeature($id): Response
    {
        return $this->render("feature-edit", array(
            'feature_id' => $id
        ));
    }

    /**
     * @throws PropelException
     */
    #[Route('/admin/module/feature-type/duplicate/feature/{id}', name: 'featuretype_duplicate', methods: ['POST'])]
    public function duplicateFeature(int $id, Request $request): Response
    {
        if (null !== $response = $this->checkAuth(array(), 'AttributeType', AccessManager::CREATE)) {
            return $response;
        }

        $currentLang = $request->getSession()?->get("thelia.admin.edition.lang")->getLocale();

        try {
            $features = FeatureAvQuery::create()
                ->filterByFeatureId($id)
                ->find()
                ->getData();

            $langs = LangQuery::create()
                ->filterByActive(1)
                ->find()
                ->getData();

            $locales = array_filter(
                array_map(static fn($lang) => $lang->getLocale(), $langs),
                static fn($locale) => $locale !== $currentLang
            );

            foreach ($features as $feature) {
                $title = FeatureAvI18nQuery::create()
                    ->filterByLocale($currentLang)
                    ->filterById($feature->getId())
                    ->findOne()
                    ?->getTitle();

                foreach ($locales as $locale) {
                    $existing = FeatureAvI18nQuery::create()
                        ->filterByLocale($locale)
                        ->filterById($feature->getId())
                        ->findOne();

                    $featureAvI18n = $existing ?? new FeatureAvI18n();
                    $featureAvI18n
                        ->setId($feature->getId())
                        ->setTitle($title)
                        ->setLocale($locale)
                        ->save();

                }
            }

        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                Translator::getInstance()?->trans("%obj modification", ['%obj' => $this->objectName]),
                $e->getMessage()
            );
        }

        return $this->generateRedirect(
            URL::getInstance()?->absoluteUrl("/admin/configuration/features/update?feature_id=" . $id)
        );
    }
}
