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
use craft\helpers\ArrayHelper;
use craft\helpers\ElementHelper;
use craft\models\Site;
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
        } catch (\Throwable $e) {
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
}