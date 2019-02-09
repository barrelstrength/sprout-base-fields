<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\web\assets\address;

use craft\web\AssetBundle;
use barrelstrength\sproutbase\web\assets\cp\CpAsset;

class AddressFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbasefields/web/assets/address/dist';

        $this->depends = [
            CpAsset::class
        ];

        $this->css = [
            'css/addressfield.css'
        ];

        $this->js = [
            'js/AddressBox.js',
            'js/EditAddressModal.js'
        ];

        parent::init();
    }
}