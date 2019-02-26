<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasefields\migrations;

use barrelstrength\sproutbaseemail\migrations\m190212_000003_update_email_template_id;
use craft\db\Migration;

/**
 * m190226_000000_add_address_table migration.
 */
class m190226_000000_add_address_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableName = '{{%sproutfields_addresses}}';

        $response = $this->getDb()->tableExists($tableName);

        if ($response == false) {
            $this->createTable($tableName, [
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

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190226_000000_add_address_table cannot be reverted.\n";
        return false;
    }
}
