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
 * m200105_000000_cleanup_address_tables migration.
 */
class m200105_000000_cleanup_address_tables extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $this->dropTableIfExists('{{%sproutseo_addresses}}');

        // While this table appeared to get deleted in the logs in the prior migration,
        // it still appeared in the database so we also remove it here in another migration
        $this->dropTableIfExists('{{%sproutfields_addresses_temp}}');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200105_000000_cleanup_address_tables cannot be reverted.\n";
        return false;
    }
}
