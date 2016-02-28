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
use Symfony\Component\Validator\Constraints\NotBlank;
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
}
