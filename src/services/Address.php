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
     * @param AddressModel     $address
     * @param ElementInterface $element
     *
     * @param bool             $isNew
     *
     * @return bool
     * @throws Throwable
     */
    public function saveAddress(AddressModel $address, ElementInterface $element, bool $isNew): bool
    {
        $record = AddressRecord::findOne([
            'elementId' => $element->id,
            'siteId' => $element->siteId,
            'fieldId' => $address->fieldId
        ]);

        if (!$record) {
            $record = new AddressRecord();
        }

        if ($isNew) {
            $record->id = null;
        }

        $record->elementId = $element->id;
        $record->siteId = $element->siteId;
        $record->fieldId = $address->fieldId;
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