<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\base;

use barrelstrength\sproutbasefields\helpers\AddressFieldHelper;

/**
 * Trait AddressFieldTrait
 *
 * @package barrelstrength\sproutbasefields\base
 *
 * @property null|string $settingsHtml
 */
trait AddressFieldTrait
{
    /**
     * Shared methods for managing Field Types between Sprout Forms and Sprout Fields
     *
     * @var AddressFieldHelper $addressFieldHelper
     */
    protected $addressFieldHelper;

    /**
     * @deprecated  Remove in Sprout Forms v4.x and Sprout Fields 4.x.
     * Removed in Sprout Forms v3.6.6 and Sprout Fields v3.4.4 and remains to assist in migrations.
     */
    public $addressHelper;

    /**
     * @var string
     */
    public $defaultLanguage = 'en';

    /**
     * @var string
     */
    public $defaultCountry = 'US';

    /**
     * @var bool
     */
    public $showCountryDropdown = true;

    /**
     * @deprecated No longer in use. Necessary in craft 3.1 migration. Remove in Sprout Forms 4.x and Sprout Fields 4.x
     */
    public $hideCountryDropdown;

    /**
     * @var array
     */
    public $highlightCountries = [];

    /**
     * This will be populated with the addressId if it should be removed from the database
     *
     * @var int
     */
    protected $_deletedAddressId;

    /**
     * @param int $addressId
     */
    public function setDeletedAddressId($addressId) {
        $this->_deletedAddressId = $addressId;
    }

    /**
     * @return int|null
     */
    public function getDeletedAddressId() {
        return $this->_deletedAddressId;
    }
}