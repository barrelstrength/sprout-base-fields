<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasefields\migrations;

use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use craft\db\Migration;
use craft\db\Query;

/**
 * m190313_000000_fix_non_abbreviation_administrative_codes migration.
 */
class m190313_000000_fix_non_abbreviation_administrative_codes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableName = '{{%sproutfields_addresses}}';

        $addresses = (new Query())
            ->select('*')
            ->from($tableName)
            ->all();

        $subdivisionRepository = new SubdivisionRepository();

        foreach ($addresses as $address) {
            $states = $subdivisionRepository->getAll([$address['countryCode']]);

            foreach ($states as $state) {
                if ($state->getName() == $address['administrativeAreaCode']){
                    $stateCode = $state->getCode();

                    $this->update($tableName, [
                        'administrativeAreaCode' => $stateCode
                    ], [
                        'id' => $address['id']
                    ], [], false);
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
        echo "m190313_000000_fix_non_abbreviation_administrative_codes cannot be reverted.\n";
        return false;
    }
}
