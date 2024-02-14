<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CurrencyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $currencyCodes = [
            'GBP' => 'United Kingdom Pound',
            'USD' => 'United States Dollar',
            'EUR' => 'Euro Member Countries',
            'JPY' => 'Japan Yen',
            'AUD' => 'Australia Dollar',
            'BGN' => 'Bulgaria Lev',
            'CAD' => 'Canada Dollar',
            'CNY' => 'China Yuan Renminbi',
            'HUF' => 'Hungary Forint',
            'INR' => 'India Rupee',
            'KRW' => 'Korea (South) Won',
            'NZD' => 'New Zealand Dollar',
            'HKD' => 'Hong Kong Dollar',
            'SGD' => 'Singapore Dollar',
            'NOK' => 'Norway Krone',
            'DKK' => 'Danish Krone',
            'PLN' => 'Poland Zloty',
            'RON' => 'Romania New Leu',
            'RUB' => 'Russia Ruble',
            'SEK' => 'Sweden Krona',
            'CHF' => 'Switzerland Franc',
            'TRY' => 'Turkey Lira',
        ];

        $i = 1;
        foreach ($currencyCodes as $currencyCode => $currencyCountry) {
            $currencyObj = $this->createCurrency($currencyCode);
            $manager->persist($currencyObj);
            $this->addReference('currency_' . $i++, $currencyObj);
        }

        $manager->flush();
    }

    private function createCurrency(string $code): Currency
    {
        $currency = new Currency();
        $currency->setCode($code);

        return $currency;
    }
}
