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
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Customer\Model\SessionFactory;

/**
 * Class AddCustomerPricesToRegularPricePlugin
 *
 * @package Cgi\CustomerPrice\Plugin\Pricing\Price
 */
class AddCustomerPricesToRegularPricePlugin
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
     * AddCustomerPricesToRegularPricePlugin constructor.
     *
     * @param SessionFactory   $sessionFactory
     * @param AddCustomerPrice $addCustomerPrice
     */
    public function __construct(
        SessionFactory $sessionFactory,
        AddCustomerPrice $addCustomerPrice
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->addCustomerPrice = $addCustomerPrice;
    }

    /**
     * Altered regular price
     *
     * @param  RegularPrice $subject
     * @param  bool|float   $result
     * @return float|null
     * @throws Exception
     */
    public function afterGetValue(RegularPrice $subject, $result)
    {
        $customerSession = $this->sessionFactory->create();

        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getId();
        } else {
            return $result;
        }

        $product = $subject->getProduct();
        $regularPrice = $this->addCustomerPrice->changeProductRegularPrice($product, $customerId);

        if (is_null($regularPrice)) {
            return $result;
        }

        return $regularPrice;
    }
}
