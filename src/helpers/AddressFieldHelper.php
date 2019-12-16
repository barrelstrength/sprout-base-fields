<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\helpers;

use barrelstrength\sproutbasefields\models\Address as AddressModel;
use barrelstrength\sproutbasefields\SproutBaseFields;
use Craft;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Address;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\errors\SiteNotFoundException;
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
     * @return null|string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws SiteNotFoundException
     */
    public function getSettingsHtml($field)
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

        if ($this->defaultLanguage === null) {
            $this->defaultLanguage = \barrelstrength\sproutbasefields\services\Address::DEFAULT_LANGUAGE;

            // If the primary site language is available choose it as a default language.
            $primarySiteLocaleId = Craft::$app->getSites()->getPrimarySite()->language;
            if (isset($availableLocales[$primarySiteLocaleId])) {
                $this->defaultLanguage = $primarySiteLocaleId;
            }
        }

        // Countries
        if ($this->defaultCountry === null) {
            $this->defaultCountry = Address::DEFAULT_COUNTRY;
        }

        $countryRepository = new CountryRepository();
        $countries = $countryRepository->getList($this->defaultLanguage);

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

        $defaultLanguage = $settings['defaultLanguage'] ?? 'en';
        $defaultCountryCode = $settings['defaultCountry'] ?? null;
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
        $addressModel = new AddressModel();

        $elementId = $element->id ?? null;
        $siteId = $element->siteId ?? null;
        $fieldId = $addressField->id;

        // @todo Address field column has Numeric value when retrieved from db, however, we can
        // possibly remove that column and just depend on the relational information to get the field data
        if (is_numeric($value) OR ($elementId && $siteId && $fieldId)) {
            $addressModel = SproutBaseFields::$app->addressField->getAddress($elementId, $siteId, $fieldId);
        }

        // Array value from post data
        if (is_array($value)) {
            if (!empty($value['delete'])) {
                SproutBaseFields::$app->addressField->deleteAddressById($value['id']);
            } else {
                $value['fieldId'] = $addressField->id ?? null;
                $addressModel = new AddressModel();
                $addressModel->setAttributes($value, false);
            }
        }

        // Adds country property that return country name
        if ($addressModel->countryCode) {
            $countryRepository = new CountryRepository();
            $country = $countryRepository->get($addressModel->countryCode);

            $addressModel->country = $country->getName();
            $addressModel->countryCode = $country->getCountryCode();
            $addressModel->countryThreeLetterCode = $country->getThreeLetterCode();
            $addressModel->currencyCode = $country->getCurrencyCode();
            $addressModel->locale = $country->getLocale();

            $subdivisionRepository = new SubdivisionRepository();
            $subdivision = $subdivisionRepository->get($addressModel->administrativeAreaCode, [$addressModel->countryCode]);

            if ($subdivision) {
                $addressModel->administrativeArea = $subdivision->getName();
            }
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
     */
    public function beforeElementSave(FieldInterface $field, ElementInterface $element, bool $isNew)
    {
        $address = $element->getFieldValue($field->handle);

        if ($address instanceof AddressModel) {
            $address->elementId = $element->id;
            $address->siteId = $element->siteId;
            $address->fieldId = $field->id;

            SproutBaseFields::$app->addressField->saveAddress($address);
        }
    }

    /**
     * Save our Address Field a second time for New Entries to ensure we have the Element ID.
     *
     * @param FieldInterface           $field
     * @param Element|ElementInterface $element
     * @param bool                     $isNew
     *
     * @return bool|void
     * @throws Exception
     */
    public function afterElementSave(FieldInterface $field, ElementInterface $element, bool $isNew)
    {
        if (!$isNew) {
            return;
        }

        /** @var $this Field */
        $address = $element->getFieldValue($field->handle);

        if ($address instanceof AddressModel) {
            // WHEN the REVISION ID changes, the ADDRESS ID stays the same... updating the original address..

            $address->elementId = $element->id;
            SproutBaseFields::$app->addressField->saveAddress($address);
        }
    }
}