<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\records;

use craft\db\ActiveRecord;

/**
 * Class Address
 *
 * @property $id
 * @property $elementId
 * @property $siteId
 * @property $fieldId
 * @property $administrativeAreaCode
 * @property $locality
 * @property $dependentLocality
 * @property $postalCode
 * @property $sortingCode
 * @property $address1
 * @property $address2
 * @property $dateCreated
 * @property $dateUpdated
 * @property $uid
 *
 * @package barrelstrength\sproutbasefields\records
 */
class Address extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutfields_addresses}}';
    }
}
