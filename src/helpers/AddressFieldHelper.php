<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\helpers;

use barrelstrength\sproutbasefields\base\AddressFieldTrait;
use barrelstrength\sproutforms\fields\formfields\Address as AddressFormField;
use barrelstrength\sproutfields\fields\Address as AddressField;
use barrelstrength\sproutbasefields\models\Address as AddressModel;
use barrelstrength\sproutbasefields\services\Address as AddressService;
use barrelstrength\sproutbasefields\SproutBaseFields;
use Craft;
use CommerceGuys\Addressing\Country\CountryRepository;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\errors\SiteNotFoundException;
use craft\helpers\ElementHelper;
use craft\helpers\Template;
use Exception;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\db\StaleObjectException;

/**
 * Helper class for shared methods between the Sprout Fields Address Field and the Sprout Forms Address Field.
 * Both need to extend different classes so we need this additional helper method to share a few methods. We
 * are not able to use a Trait or an Interface as some shared methods need to call their parent::someMethod();
 *
 * @package barrelstrength\sproutbasefields\helpers
 */
class AddressFieldHelper
{
    /**
     * @param FieldInterface|AddressField|AddressFormField $field
     *
     * @return null|string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SiteNotFoundException
     * @throws SyntaxError
     */
    public function getSettingsHtml(FieldInterface $field)
    {
        $countryRepositoryHelper = new CountryRepositoryHelper();
        $addressingAvailableLocales = $countryRepositoryHelper->getAvailableLocales();

        $craftAvailableLocales = [];

        foreach (Craft::$app->getI18n()->getAllLocales() as $locale) {
            $craftAvailableLocales[$locale->id] = Craft::t('app', '{id} – {name}', [
                'name' => $locale->getDisplayName(Craft::$app->language),
                'id' => $locale->id
            ]);
        }

        $availableLocales = [];

        foreach ($craftAvailableLocales as $localeId => $localeName) {
            if (in_array($localeId, $addressingAvailableLocales, true)) {
                $availableLocales[$localeId] = $localeName;
            }
        }

        if ($field->defaultLanguage === null) {
            $field->defaultLanguage = AddressService::DEFAULT_LANGUAGE;

            // If the primary site language is available choose it as a default language.
            $primarySiteLocaleId = Craft::$app->getSites()->getPrimarySite()->language;
            if (isset($availableLocales[$primarySiteLocaleId])) {
                $field->defaultLanguage = $primarySiteLocaleId;
            }
        }

        // Countries
        if ($field->defaultCountry === null) {
            $field->defaultCountry = AddressService::DEFAULT_COUNTRY;
        }

        $countryRepository = new CountryRepository();
        $countries = $countryRepository->getList($field->defaultLanguage);

        if (count($field->highlightCountries)) {
            $highlightCountries = SproutBaseFields::$app->addressFormatter->getHighlightCountries($field->highlightCountries);
            $countries = array_merge($highlightCountries, $countries);
        }

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/address/settings', [
                'field' => $field,
                'countries' => $countries,
                'languages' => $availableLocales
            ]
        );
    }

    /**
     * @param Field|AddressFieldTrait $field
     * @param                         $value
     * @param ElementInterface|null   $element
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getInputHtml(Field $field, $value, ElementInterface $element = null): string
    {
        /** @var $this Field */
        $name = $field->handle;

        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputName = Craft::$app->getView()->namespaceInputName($inputId);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        /** @var $this Field */
        $settings = $field->getSettings();

        $defaultLanguage = $settings['defaultLanguage'] ?? AddressService::DEFAULT_LANGUAGE;
        $defaultCountryCode = $settings['defaultCountry'] ?? AddressService::DEFAULT_COUNTRY;
        $showCountryDropdown = $settings['showCountryDropdown'] ?? null;

        $addressId = null;

        if (is_object($value)) {
            $addressId = $value->id;
        } elseif (is_array($value)) {
            $addressId = $value['id'];
        }

        $addressModel = SproutBaseFields::$app->addressField->getAddressFromElement($element, $field->id);

        $countryCode = $addressModel->countryCode ?? $defaultCountryCode;

        $addressFormatter = SproutBaseFields::$app->addressFormatter;
        $addressFormatter->setNamespace($name);
        $addressFormatter->setLanguage($defaultLanguage);
        $addressFormatter->setCountryCode($countryCode);
        $addressFormatter->setAddressModel($addressModel);

        if (count($field->highlightCountries)) {
            $addressFormatter->setHighlightCountries($field->highlightCountries);
        }

        $addressDisplayHtml = $addressId ? $addressFormatter->getAddressDisplayHtml($addressModel) : '';
        $countryInputHtml = $addressFormatter->getCountryInputHtml($showCountryDropdown);
        $addressFormHtml = $addressFormatter->getAddressFormHtml();

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/address/input', [
                'namespaceInputId' => $namespaceInputId,
                'namespaceInputName' => $namespaceInputName,
                'field' => $field,
                'fieldId' => $addressModel->fieldId ?? null,
                'addressId' => $addressId,
                'defaultCountryCode' => $defaultCountryCode,
                'addressDisplayHtml' => Template::raw($addressDisplayHtml),
                'countryInputHtml' => Template::raw($countryInputHtml),
                'addressFormHtml' => Template::raw($addressFormHtml),
                'showCountryDropdown' => $showCountryDropdown
            ]
        );
    }

    public function getStaticHtml(Field $field, $value, ElementInterface $element = null): string
    {
        $noAddressHtml = '<p class="light">'.Craft::t('sprout-base-fields', 'No address saved.').'</p>';

        if (!$value instanceof AddressModel) {
            return $noAddressHtml;
        }

        /** @var $this Field */
        $name = $field->handle;

        $addressModel = SproutBaseFields::$app->addressField->getAddressFromElement($element, $field->id);

        /** @var $this Field */
        $settings = $field->getSettings();
        $defaultLanguage = $settings['defaultLanguage'] ?? AddressService::DEFAULT_LANGUAGE;
        $defaultCountryCode = $settings['defaultCountry'] ?? AddressService::DEFAULT_COUNTRY;
        $countryCode = $addressModel->countryCode ?? $defaultCountryCode;
        $addressFormatter = SproutBaseFields::$app->addressFormatter;
        $addressFormatter->setNamespace($name);
        $addressFormatter->setLanguage($defaultLanguage);
        $addressFormatter->setCountryCode($countryCode);
        $addressFormatter->setAddressModel($addressModel);
        if (count($field->highlightCountries)) {
            $addressFormatter->setHighlightCountries($field->highlightCountries);
        }

        $addressDisplayHtml = $addressFormatter->getAddressDisplayHtml($addressModel) ;

        if (empty($addressDisplayHtml)) {
            return $noAddressHtml;
        }

        return $addressDisplayHtml;
    }

    /**
     * Prepare our Address for use as an AddressModel
     *
     * @param                       $addressField
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|AddressModel|int|mixed|string
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function normalizeValue($addressField, $value, ElementInterface $element = null)
    {
//        return null;

        if (!$element instanceof Element) {
            return null;
        }

        //        $isDraftOrRevision = ElementHelper::isDraftOrRevision($element);

        $addressModel = SproutBaseFields::$app->addressField->getAddressFromElement($element, $addressField->id);

        // Add the address field array from the POST data to the Address Model
        // @todo - find a more appropriate place to delete a cleared address
        if (is_array($value)) {
            if (!empty($value['delete'])) {
                SproutBaseFields::$app->addressField->deleteAddressById($value['id']);
                return null;
            }

            $value['fieldId'] = $addressField->id ?? null;
            $addressModel = new AddressModel();
            $addressModel->setAttributes($value, false);
        }

        return $addressModel;
    }

    /**
     *
     * Prepare the field value for the database.
     *
     * We store the Address ID in the content column.
     *
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return array|bool|mixed|null|string
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if (empty($value)) {
            return false;
        }

        $addressId = null;

        // When loading a Field Layout with an Address Field
        if (is_object($value) && get_class($value) == AddressModel::class) {
            $addressId = $value->id;
        }

        // For the ResaveElements task $value is the id
        if (is_int($value)) {
            $addressId = $value;
        }

        // When the field is saved by post request the id an attribute on $value
        if (isset($value['id']) && $value['id']) {
            $addressId = $value['id'];
        }

        // Save the addressId in the content table
        return $addressId;
    }

    /**
     * Save our Address Field a first time and assign the Address Record ID back to the Address field model
     * We'll save our Address Field a second time in afterElementSave to capture the Element ID for new entries.
     *
     * @param FieldInterface           $field
     * @param Element|ElementInterface $element
     * @param bool                     $isNew
     *
     * @return void
     * @throws Exception
     * @throws Throwable
     */
    public function beforeElementSave(FieldInterface $field, ElementInterface $element, bool $isNew)
    {
        $address = $element->getFieldValue($field->handle);

        // If our address is null, we're probably deleting something
        if (!$address instanceof AddressModel) {
            return;
        }

        // Make sure we don't overwrite the root record when saving revisions
        if ($isNew) {
            $address->id = null;
        }

        $address->elementId = $element->id;
        $address->siteId = $element->siteId;
        $address->fieldId = $field->id;

        SproutBaseFields::$app->addressField->saveAddress($address);

    }

    /**
     * Save our Address Field a second time for New Entries to ensure we have the Element ID.
     *
     * @param FieldInterface   $field
     * @param ElementInterface $element
     * @param bool             $isNew
     *
     * @throws Throwable
     */
    public function afterElementSave(FieldInterface $field, ElementInterface $element, bool $isNew)
    {
        /** @var $this Field */
        $address = $element->getFieldValue($field->handle);

        if (!$isNew || !$address instanceof AddressModel) {
            return;
        }

        $address->elementId = $element->id;

        SproutBaseFields::$app->addressField->saveAddress($address);
    }
}