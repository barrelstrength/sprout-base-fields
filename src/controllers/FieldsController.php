<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\controllers;

use barrelstrength\sproutbasefields\SproutBaseFields;
use Craft;
use craft\base\Field;
use craft\base\Element;
use craft\web\Controller as BaseController;

use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * @property Field $fieldModel
 */
class FieldsController extends BaseController
{
    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionValidateEmail(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $value = Craft::$app->getRequest()->getParam('value');
        $field = $this->getFieldModel();

        if (!$field) {
            return $this->asJson(['success' => false]);
        }

        $isValid = SproutBaseFields::$app->emailField->validateEmail($value, $field);

        return $this->asJson(['success' => $isValid]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionValidateUrl(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $value = Craft::$app->getRequest()->getParam('value');
        $field = $this->getFieldModel();

        // If we don't find a URL Field, return a new URL Field model
        if (!$field) {
            return $this->asJson(['success' => false]);
        }

        $isValid = SproutBaseFields::$app->urlField->validateUrl($value, $field);

        return $this->asJson(['success' => $isValid]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionPhoneValidate(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $phone = Craft::$app->getRequest()->getParam('phone');
        $country = Craft::$app->getRequest()->getParam('country');

        if (!SproutBaseFields::$app->phoneField->validate($phone, $country)) {
            return $this->asJson([
                'success' => false
            ]);
        }

        return $this->asJson([
            'success' => true
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionRegularExpressionValidate(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $value = Craft::$app->getRequest()->getParam('value');
        $field = $this->getFieldModel();

        $isValid = SproutBaseFields::$app->regularExpressionField->validate($value, $field);

        return $this->asJson(['success' => $isValid]);
    }

    /**
     * @return Field
     */
    protected function getFieldModel(): Field
    {
        $oldFieldContext = Craft::$app->content->fieldContext;
        $fieldContext = Craft::$app->getRequest()->getParam('fieldContext');
        $fieldHandle = Craft::$app->getRequest()->getParam('fieldHandle');

        // Retrieve an Email Field, wherever it may be
        Craft::$app->content->fieldContext = $fieldContext;

        /** @var Field $field */
        $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
        Craft::$app->content->fieldContext = $oldFieldContext;

        return $field;
    }
}
