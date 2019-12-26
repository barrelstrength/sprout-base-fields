<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\services;

use barrelstrength\sproutbasefields\SproutBaseFields;
use barrelstrength\sproutfields\fields\Email as EmailField;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Component;
use craft\db\Query;
use craft\db\Table;

/**
 * Class EmailService
 */
class Email extends Component
{
    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/email/settings',
            [
                'field' => $this,
            ]);
    }

    /**
     * @param FieldInterface        $field
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getInputHtml(FieldInterface $field, $value, ElementInterface $element = null): string
    {
        /** @var Field $field */
        $name = $field->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $fieldContext = SproutBaseFields::$app->utilities->getFieldContext($field, $element);

        // Set this to false for Quick Entry Dashboard Widget
        $elementId = ($element != null) ? $element->id : false;

        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/email/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'id' => $inputId,
                'name' => $name,
                'value' => $value,
                'elementId' => $elementId,
                'fieldContext' => $fieldContext,
                'placeholder' => $field->placeholder
            ]);
    }

    public function validate($value, FieldInterface $field, ElementInterface $element)
    {
        /** @var Field $field */
        $customPattern = $field->customPattern;
        $checkPattern = $field->customPatternToggle;

        if (!$this->validateEmailAddress($value, $customPattern, $checkPattern)) {
            $element->addError($field->handle, $this->getErrorMessage($field));
        }

        $uniqueEmail = $field->uniqueEmail;

        if ($uniqueEmail && !SproutBaseFields::$app->emailField->validateUniqueEmailAddress($value, $element, $field)) {
            $message = Craft::t('sprout-base-fields', $field->name.' must be a unique email.');
            $element->addError($field->handle, $message);
        }
    }

    /**
     * Validates an email address or email custom pattern
     *
     * @param $value         string current email to validate
     * @param $customPattern string regular expression
     * @param $checkPattern  bool
     *
     * @return bool
     */
    public function validateEmailAddress($value, $customPattern, $checkPattern = false): bool
    {
        if ($checkPattern) {
            // Use backtick as delimiters as they are invalid characters for emails
            $customPattern = '`'.$customPattern.'`';

            if (preg_match($customPattern, $value)) {
                return true;
            }
        } else if (!filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return true;
        }

        return false;
    }

    /**
     * Validates that an email address is unique to a particular field type
     *
     * @param $value
     * @param $element
     * @param $field
     *
     * @return bool
     */
    public function validateUniqueEmailAddress($value, $element, $field): bool
    {
        $fieldHandle = $element->fieldColumnPrefix.$field->handle;
        $contentTable = $element->contentTable;

        $query = (new Query())
            ->select($fieldHandle)
            ->from($contentTable)
            ->innerJoin(Table::ELEMENTS.' elements', '[[elements.id]] = '.$contentTable.'.`elementId`')
            ->where([$fieldHandle => $value])
            ->andWhere(['elements.dateDeleted' => null]);

        if (is_numeric($element->id)) {
            // Exclude current elementId from our results
            $query->andWhere(['not in', 'elementId', $element->id]);
        }

        $emailExists = $query->scalar();

        if ($emailExists) {
            return false;
        }

        return true;
    }

    /**
     * @param FieldInterface $field
     *
     * @return string
     */
    public function getErrorMessage(FieldInterface $field): string
    {
        /** @var Field $field */
        if ($field->customPatternToggle && $field->customPatternErrorMessage) {
            return Craft::t('sprout-base-fields', $field->customPatternErrorMessage);
        }

        return Craft::t('sprout-base-fields', $field->name.' must be a valid email.');;
}
}
