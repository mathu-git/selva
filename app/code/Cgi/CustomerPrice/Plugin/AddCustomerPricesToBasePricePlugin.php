<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Plugin;

use Cgi\CustomerPrice\Model\AddCustomerPrice;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\Customer\Model\SessionFactory;

/**
 * Class AddCustomerPricesToBasePricePlugin
 *
 * @package Cgi\CustomerPrice\Plugin
 */
class AddCustomerPricesToBasePricePlugin
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
     * AddCustomerPricesToBasePricePlugin constructor.
     *
     * @param SessionFactory   $sessionFactory   Session factory
     * @param AddCustomerPrice $addCustomerPrice Customer price model
     */
    public function __construct(
        SessionFactory $sessionFactory,
        AddCustomerPrice $addCustomerPrice
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->addCustomerPrice = $addCustomerPrice;
    }

    /**
     * Altered base price
     *
     * @param Price    $subject Default price model
     * @param callable $proceed Proceed to next
     * @param Product  $product Product
     * @param null     $qty     Quantity
     *
     * @return int|null
     * @throws Exception
     */
    public function aroundGetBasePrice(Price $subject, callable $proceed, $product, $qty = null)
    {
        $customerSession = $this->sessionFactory->create();

        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getId();
        } else {
            return $proceed($product, $qty);
        }

        $price = $this->addCustomerPrice->changeProductPrice($product, $customerId);

        if (is_null($price)) {
            return $proceed($product, $qty);
        }

        return $price;
    }
}
