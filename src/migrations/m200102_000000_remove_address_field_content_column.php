<?php

namespace barrelstrength\sproutbasefields\migrations;

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

        // Get all Name fields from content table (Craft / Sprout Forms)
        $addressFieldTypes = (new Query())
            ->select(['id', 'settings', 'type'])
            ->from([Table::FIELDS])
            ->where(['type' => $sproutFieldsAddressFieldClass])
            ->all();

        // Update every Name Column that matches a blank name JSON string and set it to null
        foreach ($addressFieldTypes as $field) {
            $columnName = 'field_'.$field->handle;
            if ($this->db->columnExists(Table::CONTENT, $columnName)) {
                $this->dropColumn(Table::CONTENT, $columnName);
            }
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
            ->select(['id', 'settings', 'type'])
            ->from([Table::FIELDS])
            ->where(['type' => $sproutFormsAddressFieldClass])
            ->all();

        foreach ($forms as $form) {
            $contentTable = 'sproutformscontent_'.$form->handle;
            if (!$this->db->tableExists($contentTable)) {
                continue;
            }

            foreach ($sproutFormsNameFieldTypes as $field) {
                $columnName = 'field_'.$field->handle;
                if ($this->db->columnExists($contentTable, $columnName)) {
                    $this->dropColumn($contentTable, $columnName);
                }
            }
        }

        return true;
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
