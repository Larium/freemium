<?php

declare(strict_types=1);

namespace Freemium\Domain;

final class Currency
{
    public static function supportedCurrencies(): array
    {
        return array_keys(self::$currencies);
    }

    public static function getFraction(string $currency): int
    {
        if (!array_key_exists($currency, self::$currencies)) {
            return 2;
        }

        return self::$currencies[$currency]['fraction'];
    }

    private static array $currencies = [
        'AED' => ['fraction' => 2], // UAE Dirham
        'AFN' => ['fraction' => 2], // Afghan Afghani
        'ALL' => ['fraction' => 2], // Albanian Lek
        'AMD' => ['fraction' => 2], // Armenian Dram
        'ANG' => ['fraction' => 2], // Netherlands Antillean Guilder
        'AOA' => ['fraction' => 2], // Angolan Kwanza
        'ARS' => ['fraction' => 2], // Argentine Peso
        'AUD' => ['fraction' => 2], // Australian Dollar
        'AWG' => ['fraction' => 2], // Aruban Florin
        'AZN' => ['fraction' => 2], // Azerbaijani Manat
        'BAM' => ['fraction' => 2], // Bosnia-Herzegovina Convertible Mark
        'BBD' => ['fraction' => 2], // Barbados Dollar
        'BDT' => ['fraction' => 2], // Bangladeshi Taka
        'BGN' => ['fraction' => 2], // Bulgarian Lev
        'BHD' => ['fraction' => 3], // Bahraini Dinar
        'BIF' => ['fraction' => 0], // Burundian Franc
        'BMD' => ['fraction' => 2], // Bermudian Dollar
        'BND' => ['fraction' => 2], // Brunei Dollar
        'BOB' => ['fraction' => 2], // Bolivian Boliviano
        'BRL' => ['fraction' => 2], // Brazilian Real
        'BSD' => ['fraction' => 2], // Bahamian Dollar
        'BTN' => ['fraction' => 2], // Bhutanese Ngultrum
        'BWP' => ['fraction' => 2], // Botswana Pula
        'BYN' => ['fraction' => 2], // Belarusian Ruble
        'BZD' => ['fraction' => 2], // Belize Dollar
        'CAD' => ['fraction' => 2], // Canadian Dollar
        'CDF' => ['fraction' => 2], // Congolese Franc
        'CHF' => ['fraction' => 2], // Swiss Franc
        'CLP' => ['fraction' => 0], // Chilean Peso
        'CNY' => ['fraction' => 2], // Chinese Yuan
        'COP' => ['fraction' => 2], // Colombian Peso
        'CRC' => ['fraction' => 2], // Costa Rican Colón
        'CUC' => ['fraction' => 2], // Cuban Convertible Peso
        'CUP' => ['fraction' => 2], // Cuban Peso
        'CVE' => ['fraction' => 2], // Cape Verdean Escudo
        'CZK' => ['fraction' => 2], // Czech Koruna
        'DJF' => ['fraction' => 0], // Djiboutian Franc
        'DKK' => ['fraction' => 2], // Danish Krone
        'DOP' => ['fraction' => 2], // Dominican Peso
        'DZD' => ['fraction' => 2], // Algerian Dinar
        'EGP' => ['fraction' => 2], // Egyptian Pound
        'ERN' => ['fraction' => 2], // Eritrean Nakfa
        'ETB' => ['fraction' => 2], // Ethiopian Birr
        'EUR' => ['fraction' => 2], // Euro
        'FJD' => ['fraction' => 2], // Fijian Dollar
        'FKP' => ['fraction' => 2], // Falkland Islands Pound
        'GBP' => ['fraction' => 2], // British Pound
        'GEL' => ['fraction' => 2], // Georgian Lari
        'GHS' => ['fraction' => 2], // Ghanaian Cedi
        'GIP' => ['fraction' => 2], // Gibraltar Pound
        'GMD' => ['fraction' => 2], // Gambian Dalasi
        'GNF' => ['fraction' => 0], // Guinean Franc
        'GTQ' => ['fraction' => 2], // Guatemalan Quetzal
        'GYD' => ['fraction' => 2], // Guyanese Dollar
        'HKD' => ['fraction' => 2], // Hong Kong Dollar
        'HNL' => ['fraction' => 2], // Honduran Lempira
        'HRK' => ['fraction' => 2], // Croatian Kuna
        'HTG' => ['fraction' => 2], // Haitian Gourde
        'HUF' => ['fraction' => 2], // Hungarian Forint
        'IDR' => ['fraction' => 2], // Indonesian Rupiah
        'ILS' => ['fraction' => 2], // Israeli New Shekel
        'INR' => ['fraction' => 2], // Indian Rupee
        'IQD' => ['fraction' => 3], // Iraqi Dinar
        'IRR' => ['fraction' => 2], // Iranian Rial
        'ISK' => ['fraction' => 0], // Icelandic Krona
        'JMD' => ['fraction' => 2], // Jamaican Dollar
        'JOD' => ['fraction' => 3], // Jordanian Dinar
        'JPY' => ['fraction' => 0], // Japanese Yen
        'KES' => ['fraction' => 2], // Kenyan Shilling
        'KGS' => ['fraction' => 2], // Kyrgyzstani Som
        'KHR' => ['fraction' => 2], // Cambodian Riel
        'KMF' => ['fraction' => 0], // Comorian Franc
        'KPW' => ['fraction' => 2], // North Korean Won
        'KRW' => ['fraction' => 0], // South Korean Won
        'KWD' => ['fraction' => 3], // Kuwaiti Dinar
        'KYD' => ['fraction' => 2], // Cayman Islands Dollar
        'KZT' => ['fraction' => 2], // Kazakhstani Tenge
        'LAK' => ['fraction' => 2], // Lao Kip
        'LBP' => ['fraction' => 2], // Lebanese Pound
        'LKR' => ['fraction' => 2], // Sri Lankan Rupee
        'LRD' => ['fraction' => 2], // Liberian Dollar
        'LSL' => ['fraction' => 2], // Lesotho Loti
        'LYD' => ['fraction' => 3], // Libyan Dinar
        'MAD' => ['fraction' => 2], // Moroccan Dirham
        'MDL' => ['fraction' => 2], // Moldovan Leu
        'MGA' => ['fraction' => 2], // Malagasy Ariary
        'MKD' => ['fraction' => 2], // Macedonian Denar
        'MMK' => ['fraction' => 2], // Myanmar Kyat
        'MNT' => ['fraction' => 2], // Mongolian Tugrik
        'MOP' => ['fraction' => 2], // Macanese Pataca
        'MRU' => ['fraction' => 2], // Mauritanian Ouguiya
        'MUR' => ['fraction' => 2], // Mauritian Rupee
        'MVR' => ['fraction' => 2], // Maldivian Rufiyaa
        'MWK' => ['fraction' => 2], // Malawian Kwacha
        'MXN' => ['fraction' => 2], // Mexican Peso
        'MYR' => ['fraction' => 2], // Malaysian Ringgit
        'MZN' => ['fraction' => 2], // Mozambican Metical
        'NAD' => ['fraction' => 2], // Namibian Dollar
        'NGN' => ['fraction' => 2], // Nigerian Naira
        'NIO' => ['fraction' => 2], // Nicaraguan Córdoba
        'NOK' => ['fraction' => 2], // Norwegian Krone
        'NPR' => ['fraction' => 2], // Nepalese Rupee
        'NZD' => ['fraction' => 2], // New Zealand Dollar
        'OMR' => ['fraction' => 3], // Omani Rial
        'PAB' => ['fraction' => 2], // Panamanian Balboa
        'PEN' => ['fraction' => 2], // Peruvian Sol
        'PGK' => ['fraction' => 2], // Papua New Guinean Kina
        'PHP' => ['fraction' => 2], // Philippine Peso
        'PKR' => ['fraction' => 2], // Pakistani Rupee
        'PLN' => ['fraction' => 2], // Polish Zloty
        'PYG' => ['fraction' => 0], // Paraguayan Guarani
        'QAR' => ['fraction' => 2], // Qatari Rial
        'RON' => ['fraction' => 2], // Romanian Leu
        'RSD' => ['fraction' => 2], // Serbian Dinar
        'RUB' => ['fraction' => 2], // Russian Ruble
        'RWF' => ['fraction' => 0], // Rwandan Franc
        'SAR' => ['fraction' => 2], // Saudi Riyal
        'SBD' => ['fraction' => 2], // Solomon Islands Dollar
        'SCR' => ['fraction' => 2], // Seychellois Rupee
        'SDG' => ['fraction' => 2], // Sudanese Pound
        'SEK' => ['fraction' => 2], // Swedish Krona
        'SGD' => ['fraction' => 2], // Singapore Dollar
        'SHP' => ['fraction' => 2], // Saint Helena Pound
        'SLL' => ['fraction' => 2], // Sierra Leonean Leone
        'SOS' => ['fraction' => 2], // Somali Shilling
        'SRD' => ['fraction' => 2], // Surinamese Dollar
        'STN' => ['fraction' => 2], // São Tomé and Príncipe Dobra
        'SYP' => ['fraction' => 2], // Syrian Pound
        'SZL' => ['fraction' => 2], // Swazi Lilangeni
        'THB' => ['fraction' => 2], // Thai Baht
        'TJS' => ['fraction' => 2], // Tajikistani Somoni
        'TMT' => ['fraction' => 2], // Turkmenistan Manat
        'TND' => ['fraction' => 3], // Tunisian Dinar
        'TOP' => ['fraction' => 2], // Tongan Paʻanga
        'TRY' => ['fraction' => 2], // Turkish Lira
        'TTD' => ['fraction' => 2], // Trinidad and Tobago Dollar
        'TWD' => ['fraction' => 2], // New Taiwan Dollar
        'TZS' => ['fraction' => 2], // Tanzanian Shilling
        'UAH' => ['fraction' => 2], // Ukrainian Hryvnia
        'UGX' => ['fraction' => 0], // Ugandan Shilling
        'USD' => ['fraction' => 2], // United States Dollar
        'UYU' => ['fraction' => 2], // Uruguayan Peso
        'UZS' => ['fraction' => 2], // Uzbekistan Som
        'VES' => ['fraction' => 2], // Venezuelan Bolívar Soberano
        'VND' => ['fraction' => 0], // Vietnamese Dong
        'VUV' => ['fraction' => 0], // Vanuatu Vatu
        'WST' => ['fraction' => 2], // Samoan Tala
        'XAF' => ['fraction' => 0], // Central African CFA Franc
        'XCD' => ['fraction' => 2], // East Caribbean Dollar
        'XOF' => ['fraction' => 0], // West African CFA Franc
        'XPF' => ['fraction' => 0], // CFP Franc
        'YER' => ['fraction' => 2], // Yemeni Rial
        'ZAR' => ['fraction' => 2], // South African Rand
        'ZMW' => ['fraction' => 2], // Zambian Kwacha
    ];
}
