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
        $sproutSeoAddressesTable = '{{%sproutseo_addresses}}';

        if (!$this->db->tableExists($sproutSeoAddressesTable)) {
            return true;
        }

        // Make sure we have a sprout_settings table
        $migration = new SproutBaseInstall();
        ob_start();
        $migration->safeUp();
        ob_end_clean();

        $identities = (new Query())
            ->select(['id', 'siteId', 'identity'])
            ->from([$sproutSeoGlobalsTable])
            ->all();

        // Temporarily Save any addresses found in our identity settings to the sprout_settings table
        foreach ($identities as $identity) {
            $identity = Json::decode($identity['identity']);
            $addressId = $identity['addressId'] ?? null;

            if (!$addressId) {
                continue;
            }

            $address = (new Query())
                ->select([
                    'id',
                    'countryCode',
                    'administrativeArea',
                    'locality',
                    'dependentLocality',
                    'postalCode',
                    'sortingCode',
                    'address1',
                    'address2'
                ])
                ->from('{{%sproutseo_addresses}}')
                ->where(['id' => $addressId])
                ->one();

            $address['administrativeAreaCode'] = $address['administrativeArea'];
            unset($address['id'], $address['administrativeArea']);

            Craft::$app->db->createCommand()->insert('{{%sprout_settings}}', [
                'model' => 'address-fields-migration-sprout-seo-v4.2.9-siteId:'.$identity['siteId'],
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