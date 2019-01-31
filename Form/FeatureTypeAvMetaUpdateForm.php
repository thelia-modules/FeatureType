<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Form;

use FeatureType\FeatureType;
use FeatureType\Form\Type\I18nType;
use FeatureType\Model\FeatureTypeQuery;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Thelia\Core\Translation\Translator;

/**
 * Class FeatureTypeAvMetaUpdateForm
 * @package FeatureType\Form
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeAvMetaUpdateForm extends FeatureTypeForm
{
    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return 'feature_type_av_meta-update';
    }

    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder feature :
     *
     */
    protected function buildForm()
    {
        parent::buildForm();

            $this->formBuilder->add(
                'feature_av',
                'collection',
                array(
                    'type' => new I18nType(),
                    'constraints' => array(
                        new Callback(array(
                            "methods" => array(
                                array($this,
                                    "checkImageSize"),
                        ))
                    )),
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'label_attr' => array(
                        'for' => 'description'
                    ),
                    'label' => Translator::getInstance()->trans('Description', array(), FeatureType::MODULE_DOMAIN),
                    'options' => array(
                        'required' => true
                    )
                )
            );

            $this->formBuilder->add(
                'feature_id',
                'integer',
                array(
                    'constraints' => array(
                        new NotBlank()
                    )
                )
            );
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function checkImageSize($value, ExecutionContextInterface $context)
    {
        foreach ($value as $featureAvId => $featureAv) {
            foreach ($featureAv['lang'] as $langId => $lang) {
                foreach ($lang['feature_type'] as $featureTypeId => $value) {

                    if (!$value instanceof UploadedFile) {
                        continue;
                    }

                    $featureType = FeatureTypeQuery::create()
                        ->findOneById($featureTypeId);

                    $size = getimagesize($value);
                    list($width, $height) = $size;

                    if (null !== $featureType->getImageMaxWidth() && $width > $featureType->getImageMaxWidth()) {
                        $context->addViolation(Translator::getInstance()->trans(Translator::getInstance()
                            ->trans(
                                "Your image is too large (maximum %width px)",
                                [
                                    '%width' => $featureType->getImageMaxWidth(),
                                ]
                            ))
                        );
                    }

                    if (null !== $featureType->getImageMaxHeight() && $height > $featureType->getImageMaxHeight()) {
                        $context->addViolation(Translator::getInstance()->trans(Translator::getInstance()
                            ->trans(
                                "Your image is too tall (maximum %height px)",
                                [
                                    '%height' => $featureType->getImageMaxHeight(),
                                ]
                            ))
                        );
                    }

                    if (null !== $featureType->getImageRatio() && ($width/$height) !== $featureType->getImageRatio()) {
                        $context->addViolation(Translator::getInstance()->trans(Translator::getInstance()
                            ->trans(
                                "Bad image ratio (%ratio required)",
                                [
                                    '%ratio' => $featureType->getImageRatio(),
                                ]
                            ))
                        );
                    }
                }
            }
        }
    }
}
