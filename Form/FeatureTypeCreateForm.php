<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Form;

use FeatureType\Model\FeatureTypeQuery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use FeatureType\FeatureType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Thelia\Core\Translation\Translator;

/**
 * Class FeatureTypeCreateForm
 * @package FeatureType\Form
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeCreateForm extends FeatureTypeForm
{
    /**
     * @return string the name of you form. This name must be unique
     */
    public static function getName()
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
            ->add('slug', TextType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Slug', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'slug'
                ),
                'constraints' => array(
                    new NotBlank(),
                    new Callback([$this, "checkFormatType"]),
                    new Callback([$this, "checkExistType"])
                )
            ))
            ->add('title', CollectionType::class, array(
                'entry_type' => TextType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'label' => Translator::getInstance()->trans('Title'),
                'label_attr' => array(
                    'for' => 'title'
                ),
                'required' => true
            ))
            ->add('description', CollectionType::class, array(
                'entry_type' => TextType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'label_attr' => array(
                    'for' => 'description'
                ),
                'label' => Translator::getInstance()->trans('Description'),
                'required' => true
            ))
            ->add('has_feature_av_value', TextType::class, array(
                'required' => false,
                'empty_data' => false,
                'label' => Translator::getInstance()->trans('Has feature av value', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'has_feature_av_value'
                )
            ))
            ->add('is_multilingual_feature_av_value', TextType::class, array(
                'required' => false,
                'empty_data' => false,
                'label' => Translator::getInstance()->trans('Multilingual value', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'is_multilingual_feature_av_value'
                )
            ))
            ->add('pattern', TextType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Pattern', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'pattern'
                )
            ))
            ->add('css_class', TextType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Input css class', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'css_class'
                )
            ))
            ->add('input_type', ChoiceType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Input type', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'input_type'
                ),
                'empty_data' => 'text',
                'choices'   => array(
                    Translator::getInstance()->trans('Type text', array(), FeatureType::MODULE_DOMAIN) => 'text',
                    Translator::getInstance()->trans('Type boolean', array(), FeatureType::MODULE_DOMAIN) => 'boolean',
                    Translator::getInstance()->trans('Type textarea', array(), FeatureType::MODULE_DOMAIN) => 'textarea',
                    Translator::getInstance()->trans('Type color', array(), FeatureType::MODULE_DOMAIN) => 'color',
                    Translator::getInstance()->trans('Type number', array(), FeatureType::MODULE_DOMAIN) => 'number',
                    Translator::getInstance()->trans('Type range', array(), FeatureType::MODULE_DOMAIN) => 'range',
                    Translator::getInstance()->trans('Type url', array(), FeatureType::MODULE_DOMAIN) => 'url',
                    Translator::getInstance()->trans('Type image', array(), FeatureType::MODULE_DOMAIN) => 'image'
                )
            ))
            ->add('min', TextType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Input min', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'min'
                )
            ))
            ->add('max', TextType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Input max', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'max'
                )
            ))
            ->add('step', TextType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Input step', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'step'
                )
            ))
            ->add('image_max_width', NumberType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Image max width', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'image_max_width'
                )
            ))
            ->add('image_max_height', NumberType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Image max height', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'image_max_height'
                )
            ))
            ->add('image_ratio', NumberType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Image ratio', array(), FeatureType::MODULE_DOMAIN),
                'label_attr' => array(
                    'for' => 'image_ratio'
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
            $context->addViolation(Translator::getInstance()->trans(
                "The slug is not valid",
                array(),
                FeatureType::MODULE_DOMAIN
            ));
        }

        // test if reserved
        if (in_array($value, explode(',', FeatureType::RESERVED_SLUG))) {
            $context->addViolation(Translator::getInstance()->trans(
                "The feature slug <%slug> is reserved",
                array(
                    '%slug' => $value
                ),
                FeatureType::MODULE_DOMAIN
            ));
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
            $context->addViolation(Translator::getInstance()->trans(
                "The feature slug <%slug> already exists",
                array(
                    '%slug' => $value
                ),
                FeatureType::MODULE_DOMAIN
            ));
        }
    }
}
