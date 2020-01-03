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
 * m200102_000000_update_sproutseo_globals_address migration.
 */
class m200102_000000_update_sproutseo_globals_address extends Migration
{
    /**
     * @return bool
     * @throws Exception
     */
    public function safeUp(): bool
    {
        $sproutSeoGlobalsTable = '{{%sproutseo_globals}}';

        if (!$this->db->tableExists($sproutSeoGlobalsTable)) {
            return true;
        }

        // Make sure we have a sprout_settings table
        $migration = new SproutBaseInstall();
        ob_start();
        $migration->safeUp();
        ob_end_clean();

        $identities = (new Query())
            ->select(['id', 'identity'])
            ->from([$sproutSeoGlobalsTable])
            ->all();

        // Temporarily Save any addresses found in our identity settings to the sprout_settings table
        foreach ($identities as $identity) {
            $identity = Json::decode($identity);
            $addressId = $identity['addressId'] ?? null;

            if (!$addressId || !is_int($addressId)) {
                continue;
            }

            $address = (new Query())
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
                ->where(['id' => $addressId])
                ->one();

            unset($address['id'], $address['elementId'], $address['fieldId']);

            Craft::$app->db->createCommand()->insert('{{%sprout_settings}}', [
                'model' => 'address-fields-migration-sprout-seo-v4.2.9-siteId:'.$address['siteId'],
                'settings' => Json::encode($address)
            ])->execute();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200102_000000_update_sproutseo_globals_address cannot be reverted.\n";
        return false;
    }
}
