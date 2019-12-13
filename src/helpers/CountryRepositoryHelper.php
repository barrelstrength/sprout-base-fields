<?php

namespace barrelstrength\sproutbasefields\helpers;

use CommerceGuys\Addressing\Country\CountryRepository;

class CountryRepositoryHelper extends CountryRepository
{
    /**
     * Helper method to retrieve protected property on parent class
     *
     * @return array
     */
    public function getAvailableLocales(): array
    {
        return $this->availableLocales;
    }
}