<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\services;

use barrelstrength\sproutbasefields\base\AddressFieldTrait;
use barrelstrength\sproutbasefields\models\Address as AddressModel;
use barrelstrength\sproutbasefields\events\OnSaveAddressEvent;
use barrelstrength\sproutbasefields\records\Address as AddressRecord;
use barrelstrength\sproutbasefields\SproutBaseFields;
use barrelstrength\sproutforms\base\FormField;
use Craft;
use craft\base\Component;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\errors\SiteNotFoundException;
use craft\helpers\ArrayHelper;
use craft\helpers\ElementHelper;
use craft\models\Site;
use Exception;
use InvalidArgumentException;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\db\StaleObjectException;

class Address extends Component
{
    const EVENT_ON_SAVE_ADDRESS = 'onSaveAddressEvent';

    const DEFAULT_COUNTRY = 'US';
    const DEFAULT_LANGUAGE = 'en';

    /**
     * @param FieldInterface   $field
     * @param ElementInterface $element
     *
     * @param bool             $isNew
     *
     * @return bool
     * @throws StaleObjectException
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function saveAddress(FieldInterface $field, ElementInterface $element, bool $isNew): bool
    {
        /** @var Element $element */
        /** @var Field|FormField|AddressFieldTrait $field */
        $address = $element->getFieldValue($field->handle);

        // If we don't have an address model, delete the old address associated with this field
        if (!$address instanceof AddressModel) {
            Craft::$app->db->createCommand()
                ->delete('{{%sproutfields_addresses}}', [
                    'elementId' => $element->id,
                    'siteId' => $element->siteId,
                    'fieldId' => $field->id
                ])
                ->execute();

            return true;
        }

        // If the user cleared the address, delete it if it exists and don't save anything
        if ($deletedAddressId = $field->getDeletedAddressId()) {
            SproutBaseFields::$app->addressField->deleteAddressById($deletedAddressId);
            return true;
        }

        $record = AddressRecord::findOne([
            'elementId' => $element->id,
            'siteId' => $element->siteId,
            'fieldId' => $field->id
        ]);

        if (!$record) {
            $record = new AddressRecord();
        }

        if ($isNew) {
            $record->id = null;
        }

        $record->elementId = $element->id;
        $record->siteId = $element->siteId;
        $record->fieldId = $field->id;
        $record->countryCode = $address->countryCode;
        $record->administrativeAreaCode = $address->administrativeAreaCode;
        $record->locality = $address->locality;
        $record->dependentLocality = $address->dependentLocality;
        $record->postalCode = $address->postalCode;
        $record->sortingCode = $address->sortingCode;
        $record->address1 = $address->address1;
        $record->address2 = $address->address2;

        if (!$address->validate()) {
            return false;
        }

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            $record->save();

            $address->id = $record->id;

            $this->deleteUnusedAddresses();

            $this->afterSaveAddress($address, $element);

            $transaction->commit();

            return true;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param FieldInterface   $field
     * @param ElementInterface $source
     * @param ElementInterface $target
     * @param bool             $isNew
     *
     * @throws Throwable
     */
    public function duplicateAddress(FieldInterface $field, ElementInterface $source, ElementInterface $target, bool $isNew)
    {
        /** Element $target */
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            SproutBaseFields::$app->addressField->saveAddress($field, $target, $isNew);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Deletes any addresses that are found that no longer match an existing Element ID
     *
     * @throws \yii\db\Exception
     */
    public function deleteUnusedAddresses()
    {
        $addressIdsWithDeletedElementIds = (new Query())
            ->select('addresses.id')
            ->from('{{%sproutfields_addresses}} addresses')
            ->leftJoin('{{%elements}} elements', '[[addresses.elementId]] = [[elements.id]]')
            ->where(['elements.id' => null])
            ->column();

        Craft::$app->db->createCommand()
            ->delete('{{%sproutfields_addresses}}', [
                'id' => $addressIdsWithDeletedElementIds
            ])
            ->execute();
    }

    /**
     * @param $id
     *
     * @return AddressModel|null
     */
    public function getAddressById($id)
    {
        $result = (new Query())
            ->select([
                'id',
                'elementId',
                'siteId',
                'fieldId',
                'countryCode',
                'administrativeAreaCode',
                'locality',
                'dependentLocality',
                'postalCode',
                'sortingCode',
                'address1',
                'address2'
            ])
            ->from('{{%sproutfields_addresses}}')
            ->where(['id' => $id])
            ->one();

        return $result ? new AddressModel($result) : null;
    }

    /**
     * @param ElementInterface $element
     * @param                  $fieldId
     *
     * @return AddressModel|null
     */
    public function getAddressFromElement(ElementInterface $element, $fieldId)
    {
        $elementId = $element->id ?? null;

        if (!$elementId) {
            return null;
        }

        /** @var Element $element */
        $query = (new Query())
            ->select([
                'id',
                'elementId',
                'siteId',
                'fieldId',
                'countryCode',
                'administrativeAreaCode',
                'locality',
                'dependentLocality',
                'postalCode',
                'sortingCode',
                'address1',
                'address2'
            ])
            ->from('{{%sproutfields_addresses}}')
            ->where([
                'siteId' => $element->siteId,
                'fieldId' => $fieldId
            ]);

        if ($element->id) {
            $query->andWhere(['elementId' => $element->id]);
        }

        $result = $query->one();

        return $result ? new AddressModel($result) : null;
    }

    /**
     * @param null $id
     *
     * @return bool|false|int
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteAddressById($id)
    {
        $record = AddressRecord::findOne($id);

        if ($record) {
            return $record->delete();
        }

        return false;
    }

    /**
     * @param AddressModel $model
     * @param              $source
     */
    public function afterSaveAddress(AddressModel $model, $source)
    {
        $event = new OnSaveAddressEvent([
            'model' => $model,
            'address' => $model,
            'source' => $source
        ]);

        $this->trigger(self::EVENT_ON_SAVE_ADDRESS, $event);
    }

    /**
     * @param FieldInterface $field
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws SiteNotFoundException
     */
    public function getSettingsHtml(FieldInterface $field): string
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

        if (!$addressModel) {
            $addressModel = new AddressModel();
            $addressModel->countryCode = $defaultCountryCode;
            $addressModel->fieldId = $field->id;
        }

        // Override the Default Country Code with the current country code if it exists
        $defaultCountryCode = $addressModel->countryCode ?? $defaultCountryCode;

        $addressFormatter = SproutBaseFields::$app->addressFormatter;
        $addressFormatter->setNamespace($name);
        $addressFormatter->setLanguage($defaultLanguage);
        $addressFormatter->setCountryCode($defaultCountryCode);
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
                'fieldId' => $addressModel->fieldId ?? $field->id ?? null,
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

        $addressDisplayHtml = $addressFormatter->getAddressDisplayHtml($addressModel);

        if (empty($addressDisplayHtml)) {
            return $noAddressHtml;
        }

        return $addressDisplayHtml;
    }

    /**
     * Prepare our Address for use as an AddressModel
     *
     * @param FieldInterface        $addressField
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return AddressModel|null
     */
    public function normalizeValue(FieldInterface $addressField, $value, ElementInterface $element = null)
    {
        if ($value instanceof AddressModel) {
            return $value;
        }

        if (!$element instanceof ElementInterface) {
            return null;
        }

        // Mark this address for deletion. This is processed in the saveAddress method
        $deleteAddress = (int)$value['delete'];

        $address = SproutBaseFields::$app->addressField->getAddressFromElement($element, $addressField->id);

        /** @var AddressFieldTrait $addressField */
        if ($deleteAddress) {
            // Use the ID from the Address found in the database because the posted Address ID may not
            // match the current Address ID if we're duplicating an Element
            $addressField->setDeletedAddressId($address->id ?? null);
        }

        // Add the address field array from the POST data to the Address Model address
        if (is_array($value)) {

            if ($address instanceof AddressModel) {
                $address->id = $value['id'] ?? null;
            } else {
                $address = new AddressModel();
            }

            $address->elementId = $element->id;
            $address->siteId = $element->siteId;
            $address->fieldId = $addressField->id;
            $address->countryCode = $value['countryCode'];
            $address->administrativeAreaCode = $value['administrativeAreaCode'] ?? null;
            $address->locality = $value['locality'] ?? null;
            $address->dependentLocality = $value['dependentLocality'] ?? null;
            $address->postalCode = $value['postalCode'] ?? null;
            $address->sortingCode = $value['sortingCode'] ?? null;
            $address->address1 = $value['address1'] ?? null;
            $address->address2 = $value['address2'] ?? null;
        }

        return $address;
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
     * @param FieldInterface   $field
     * @param ElementInterface $element
     * @param bool             $isNew
     *
     * @throws StaleObjectException
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function afterElementSave(FieldInterface $field, ElementInterface $element, bool $isNew)
    {
        $addressService = SproutBaseFields::$app->addressField;

        /** @var Element $element */
        /** @var Field|FormField $field */
        if ($element->duplicateOf !== null) {
            $addressService->duplicateAddress($field, $element->duplicateOf, $element, $isNew);
        } else {
            $addressService->saveAddress($field, $element, $isNew);
        }

        // Reset the field value if this is a new element
        if ($element->duplicateOf || $isNew) {
            $element->setFieldValue($field->handle, null);
        }
    }
}