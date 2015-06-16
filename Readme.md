# Feature Type

Authors: Thelia <info@thelia.net>, Gilles Bourgeat <gbourgeat@openstudio.fr>

* This module allows you to add to your features the features types.
* Example : Color, Image link to the textures ...
* An feature can have several types.
* An feature type can have values or not.
* Values can be unique by language.

## Compatibility

Thelia >= 2.1

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is ```FeatureType```.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```
composer require thelia/feature-type-module:~1.1.1
```

## Usage

* Once activated, click on the configure button for add or edit the features types.
* For associate an feature to an feature type, edit an feature.

## Hooks

### backoffice :
- feature-type.form-top (in form : create, update, feature type) (params : feature_type_id)
- feature-type.form-bottom (in form : create, update, feature type) (params : feature_type_id)
- feature-type.configuration-top
- feature-type.configuration-bottom
- feature-type.list-action (by feature type) (params : feature_type_id)
- feature-type.configuration-js

## Loop

### feature_type

#### Input arguments

|Argument |Description |
|---      |--- |
|**id**   | A single or a list of ids. |
|**exclude_id** | A single or a list of ids. |
|**slug** | String |
|**feature_id** | A single or a list of features ids. |

#### Output arguments

|Variable       |Description |
|---            |--- |
|ID            | The feature type id |
|SLUG      | The feature type slug |
|TITLE    | The feature type title |
|DESCRIPTION    | The feature type description |
|CSS_CLASS    | The feature type css class |
|PATTERN    | The feature type pattern |
|INPUT_TYPE    | The feature type input type |
|MIN    | The feature type minimum value |
|MAX    | The feature type maximum value |
|STEP    | The feature type step value |
|IS_MULTILINGUAL_FEATURE_AV_VALUE    | Indicates whether the values are unique for each language |
|HAS_FEATURE_AV_VALUE    | Indicates whether the type feature has values for each feature av |

### feature_extend_feature_type

Extends the Thelia loop : [Feature](http://doc.thelia.net/en/documentation/loop/feature.html)

#### Other input arguments

|Argument |Description |
|---      |--- |
|**feature_type_id**   | A single or a list of features type ids. |
|**feature_type_slug**   | A single or a list of features type slugs. |

#### Other output arguments

* The features types associated.
* The variable name is equal to the name of the slug,
* The value is boolean, true for associated, false for unassociated.

#### Example
```smarty
    {loop name="feature_extend_feature_type" type="feature_extend_feature_type" feature_type_id="1,2,3"}
        {$TITLE} <br/>

        {if $COLOR}
            The feature has type color
        {/if}

        {if $MY_FEATURE_TYPE}
            The feature has type "My feature type"
        {/if}
    {/loop}
 ```

### feature_availability_extend_feature_type

Extends the Thelia loop : [Feature availability](http://doc.thelia.net/en/documentation/loop/feature_availability.html)

#### Other input arguments

|Argument |Description |
|---      |--- |
|**feature_type_id**   | A single or a list of features type ids. |
|**feature_type_slug**   | A single or a list of features type slugs. |

#### Other output arguments

* The features types associated.
* The variable name is equal to the name of the slug,
* The variable contains the value.

#### Example
```smarty
    title : color : my feature type
    {loop name="feature_availability_extend_feature_type" type="feature_availability_extend_feature_type" feature="1"}
        {$TITLE} : {$COLOR} : {$MY_FEATURE_TYPE} <br/>
    {/loop}

    title : color : my feature type
    {loop name="feature_availability_extend_feature_type" type="feature_availability_extend_feature_type" feature_type_slug="color"}
        {$TITLE} : {$COLOR} : {$MY_FEATURE_TYPE} <br/>
    {/loop}
```

### feature_value_extend_feature_type

Extends the Thelia loop : [Feature value](http://doc.thelia.net/en/documentation/loop/feature_value.html)

#### Other output arguments

* The features types associated.
* The variable name is equal to the name of the slug,
* The variable contains the value.

#### Example
```smarty
    title : color
    {loop name="feature_value_extend_feature_type" type="feature_value_extend_feature_type" feature="1" product="1"}
        {$TITLE} : {$COLOR} <br/>
    {/loop}
```

## Model

### FeatureType::getValue

```php
    /**
     * Returns a value based on the slug, feature_av_id and locale
     *
     * <code>
     * $value  = FeatureType::getValue('color', 2);
     * </code>
     *
     * @param string $slug
     * @param int $featureId
     * @param string $locale
     * @return string
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public static function getValue($slug, $featureId, $locale = 'en_US')
```

### FeatureType::getValues

```php
    /**
     * Returns a set of values
     * If the value does not exist, it is replaced by null
     *
     * <code>
     * $values = FeatureType::getValue(['color','texture'], [4,7]);
     * </code>
     *
     * <sample>
     *  array(
     *  'color' => [4 => '#00000', 7 => '#FFF000'],
     *  'texture' => [4 => null, 7 => 'lines.jpg']
     * )
     * </sample>
     *
     * @param array $slugs[]
     * @param array $featureIds[]
     * @param string $locale
     * @return string
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public static function getValues(array $slugs, array $featureIds, $locale = 'en_US')
```

### FeatureType::getFirstValues

```php
    /**
     * Returns a set of first values
     * If the value does not exist, it is replaced by null
     *
     * <code>
     * $values = FeatureType::getFirstValues(['color','texture', 'other'], [4,7]);
     * </code>
     *
     * <sample>
     *  array(
     *  'color' => '#00000',
     *  'texture' => 'lines.jpg',
     *  'other' => null
     * )
     * </sample>
     *
     * @param array $slugs
     * @param array $featureIds
     * @param string $locale
     * @return array
     */
    public static function getFirstValues(array $slugs, array $featureIds, $locale = 'en_US')
```