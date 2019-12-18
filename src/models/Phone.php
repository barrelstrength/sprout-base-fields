<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\models;

use craft\base\Model;
use craft\helpers\Json;
use Exception;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Class Name
 *
 * @property string $asJson
 */
class Phone extends Model
{
    /**
     * @var string
     */
    public $phone;
    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $international;

    /**
     * @var string
     */
    public $national;

    /**
     * @var string
     */
    public $E164;

    /**
     * @var string
     */
    public $RFC3966;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->international;
    }

    /**
     * Phone constructor.
     *
     * @param string $phone
     * @param string $country
     */
    public function __construct($phone, $country)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $this->phone = $phone;
        $this->country = $country;

        try {
            $phoneNumber = $phoneUtil->parse($phone, $country);
            $code = $phoneUtil->getCountryCodeForRegion($country);
            $this->code = $code;
            $this->international = $phoneUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
            $this->national = $phoneUtil->format($phoneNumber, PhoneNumberFormat::NATIONAL);
            $this->E164 = $phoneUtil->format($phoneNumber, PhoneNumberFormat::E164);
            $this->RFC3966 = $phoneUtil->format($phoneNumber, PhoneNumberFormat::RFC3966);
        } catch (Exception $e) {
            // let's continue
        }

        parent::__construct();
    }

    public function getAsJson(): string
    {
        return Json::encode([
            'country' => $this->country,
            'phone' => $this->phone
        ]);
    }
}
