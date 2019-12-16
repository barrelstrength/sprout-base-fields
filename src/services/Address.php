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
use InvalidArgumentException;

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
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
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
     * @throws \Exception
     */
    public function saveAddress(AddressModel $model, $source = ''): bool
    {
        $result = false;

        $record = new AddressRecord;

        if (!empty($model->id)) {
            $record = AddressRecord::findOne($model->id);

            if (!$record) {
                throw new InvalidArgumentException('No Address exists with the ID '.$model->id);
            }
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
     * @return AddressModel
     */
    public function getAddressById($id): AddressModel
    {
        $model = new AddressModel();

        if ($record = AddressRecord::findOne($id)) {
            $model->setAttributes($record->getAttributes(), false);
        }

        return $model;
    }

    public function getAddress($elementId, $siteId, $fieldId): AddressModel
    {
        $model = new AddressModel();

        if ($record = AddressRecord::findOne([
            'elementId' => $elementId,
            'siteId' => $siteId,
            'fieldId' => $fieldId
        ])) {
            $model->setAttributes($record->getAttributes(), false);
        }

        return $model;
    }


    /**
     * @param null $id
     *
     * @return bool|false|int
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteAddressById($id = null)
    {
        $record = AddressRecord::findOne($id);
        $result = false;

        if ($record) {
            $result = $record->delete();
        }

        return $result;
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