<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Plugin\Pricing\Price;

use Cgi\CustomerPrice\Model\AddCustomerPrice;
use Exception;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Customer\Model\SessionFactory;

/**
 * Class AddCustomerPricesToTierPricePlugin
 *
 * @package Cgi\CustomerPrice\Plugin\Pricing\Price
 */
class AddCustomerPricesToTierPricePlugin
{
    /**
     * @var AddCustomerPrice
     */
    protected $addCustomerPrice;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * AddCustomerPricesToTierPricePlugin constructor
     *
     * @param AddCustomerPrice $addCustomerPrice
     * @param SessionFactory   $sessionFactory
     */
    public function __construct(
        AddCustomerPrice $addCustomerPrice,
        SessionFactory $sessionFactory
    ) {
        $this->addCustomerPrice = $addCustomerPrice;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * Altered tier price
     *
     * @param  TierPrice  $subject
     * @param  bool|float $result
     * @return float|null
     * @throws Exception
     */
    public function afterGetValue(TierPrice $subject, $result)
    {
        $customerSession = $this->sessionFactory->create();

        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getId();
        } else {
            return $result;

        }

        $product = $subject->getProduct();
        $tierPrice = $this->addCustomerPrice->changeProductTierPrice($product, $customerId);

        if (is_null($tierPrice)) {
            return $result;
        }

        return $tierPrice;
    }
}
