<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\services;

use barrelstrength\sproutbasefields\SproutBaseFields;
use barrelstrength\sproutfields\fields\Url as UrlField;
use craft\base\ElementInterface;
use craft\base\Field;
use yii\base\Component;
use Craft;

/**
 * Class Url
 */
class Url extends Component
{
    /**
     * Validates a phone number against a given mask/pattern
     *
     * @param                  $value
     * @param Field            $field
     * @param ElementInterface $element
     *
     * @return bool|void
     */
    public function validateUrl($value, Field $field, ElementInterface $element)
    {
        $customPattern = $field->customPattern;
        $checkPattern = $field->customPatternToggle;

        if ($customPattern && $checkPattern) {
            // Use backtick as delimiters as they are invalid characters for emails
            $customPattern = '`'.$customPattern.'`';

            if (preg_match($customPattern, $value)) {
                return;
            }
        }

        $path = parse_url($value, PHP_URL_PATH);
        $encodedPath = array_map('urlencode', explode('/', $path));
        $url = str_replace($path, implode('/', $encodedPath), $value);

        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return;
        }

        $message = Craft::t('sprout-base-fields', $field->name.' must be a valid URL.');

        if ($field->customPatternToggle && $field->customPatternErrorMessage) {
            $message = Craft::t('sprout-base-fields', $field->customPatternErrorMessage);
        }

        $element->addError($field->handle, $message);
    }

    /**
     * Return error message
     *
     * @param $fieldName
     * @param $field
     *
     * @return string
     */
    public function getErrorMessage($fieldName, $field): string
    {

    }
}
