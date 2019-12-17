<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\controllers;

use barrelstrength\sproutbasefields\services\AddressFormatter;
use barrelstrength\sproutbasefields\models\Address as AddressModel;
use barrelstrength\sproutbasefields\records\Address as AddressRecord;
use barrelstrength\sproutbasefields\SproutBaseFields;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\helpers\Json;
use craft\web\Controller;
use Craft;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class AddressController extends Controller
{
    /**
     * @var array
     */
    protected $allowAnonymous = [
        'update-address-form-html'
    ];

    /**
     * Updates the Address Form Input HTML
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws MissingComponentException
     */
    public function actionUpdateAddressFormHtml(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressFormatter = SproutBaseFields::$app->addressFormatter;

        $addressId = Craft::$app->getRequest()->getBodyParam('addressId');
        $fieldId = Craft::$app->getRequest()->getBodyParam('fieldId');
        $countryCode = Craft::$app->getRequest()->getBodyParam('countryCode');
        $namespace = Craft::$app->getRequest()->getBodyParam('namespace') ?? 'address';
        $overrideTemplatePaths = Craft::$app->getRequest()->getBodyParam('overrideTemplatePaths', false);

        $oldTemplatePath = Craft::$app->getView()->getTemplatesPath();

        if ($overrideTemplatePaths) {
            $sproutFormsTemplatePath = Craft::$app->getSession()->get('sproutforms-templatepath-fields');
            Craft::$app->getView()->setTemplatesPath($sproutFormsTemplatePath);

            // Set the base path to blank to enable Sprout Forms and Template Overrides
            $addressFormatter->setBaseAddressFieldPath('');
        }

        $addressModel = new AddressModel();
        $addressModel->id = $addressId;
        $addressModel->fieldId = $fieldId;

        $addressFormatter->setNamespace($namespace);
        $addressFormatter->setCountryCode($countryCode);
        $addressFormatter->setAddressModel($addressModel);

        $addressFormHtml = $addressFormatter->getAddressFormHtml();

        if ($overrideTemplatePaths) {
            // Set the base path to blank to enable Sprout Forms and Template Overrides
            $addressFormatter->setBaseAddressFieldPath('');

            // Set our template path back to what it was before our ajax request
            Craft::$app->getView()->setTemplatesPath($oldTemplatePath);
        }

        return $this->asJson([
            'html' => $addressFormHtml,
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function actionGetAddressFormFieldsHtml(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressFormatter = SproutBaseFields::$app->addressFormatter;
        $addressId = null;

        $addressModel = new AddressModel();

        if (Craft::$app->getRequest()->getBodyParam('addressId') != null) {
            $addressId = Craft::$app->getRequest()->getBodyParam('addressId');
            $addressModel = SproutBaseFields::$app->addressField->getAddressById($addressId);
        } elseif (Craft::$app->getRequest()->getBodyParam('defaultCountryCode') != null) {
            $defaultCountryCode = Craft::$app->getRequest()->getBodyParam('defaultCountryCode');
            $addressModel->countryCode = $defaultCountryCode;
        }

        $addressDisplayHtml = $addressFormatter->getAddressDisplayHtml($addressModel);

        if ($addressId == null) {
            $addressDisplayHtml = '';
        }

        $countryCode = $addressModel->countryCode;

        $namespace = Craft::$app->getRequest()->getBodyParam('namespace') ?? 'address';

        $addressFormatter->setNamespace($namespace);
        $addressFormatter->setCountryCode($countryCode);
        $addressFormatter->setAddressModel($addressModel);

        $showCountryDropdown = Craft::$app->getRequest()->getBodyParam('showCountryDropdown') !== null;

        $countryCodeHtml = $addressFormatter->getCountryInputHtml($showCountryDropdown);
        $addressFormHtml = $addressFormatter->getAddressFormHtml();

        return $this->asJson([
            'html' => $addressDisplayHtml,
            'countryCodeHtml' => $countryCodeHtml,
            'addressFormHtml' => $addressFormHtml,
            'countryCode' => $countryCode
        ]);
    }

    /**
     * Get an address
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionGetAddressDisplayHtml(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressFormatter = SproutBaseFields::$app->addressFormatter;

        $formValues = Craft::$app->getRequest()->getBodyParam('formValues');
        $namespace = Craft::$app->getRequest()->getBodyParam('namespace') ?? 'address';

        $addressId = $formValues['id'] ?? null;
        $addressModel = new AddressModel($formValues);
        $addressModel->id = $addressId;

        if (!$addressModel->validate()) {
            return $this->asJson([
                'result' => false,
                'errors' => $addressModel->getErrors()
            ]);
        }

        $addressDisplayHtml = $addressFormatter->getAddressDisplayHtml($addressModel);
        $countryCode = $addressModel->countryCode;

        $addressFormatter->setNamespace($namespace);

        if ($addressModel->fieldId) {
            $field = Craft::$app->fields->getFieldById($addressModel->fieldId);

            if (isset($field->highlightCountries) && count($field->highlightCountries)) {
                $addressFormatter->setHighlightCountries($field->highlightCountries);
            }
        }

        $addressFormatter->setCountryCode($countryCode);
        $addressFormatter->setAddressModel($addressModel);

        $countryCodeHtml = $addressFormatter->getCountryInputHtml();
        $addressFormHtml = $addressFormatter->getAddressFormHtml();

        return $this->asJson([
            'result' => true,
            'html' => $addressDisplayHtml,
            'countryCodeHtml' => $countryCodeHtml,
            'addressFormHtml' => $addressFormHtml,
            'countryCode' => $countryCode
        ]);
    }

    /**
     * Delete an address
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionDeleteAddress(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressId = null;
        $addressModel = null;

        if (Craft::$app->getRequest()->getBodyParam('addressId') != null) {
            $addressId = Craft::$app->getRequest()->getBodyParam('addressId');
            $addressModel = SproutBaseFields::$app->addressField->getAddressById($addressId);
        }

        $result = [
            'result' => true,
            'errors' => []
        ];

        try {
            $response = false;

            if ($addressModel->id !== null && $addressModel->id) {
                $addressRecord = new AddressRecord();
                $response = $addressRecord->deleteByPk($addressModel->id);
            }

            $globals = (new Query())
                ->select('*')
                ->from(['{{%sproutseo_globals}}'])
                ->one();

            if ($globals && $response) {
                $identity = $globals['identity'];
                $identity = Json::decode($identity, true);

                if ($identity['addressId'] != null) {
                    $identity['addressId'] = '';
                    $globals['identity'] = Json::encode($identity);

                    Craft::$app->db->createCommand()->update('{{%sproutseo_globals}}',
                        $globals,
                        'id=:id',
                        [':id' => 1]
                    )->execute();
                }
            }
        } catch (Exception $e) {
            $result['result'] = false;
            $result['errors'] = $e->getMessage();
        }

        return $this->asJson($result);
    }

    /**
     * Returns the Geo Coordinates for an Address via the Google Maps service
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionQueryAddressCoordinatesFromGoogleMaps(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressInfo = null;

        if (Craft::$app->getRequest()->getBodyParam('addressInfo') != null) {
            $addressInfo = Craft::$app->getRequest()->getBodyParam('addressInfo');
        }

        $result = [
            'result' => false,
            'errors' => []
        ];

        try {
            $data = [];

            if ($addressInfo) {
                $addressInfo = str_replace('\n', ' ', $addressInfo);

                // Get JSON results from this request
                $geo = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($addressInfo).'&sensor=false');

                // Convert the JSON to an array
                $geo = Json::decode($geo, true);

                if ($geo['status'] === 'OK') {
                    $data['latitude'] = $geo['results'][0]['geometry']['location']['lat'];
                    $data['longitude'] = $geo['results'][0]['geometry']['location']['lng'];

                    $result = [
                        'result' => true,
                        'errors' => [],
                        'geo' => $data
                    ];
                }
            }
        } catch (\Exception $e) {
            $result['result'] = false;
            $result['errors'] = $e->getMessage();
        }

        return $this->asJson($result);
    }
}

