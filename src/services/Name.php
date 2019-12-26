<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\services;

use barrelstrength\sproutbasefields\models\Name as NameModel;
use barrelstrength\sproutbasefields\SproutBaseFields;
use craft\base\Component;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\helpers\Json;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @property string $settingsHtml
 */
class Name extends Component
{
    /**
     * @param $value
     *
     * @return NameModel|null
     */
    public function normalizeValue($value)
    {
        $nameModel = new NameModel();

        // String value when retrieved from db
        if (is_string($value)) {
            $nameArray = Json::decode($value);
            $nameModel->setAttributes($nameArray, false);
            return $nameModel;
        }

        // Array value from post data
        if (is_array($value) && isset($value['name'])) {

            $nameModel->setAttributes($value['name'], false);

            if ($fullNameShort = $value['name']['fullNameShort'] ?? null) {
                $nameArray = explode(' ', trim($fullNameShort));

                $nameModel->firstName = $nameArray[0] ?? $fullNameShort;
                unset($nameArray[0]);

                $nameModel->lastName = implode(' ', $nameArray);
            }
            return $nameModel;
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return NameModel|bool|string
     */
    public function serializeValue($value)
    {
        /** @var NameModel $value */
        if ($value === null) {
            return false;
        }

        // Submitting an Element to be saved
        if (is_object($value) && get_class($value) == NameModel::class) {
            return Json::encode($value->getAttributes());
        }

        return $value;
    }

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
            'sprout-base-fields/_components/fields/formfields/name/settings',
            [
                'field' => $field,
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
        // @todo - can we update the Quick Entry widget to expect null?
        $elementId = $element->id ?? false;

        $rendered = Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/name/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'id' => $inputId,
                'name' => $name,
                'field' => $field,
                'value' => $value,
                'elementId' => $elementId,
                'fieldContext' => $fieldContext
            ]);

        return TemplateHelper::raw($rendered);
    }
}