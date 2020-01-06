<?php

namespace barrelstrength\sproutbasefields\migrations;

use barrelstrength\sproutbasefields\SproutBaseFields;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\Json;
use yii\base\NotSupportedException;

/**
 * m200102_000000_remove_address_field_content_column migration.
 */
class m200102_000000_remove_address_field_content_column extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        $sproutFieldsAddressFieldClass = 'barrelstrength\sproutfields\fields\Address';
        /** @noinspection ClassConstantCanBeUsedInspection */
        $sproutFormsAddressFieldClass = 'barrelstrength\sproutforms\fields\formfields\Address';

        // SPROUT FIELDS

        $addressFieldsTable = '{{%sproutfields_addresses}}';
        $sproutSeoAddressTable = '{{%sproutseo_addresses}}';
        $tempAddressFieldsTable = '{{%sproutfields_addresses_temp}}';
        $oldAddressFieldsTable = '{{%sproutfields_addresses_old}}';

        // Get all Name fields from content table (Craft / Sprout Forms)
        $addressFieldTypes = (new Query())
            ->select(['id', 'handle', 'settings', 'type'])
            ->from([Table::FIELDS])
            ->where(['type' => $sproutFieldsAddressFieldClass])
            ->all();

        // Update every Name Column that matches a blank name JSON string and set it to null
        foreach ($addressFieldTypes as $field) {
            $columnName = 'field_'.$field['handle'];

            // If we don't have an address column in the content table, no need to migrate anything
            if (!$this->db->columnExists(Table::CONTENT, $columnName)) {
                continue;
            }

            if (!$this->db->tableExists($tempAddressFieldsTable)) {
                $this->createTemporaryAddressTable($tempAddressFieldsTable);
            }

            $elementsWithAddressIds = (new Query())
                ->select(['id', 'elementId', $columnName])
                ->from([Table::CONTENT])
                ->where(['not', [$columnName => null]])
                ->all();

            // Insert new Address records with Element IDs to match existing Elements
            foreach ($elementsWithAddressIds as $elementsWithAddressId) {
                $addressId = $elementsWithAddressId[$columnName];

                $address = (new Query())
                    ->select(['*'])
                    ->from([$addressFieldsTable])
                    ->where(['id' => $addressId])
                    ->one();

                if (!$address) {
                    SproutBaseFields::info('Unable to migrate address. Unable to find address with ID: '. $addressId. ' for element '. $elementsWithAddressId['elementId']);
                    continue;
                }

                $address['elementId'] = $elementsWithAddressId['elementId'];
                unset($address['id']);

                $this->insert($tempAddressFieldsTable, $address, false);
            }

            $this->dropColumn(Table::CONTENT, $columnName);
        }

        // The temp table will only get created if address columns exist, so only
        // rename and delete if it exists
        if ($this->db->tableExists($tempAddressFieldsTable)) {
            $this->renameTable($addressFieldsTable, $oldAddressFieldsTable);
            $this->renameTable($tempAddressFieldsTable, $addressFieldsTable);

            $this->dropTableIfExists($tempAddressFieldsTable);
            $this->dropTableIfExists($oldAddressFieldsTable);
            $this->dropTableIfExists($sproutSeoAddressTable);
        }

        if (!$this->db->tableExists('{{%sproutforms_forms}}')) {
            return true;
        }

        // SPROUT FORMS

        $forms = (new Query())
            ->select(['id', 'handle'])
            ->from(['{{%sproutforms_forms}}'])
            ->all();

        $sproutFormsNameFieldTypes = (new Query())
            ->select(['id', 'handle', 'settings', 'type'])
            ->from([Table::FIELDS])
            ->where(['type' => $sproutFormsAddressFieldClass])
            ->all();

        foreach ($forms as $form) {
            $contentTable = 'sproutformscontent_'.$form['handle'];
            if (!$this->db->tableExists($contentTable)) {
                continue;
            }

            foreach ($sproutFormsNameFieldTypes as $field) {
                $columnName = 'field_'.$field['handle'];
                if ($this->db->columnExists($contentTable, $columnName)) {
                    $this->dropColumn($contentTable, $columnName);
                }
            }
        }

        return true;
    }

    public function createTemporaryAddressTable($tempAddressFieldsTable) {
        // Create a fresh address field table
        $this->createTable($tempAddressFieldsTable, [
            'id' => $this->primaryKey(),
            'elementId' => $this->integer(),
            'siteId' => $this->integer(),
            'fieldId' => $this->integer(),
            'countryCode' => $this->string(),
            'administrativeAreaCode' => $this->string(),
            'locality' => $this->string(),
            'dependentLocality' => $this->string(),
            'postalCode' => $this->string(),
            'sortingCode' => $this->string(),
            'address1' => $this->string(),
            'address2' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200102_000000_remove_address_field_content_column cannot be reverted.\n";
        return false;
    }
}
