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

namespace FeatureType\Form;

use FeatureType\Model\FeatureTypeQuery;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use FeatureType\FeatureType;
use Symfony\Component\Validator\ExecutionContextInterface;
use Thelia\Core\Translation\Translator;

/**
 * Class FeatureTypeCreateForm
 * @package FeatureType\Form
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class FeatureTypeCreateForm extends FeatureTypeForm
{
    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return 'feature_type-create';
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

        $this->formBuilder
            ->add('slug', 'text', array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Slug', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'slug'
                ),
                'constraints' => array(
                    new NotBlank(),
                    new Callback(array(
                        "methods" => array(
                            array($this,
                                "checkFormatType"),
                            array($this,
                                "checkExistType")
                        )
                    ))
                )
            ))
            ->add('title', 'collection', array(
                'type' => 'text',
                'allow_add'    => true,
                'allow_delete' => true,
                'label' => Translator::getInstance()->trans('Title'),
                'label_attr' => array(
                    'for' => 'title'
                ),
                'options' => array(
                    'required' => true
                )
            ))
            ->add('description', 'collection', array(
                'type' => 'text',
                'allow_add'    => true,
                'allow_delete' => true,
                'label_attr' => array(
                    'for' => 'description'
                ),
                'label' => Translator::getInstance()->trans('Description'),
                'options' => array(
                    'required' => true
                )
            ))
            ->add('has_feature_av_value', 'text', array(
                'required' => false,
                'empty_data' => false,
                'label' => Translator::getInstance()->trans('Has feature av value', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'has_feature_av_value'
                )
            ))
            ->add('is_multilingual_feature_av_value', 'text', array(
                'required' => false,
                'empty_data' => false,
                'label' => Translator::getInstance()->trans('Multilingual value', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'is_multilingual_feature_av_value'
                )
            ))
            ->add('pattern', 'text', array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Pattern', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'pattern'
                )
            ))
            ->add('css_class', 'text', array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Input css class', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'css_class'
                )
            ))
            ->add('input_type', 'choice', array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Input type', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'input_type'
                ),
                'empty_value' => 'text',
                'choices'   => array(
                    'text'   => Translator::getInstance()->trans('Type text', array(), FeatureType::MODULE_DOMAIN),
                    'boolean'   => Translator::getInstance()->trans('Type boolean', array(), FeatureType::MODULE_DOMAIN),
                    'textarea'   => Translator::getInstance()->trans('Type textarea', array(), FeatureType::MODULE_DOMAIN),
                    'color'   => Translator::getInstance()->trans('Type color', array(), FeatureType::MODULE_DOMAIN),
                    'number'   => Translator::getInstance()->trans('Type number', array(), FeatureType::MODULE_DOMAIN),
                    'range'   => Translator::getInstance()->trans('Type range', array(), FeatureType::MODULE_DOMAIN),
                    'url'   => Translator::getInstance()->trans('Type url', array(), FeatureType::MODULE_DOMAIN)
                )
            ))
            ->add('min', 'text', array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Input min', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'min'
                )
            ))
            ->add('max', 'text', array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Input max', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'max'
                )
            ))
            ->add('step', 'text', array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Input step', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'step'
                )
            ));
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function checkFormatType($value, ExecutionContextInterface $context)
    {
        // test if good format
        if (!preg_match('/[a-z][a-z_0-9]{3,50}/', $value)) {
            $context->addViolation(Translator::getInstance()->trans(Translator::getInstance()->trans(
                "The slug is not valid",
                array(),
                FeatureType::MODULE_DOMAIN
            )));
        }

        // test if reserved
        if (in_array($value, explode(',', FeatureType::RESERVED_SLUG))) {
            $context->addViolation(Translator::getInstance()->trans(Translator::getInstance()->trans(
                "The feature slug <%slug> is reserved",
                array(
                    '%slug' => $value
                ),
                FeatureType::MODULE_DOMAIN
            )));
        }
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function checkExistType($value, ExecutionContextInterface $context)
    {
        // test if exist
        if (FeatureTypeQuery::create()->findOneBySlug($value) !== null) {
            $context->addViolation(Translator::getInstance()->trans(Translator::getInstance()->trans(
                "The feature slug <%slug> already exists",
                array(
                    '%slug' => $value
                ),
                FeatureType::MODULE_DOMAIN
            )));
        }
    }
}
