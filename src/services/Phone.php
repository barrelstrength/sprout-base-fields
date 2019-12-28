<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\services;

use barrelstrength\sproutbasefields\models\Phone as PhoneModel;
use CommerceGuys\Addressing\Country\CountryRepository;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\helpers\Json;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Component;
use Craft;

/**
 * Class PhoneService
 *
 * @property array $countries
 */
class Phone extends Component
{
    /**
     * @param FieldInterface $field
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml(FieldInterface $field): string
    {
        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/phone/settings',
            [
                'field' => $field,
            ]
        );
    }

    /**
     * @param FieldInterface        $field
     * @param                       $value
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getInputHtml(FieldInterface $field, $value): string
    {
        /** @var Field $field */
        $name = $field->handle;
        $countryId = Craft::$app->getView()->formatInputId($name.'-country');
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);
        $namespaceCountryId = Craft::$app->getView()->namespaceInputId($countryId);
        $countries = $this->getCountries();

        $country = $value['country'] ?? $field->country;
        $val = $value['phone'] ?? null;

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/phone/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'namespaceCountryId' => $namespaceCountryId,
                'id' => $inputId,
                'countryId' => $countryId,
                'name' => $field->handle,
                'value' => $val,
                'placeholder' => $field->placeholder,
                'countries' => $countries,
                'country' => $country,
                'limitToSingleCountry' => $field->limitToSingleCountry
            ]
        );
    }

    /**
     * @param FieldInterface        $field
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return PhoneModel|null
     */
    public function normalizeValue(FieldInterface $field, $value, ElementInterface $element = null)
    {
        $phoneInfo = [];

        /** @var Field $field */
        if (is_array($value) && $element) {
            $namespace = $element->getFieldParamNamespace();
            $namespace .= '.'.$field->handle;
            $phoneInfo = Craft::$app->getRequest()->getBodyParam($namespace);
            // bad phone or empty phone
        }

        if (is_string($value)) {
            $phoneInfo = Json::decode($value);
        }

        if (!isset($phoneInfo['phone'], $phoneInfo['country'])) {
            return $value;
        }

        return new PhoneModel($phoneInfo['phone'], $phoneInfo['country']);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function serializeValue($value): string
    {
        if ($value instanceof PhoneModel) {
            return $value->getAsJson();
        }

        return $value;
    }

    /**
     * Validates a phone number
     *
     * @param $value
     *
     * @return bool
     */
    public function validate($value): bool
    {
        $phone = $value['phone'] ?? null;
        $country = $value['country'] ?? Address::DEFAULT_COUNTRY;

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $swissNumberProto = $phoneUtil->parse($phone, $country);
            return $phoneUtil->isValidNumber($swissNumberProto);
        } catch (NumberParseException $e) {
            return false;
        }
    }

    /**
     * Return error message
     *
     * @param $field
     * @param $country
     *
     * @return string
     */
    public function getErrorMessage($field, $country = null): string
    {
        // Change empty condition to show default message when toggle settings is unchecked
        if ($field->customPatternErrorMessage) {
            return Craft::t('sprout-base-fields', $field->customPatternErrorMessage);
        }

        $message = Craft::t('sprout-base-fields', '{fieldName} is invalid.');

        if (!$country) {
            return $message;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        $exampleNumber = $phoneUtil->getExampleNumber($country);
        $exampleNationalNumber = $phoneUtil->format($exampleNumber, PhoneNumberFormat::NATIONAL);

        return Craft::t('sprout-base-fields', $message.' Example format: {exampleNumber}', [
            'fieldName' => $field->name,
            'exampleNumber' => $exampleNationalNumber
        ]);
    }

    /**
     * @return array
     */
    public function getCountries(): array
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $regions = $phoneUtil->getSupportedRegions();
        $countries = [];

        foreach ($regions as $countryCode) {
            $code = $phoneUtil->getCountryCodeForRegion($countryCode);
            $countryRepository = new CountryRepository();
            $country = $countryRepository->get($countryCode);

            if ($country) {
                $countries[$countryCode] = $country->getName().' +'.$code;
            }
        }

        asort($countries);

        return $countries;
    }
}
