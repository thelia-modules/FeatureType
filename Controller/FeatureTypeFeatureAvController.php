<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Controller;

use FeatureType\Event\FeatureTypeEvents;
use FeatureType\Event\FeatureTypeAvMetaEvent;
use FeatureType\FeatureType;
use FeatureType\Form\FeatureTypeAvMetaUpdateForm;
use FeatureType\Model\FeatureFeatureType;
use FeatureType\Model\FeatureFeatureTypeQuery;
use FeatureType\Model\FeatureTypeAvMeta;
use FeatureType\Model\FeatureTypeAvMetaQuery;
use FeatureType\Model\FeatureTypeQuery;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Files\Exception\ProcessFileException;
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
    public function updateMetaAction(EventDispatcherInterface $eventDispatcher, $feature_id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::FEATURE), null, AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm("feature_type_av_meta-update");

        try {
            $formUpdate = $this->validateForm($form);

            $featureAvs = $formUpdate->get('feature_av')->getData();

            foreach ($featureAvs as $featureAvId => $featureAv) {
                foreach ($featureAv['lang'] as $langId => $lang) {
                    foreach ($lang['feature_type'] as $featureTypeId => $value) {
                        $values = [];
                        $values[$langId] = $value;
                        $featureType = FeatureTypeQuery::create()
                            ->findOneById($featureTypeId);

                        if ($featureType->getInputType() === "image") {
                            if (null === $value) {
                                continue;
                            }

                            $uploadedFileName = $this->uploadFile($value);
                            $values[$langId] = $uploadedFileName;

                            if (!$featureType->getIsMultilingualFeatureAvValue()) {
                                $activeLangs = LangQuery::create()
                                    ->filterByActive(1)
                                    ->find();

                                /** @var Lang $lang */
                                foreach ($activeLangs as $lang) {
                                    $values[$lang->getId()] = $uploadedFileName;
                                }
                            }
                        }

                        foreach ($values as $langId => $langValue) {
                            $this->dispatchEvent(
                                $eventDispatcher,
                                $this->getFeatureFeatureType($featureTypeId, $feature_id),
                                $featureAvId,
                                $langId,
                                $langValue
                            );
                        }
                    }
                }
            }

            $this->resetUpdateForm();
            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)),
                $e->getMessage(),
                $form,
                $e
            );

            return $this->viewFeature($feature_id);
        }
    }

    public function deleteMetaAction(EventDispatcherInterface $eventDispatcher, $feature_id, $feature_type_id, $feature_av_id, $lang_id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::FEATURE), null, AccessManager::DELETE)) {
            return $response;
        }

        $form = $this->createForm("feature_type.delete");

        try {
             $this->validateForm($form);

            $featureType = FeatureTypeQuery::create()
                ->findOneById($feature_type_id);

            $featureFeatureType =  $this->getFeatureFeatureType($feature_type_id, $feature_id);

            $eventName = FeatureTypeEvents::FEATURE_TYPE_AV_META_DELETE;

            $featureAvMetaQuery = FeatureTypeAvMetaQuery::create()
                ->filterByFeatureAvId($feature_av_id)
                ->filterByFeatureFeatureTypeId($featureFeatureType->getId());

            if ($featureType->getIsMultilingualFeatureAvValue()) {
                $featureAvMetaQuery->filterByLocale($this->getLocale($lang_id));
            }

            $featureAvMetas = $featureAvMetaQuery->find();

            foreach ($featureAvMetas as $featureAvMeta) {
                $eventDispatcher->dispatch(
                    (new FeatureTypeAvMetaEvent($featureAvMeta)),
                    $eventName
                );
            }

            $this->resetUpdateForm();
            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)),
                $e->getMessage(),
                $form,
                $e
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
    protected function dispatchEvent(EventDispatcherInterface $eventDispatcher, FeatureFeatureType $featureFeatureType, $featureAvId, $langId, $value)
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

        $eventDispatcher->dispatch(
            (new FeatureTypeAvMetaEvent($featureAvMeta)),
            $eventName
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

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function uploadFile(UploadedFile $file)
    {
        if ($file->getError() == UPLOAD_ERR_INI_SIZE) {
            $message = $this->getTranslator()
                ->trans(
                    'File is too large, please retry with a file having a size less than %size%.',
                    array('%size%' => ini_get('upload_max_filesize')),
                    'core'
                );

            throw new ProcessFileException($message, 403);
        }

        $validMimeTypes = [
            'image/jpeg' => ["jpg", "jpeg"],
            'image/png' => ["png"],
            'image/gif' => ["gif"]
        ];
        $mimeType = $file->getMimeType();
        if (!isset($validMimeTypes[$mimeType])) {
            $message = $this->getTranslator()
                ->trans(
                    'Only files having the following mime type are allowed: %types%',
                    [ '%types%' => implode(', ', array_keys($validMimeTypes))]
                );

            throw new ProcessFileException($message, 415);
        }

        $regex = "#^(.+)\.(".implode("|", $validMimeTypes[$mimeType]).")$#i";

        $realFileName = $file->getClientOriginalName();
        if (!preg_match($regex, $realFileName)) {
            $message = $this->getTranslator()
                ->trans(
                    "There's a conflict between your file extension \"%ext\" and the mime type \"%mime\"",
                    [
                        '%mime' => $mimeType,
                        '%ext' => $file->getClientOriginalExtension()
                    ]
                );

            throw new ProcessFileException($message, 415);
        }

        $fileSystem = new Filesystem();
        $fileSystem->mkdir(THELIA_WEB_DIR. DS .FeatureType::FEATURE_TYPE_AV_IMAGE_FOLDER);

        $fileName = $this->generateUniqueFileName().'_'.$realFileName;
        $file->move(THELIA_WEB_DIR. DS .FeatureType::FEATURE_TYPE_AV_IMAGE_FOLDER, $fileName);
        return DS . FeatureType::FEATURE_TYPE_AV_IMAGE_FOLDER. DS .$fileName;
    }

    /**
     * @return string
     */
    protected function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return substr(md5(uniqid()), 0, 10);
    }

    protected function resetUpdateForm() {
        $this->getParserContext()->remove(FeatureTypeAvMetaUpdateForm::class.':form');
        $theliaFormErrors = $this->getRequest()->getSession()->get('thelia.form-errors');
        unset($theliaFormErrors[FeatureTypeAvMetaUpdateForm::class.':form']);
        $this->getRequest()->getSession()->set('thelia.form-errors', $theliaFormErrors);
    }
}
