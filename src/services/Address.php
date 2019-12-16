<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\services;

use barrelstrength\sproutbasefields\models\Address as AddressModel;
use barrelstrength\sproutbasefields\events\OnSaveAddressEvent;
use barrelstrength\sproutbasefields\records\Address as AddressRecord;
use barrelstrength\sproutbasefields\SproutBaseFields;
use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\db\Query;
use Exception;
use InvalidArgumentException;
use Throwable;
use yii\db\StaleObjectException;

class Address extends Component
{
    const EVENT_ON_SAVE_ADDRESS = 'onSaveAddressEvent';

    const DEFAULT_COUNTRY = 'US';
    const DEFAULT_LANGUAGE = 'en';

    /**
     * @param string   $namespace
     * @param int|null $fieldId
     *
     * @return int|null
     * @throws Throwable
     * @throws \yii\db\Exception
     * @throws StaleObjectException
     */
    public function saveAddressByPost($namespace = 'address', int $fieldId = null)
    {
        if (Craft::$app->getRequest()->getBodyParam($namespace) === null) {
            return null;
        }

        $addressInfo = Craft::$app->getRequest()->getBodyParam($namespace);

        if ($fieldId !== null) {
            $addressInfo['fieldId'] = $fieldId;
        }

        $isDelete = $addressInfo['delete'] ?? null;
        $addressId = $addressInfo['id'] ?? null;

        if ($isDelete !== null && $addressId !== null) {
            SproutBaseFields::$app->addressField->deleteAddressById($addressId);

            return null;
        }

        unset($addressInfo['delete']);

        $addressModel = new AddressModel($addressInfo);

        if ($addressModel->validate() == true && $this->saveAddress($addressModel)) {
            return $addressModel->id;
        }

        return null;
    }

    /**
     * @param AddressModel $model
     * @param string       $source
     *
     * @return bool
     * @throws Throwable
     */
    public function saveAddress(AddressModel $model, $source = ''): bool
    {
        if (!empty($model->id)) {
            $record = AddressRecord::findOne($model->id);

            if (!$record) {
                throw new InvalidArgumentException('No Address exists with the ID '.$model->id);
            }
        } else {
            $record = new AddressRecord;
        }

        $attributes = $model->getAttributes();

        // Unset id to avoid postgres not null id error
        unset($attributes['id']);

        $record->setAttributes($attributes, false);

        if (!$model->validate()) {
            return false;
        }

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            $record->save();

            $model->id = $record->id;

            $this->afterSaveAddress($model, $source);

            $transaction->commit();

            return true;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
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
            $query->where(['elementId' => $element->id]);
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
}