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

use Doctrine\DBAL\Driver\Connection;
use PrestaShopBundle\Currency\Currency;
use PrestaShopBundle\Currency\CurrencyFactory;
use PrestaShopBundle\Currency\Exception\CurrencyNotFoundException;

class InstalledDatabaseRepository extends AbstractInstalledRepositoryMiddleware
{

    protected $connection;

    public function __construct(InstalledRepositoryInterface $nextRepository = null, Connection $connection)
    {
        $this->nextRepository = $nextRepository;
        $this->connection     = $connection;
    }

    /**
     * Get currency data by internal database identifier
     *
     * @param int $id
     *
     * @return Currency
     */
    public function getCurrencyByIdOnCurrentRepository($id)
    {
        $currencyModel = new \Currency($id);
        if ($currencyModel->id > 0) {
            $factory  = new CurrencyFactory();
            $currency = $factory->setId($currencyModel->id)
                                ->setIsoCode($currencyModel->iso_code)
                                ->setNumericIsoCode($currencyModel->iso_code_num)
                                ->setDecimalDigits($currencyModel->decimals)
                                ->setDisplayName($currencyModel->name)
                //->setSymbols($currencyModel->symbol)
                                ->build();

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
        $currencyModel = new \Currency;
        $this->hydrateCurrencyModel($currencyModel, $currency);
        $currencyModel->save();

        return $currency;
    }

    /**
     * @param Currency $currency
     *
     * @return Currency|null
     */
    protected function updateInstalledCurrencyCurrentRepository(Currency $currency)
    {
        $currencyModel = new \Currency($currency->getId());
        if ($currencyModel->id <= 0) {
            throw new CurrencyNotFoundException(
                'Cannot update currency with id ' . $currency->getId() . ' : currency not found'
            );
        }
        $this->hydrateCurrencyModel($currencyModel, $currency);
        $currencyModel->save();

        return $currency;
    }

    protected function hydrateCurrencyModel(\Currency $currencyModel, Currency $currency)
    {
        $currencyModel->iso_code     = $currency->getIsoCode();
        $currencyModel->iso_code_num = $currency->getNumericIsoCode();
        $currencyModel->decimals     = $currency->getDecimalDigits();
        $currencyModel->name         = $currency->getDisplayNames();
        //$currencyModel->symbol = $currency->getSymbol();
    }

    /**
     * @param Currency $currency
     *
     * @return bool
     */
    protected function deleteInstalledCurrencyCurrentRepository(Currency $currency)
    {
        $currencyModel = new \Currency($currency->getId());
        if ($currencyModel->id <= 0) {
            throw new CurrencyNotFoundException(
                'Cannot update currency with id ' . $currency->getId() . ' : currency not found'
            );
        }
        $currencyModel->delete();

        return true;
    }
}
