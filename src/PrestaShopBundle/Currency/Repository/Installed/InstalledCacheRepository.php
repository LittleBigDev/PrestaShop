<?php
/**
 * 2007-2017 PrestaShop
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
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShopBundle\Currency\Repository\Installed;

use PrestaShopBundle\Currency\Currency;
use PrestaShopBundle\Currency\CurrencyFactory;
use PrestaShopBundle\Currency\CurrencyParameters;
use PrestaShopBundle\Currency\Symbol;
use PSR\Cache\CacheItemPoolInterface;

class InstalledCacheRepository extends AbstractInstalledRepositoryMiddleware
{
    /**
     * @var CacheItemPoolInterface
     */
    protected $cacheService;

    public function __construct(
        InstalledRepositoryInterface $nextRepository = null,
        CacheItemPoolInterface $cacheService
    ) {
        $this->setNextRepository($nextRepository);
        $this->cacheService = $cacheService;
    }

    /**
     * Get currency data by internal database identifier
     *
     * @param int $id
     *   The currency id
     *
     * @return Currency
     *   The requested currency
     */
    public function getCurrencyByIdOnCurrentRepository($id)
    {
        $cacheItem    = $this->cacheService->getItem($id);
        $currencyData = $cacheItem->get();
        if (!empty($currencyData)) {
            $currencyParameters = new CurrencyParameters();
            $currencyParameters
                ->setId($id)
                ->setIsoCode($currencyData['isoCode'])
                ->setNumericIsoCode($currencyData['numericIsoCode'])
                ->setDecimalDigits($currencyData['decimalDigits'])
                ->setDisplayNameData($currencyData['localizedNames'])
                ->setSymbol(new Symbol(
                    $currencyData['symbol']['default'],
                    $currencyData['symbol']['narrow']
                ));

            $factory  = new CurrencyFactory();
            $currency = $factory->build($currencyParameters);

            return $currency;
        }

        return null;
    }

    /**
     * @param Currency $currency
     *
     * @return Currency|null
     */
    protected function addInstalledCurrencyOnCurrentRepository(Currency $currency)
    {
        $this->setInCache($currency);

        return $currency;
    }

    /**
     * @param Currency $currency
     *
     * @return Currency|null
     */
    protected function updateInstalledCurrencyCurrentRepository(Currency $currency)
    {
        $this->setInCache($currency);

        return $currency;
    }

    /**
     * @param Currency $currency
     *
     * @return bool
     */
    protected function deleteInstalledCurrencyCurrentRepository(Currency $currency)
    {
        $this->cacheService->deleteItem($currency->getId());

        return true;
    }

    protected function setInCache(Currency $currency)
    {
        if ((int)$currency->getId() > 0) {
            // do not store currency if id was not set
            $cacheItem = new InstalledCacheItem();
            $cacheItem->setKey($currency->getId())
                ->set(
                    array(
                        'isoCode'          => $currency->getIsoCode(),
                        'numericIsoCode'   => $currency->getNumericIsoCode(),
                        'decimalDigits'    => $currency->getDecimalDigits(),
                        'localizedNames'   => $currency->getName(),
                        'localizedSymbols' => $currency->getSymbol(),
                    )
                );
        }
    }
}
