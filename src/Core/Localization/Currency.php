<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\PrestaShop\Core\Localization;

use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;

/**
 * The Currency class is used to feed callers with currency-related data
 *
 * For instance, the LocaleRepository uses currency data to build Price specifications
 */
class Currency implements CurrencyInterface
{
    const SYMBOL_TYPE_DEFAULT        = 'default';
    const SYMBOL_TYPE_NARROW         = 'narrow';
    const DISPLAY_NAME_COUNT_DEFAULT = 'default';
    const DISPLAY_NAME_COUNT_ONE     = 'one';
    const DISPLAY_NAME_COUNT_OTHER   = 'other';

    /**
     * Is this currency active ?
     *
     * @var bool
     */
    protected $isActive;

    /**
     * Conversion rate of this currency against the default shop's currency
     *
     * Price in currency A * currency A's conversion rate = price in default currency
     *
     * Example:
     * Given the Euro as default shop's currency,
     * If 1 dollar = 1.31 euros,
     * Then conversion rate for Dollar will be 1.31
     *
     * @var float
     */
    protected $conversionRate;

    /**
     * Currency's alphabetic ISO code (ISO 4217)
     *
     * @see https://www.iso.org/iso-4217-currency-codes.html
     *
     * @var string
     */
    protected $isoCode;

    /**
     * Currency's numeric ISO code (ISO 4217)
     *
     * @see https://www.iso.org/iso-4217-currency-codes.html
     *
     * @var string
     */
    protected $numericIsoCode;

    /**
     * Currency's symbols, by locale code
     *
     * eg.: $symbolsUSD = [
     *     'en-US' => '$',
     *     'es-CO' => 'US$', // In Colombia, colombian peso's symbol is "$". They have to differentiate foreign dollars.
     * ]
     *
     * @var string[]
     */
    protected $symbols;

    /**
     * Number of decimal digits to use with this currency
     *
     * @var int
     */
    protected $precision;

    /**
     * the currency's name, depending on count context
     *
     * e.g.: "Used currency is Dollar" (default), "I need one dollar" (one), "I need five dollars" (other)
     * [
     *     'default' => 'dollar',
     *     'one'     => 'dollar',
     *     'other'   => 'dollars',
     * ]
     *
     * @var string[]
     */
    protected $names;

    /**
     * @param bool $isActive
     *  Is this currency active ?
     *
     * @param float $conversionRate
     *  Conversion rate of this currency against the default shop's currency
     *
     * @param string $isoCode
     *  Currency's alphabetic ISO code (ISO 4217)
     *
     * @param int $numericIsoCode
     *  Currency's numeric ISO code (ISO 4217)
     *
     * @param string[] $symbols
     *  Currency's symbols, by locale code
     *
     * @param int $precision
     *  Number of decimal digits to use with this currency
     *
     * @param string[] $names
     *  the currency's name, by locale code
     *
     */
    public function __construct(
        $isActive,
        $conversionRate,
        $isoCode,
        $numericIsoCode,
        $symbols,
        $precision,
        $names
    ) {
        $this->isActive       = $isActive;
        $this->conversionRate = $conversionRate;
        $this->isoCode        = $isoCode;
        $this->numericIsoCode = $numericIsoCode;
        $this->symbols        = $symbols;
        $this->precision      = $precision;
        $this->names          = $names;
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @inheritDoc
     */
    public function getConversionRate()
    {
        return $this->conversionRate;
    }

    /**
     * @inheritDoc
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * @inheritDoc
     */
    public function getNumericIsoCode()
    {
        return $this->numericIsoCode;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizationException
     */
    public function getSymbol($type = self::SYMBOL_TYPE_NARROW)
    {
        $type = (string)$type;
        if (!isset($this->symbols[$type])) {
            throw new LocalizationException('Invalid symbol type: ' . print_r($type, true));
        }

        return $this->symbols[$type];
    }

    /**
     * @inheritDoc
     */
    public function getDecimalPrecision()
    {
        return $this->precision;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizationException
     */
    public function getName($countContext = self::DISPLAY_NAME_COUNT_DEFAULT)
    {
        $countContext = (string)$countContext;
        if (!isset($this->names[$countContext])) {
            throw new LocalizationException('Invalid count context: ' . print_r($countContext, true));
        }

        return $this->names[$countContext];
    }
}
