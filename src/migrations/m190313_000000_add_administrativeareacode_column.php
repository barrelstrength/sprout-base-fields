<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasefields\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m190313_000000_add_administrativeareacode_column migration.
 */
class m190313_000000_add_administrativeareacode_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
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
