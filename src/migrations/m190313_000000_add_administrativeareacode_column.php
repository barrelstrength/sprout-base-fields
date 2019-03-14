<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasefields\migrations;

use barrelstrength\sproutbaseemail\migrations\m190212_000003_update_email_template_id;
use craft\db\Migration;

/**
 * m190313_000000_add_administrativeareacode_column migration.
 */
class m190313_000000_add_administrativeareacode_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableName = '{{%sproutfields_addresses}}';

        if ($this->db->columnExists($tableName, 'administrativeArea')) {
            $this->renameColumn($tableName, 'administrativeArea', 'administrativeAreaCode');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190313_000000_add_administrativeareacode_column cannot be reverted.\n";
        return false;
    }
}
