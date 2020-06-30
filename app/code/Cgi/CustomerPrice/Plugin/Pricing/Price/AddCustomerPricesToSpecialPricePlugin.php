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
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Customer\Model\SessionFactory;

/**
 * Class AddCustomerPricesToSpecialPricePlugin
 *
 * @package Cgi\CustomerPrice\Plugin\Pricing\Price
 */
class AddCustomerPricesToSpecialPricePlugin
{
    /**
     * Customer price model
     *
     * @var AddCustomerPrice
     */
    protected $addCustomerPrice;

    /**
     * Session factory
     *
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * AddCustomerPricesToSpecialPricePlugin constructor
     *
     * @param AddCustomerPrice $addCustomerPrice Customer price model
     * @param SessionFactory   $sessionFactory   Session factory
     */
    public function __construct(
        AddCustomerPrice $addCustomerPrice,
        SessionFactory $sessionFactory
    ) {
        $this->addCustomerPrice = $addCustomerPrice;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * Altered special price
     *
     * @param SpecialPrice $subject Special price
     * @param bool|float   $result  Price
     *
     * @return float|null
     * @throws Exception
     */
    public function afterGetValue(SpecialPrice $subject, $result)
    {
        $customerSession = $this->sessionFactory->create();

        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getId();
        } else {
            return $result;
        }

        $product = $subject->getProduct();
        $specialPrice = $this->addCustomerPrice->changeProductSpecialPrice($product, $customerId);

        if (is_null($specialPrice)) {
            return $result;
        }

        return $specialPrice;
    }
}
