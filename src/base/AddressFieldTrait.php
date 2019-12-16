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
     * @var AddressFieldHelper $addressFieldHelper
     */
    public $addressFieldHelper;

    /**
     * @var string
     */
    public $defaultLanguage;

    /**
     * @var string
     */
    public $defaultCountry;

    /**
     * @var bool
     */
    public $showCountryDropdown = true;

    /**
     * @deprecated No longer in user. Necessary in craft 3.1 migration
     */
    public $hideCountryDropdown;

    /**
     * @var array
     */
    public $highlightCountries = [];
}