<?php

namespace barrelstrength\sproutbasefields\migrations;

use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use Craft;
use yii\base\NotSupportedException;
use yii\db\Exception;

/**
 * m200109_000000_update_address_tables migration.
 */
class m200109_000000_update_address_tables extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $oldAddressTableName = '{{%sproutfields_addresses}}';
        $newAddressTableName = '{{%sprout_addresses}}';

        // In some cases, our temp address migration table never got created
        // Make sure we rename the address table to the new name
        if ($this->db->tableExists($oldAddressTableName) && !$this->db->tableExists($newAddressTableName)) {
            $this->renameTable($oldAddressTableName, $newAddressTableName);
        }

        // In some cases, Sprout SEO could migrate the address and not cleanup the table.
        // Make sure we've deleted it.
        $this->dropTableIfExists('{{%sproutseo_addresses}}');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200109_000000_update_address_tables cannot be reverted.\n";
        return false;
    }
}
