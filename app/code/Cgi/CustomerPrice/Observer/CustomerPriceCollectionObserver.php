<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Observer;

use Cgi\CustomerPrice\Model\AddCustomerPrice;
use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CustomerPriceCollectionObserver
 *
 * @package Cgi\CustomerPrice\Observer
 */
class CustomerPriceCollectionObserver implements ObserverInterface
{
    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var AddCustomerPrice
     */
    protected $addCustomerPrice;

    /**
     * CustomerPriceCollectionObserver constructor.
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
     * Load product collection with customer price
     *
     * @param EventObserver $observer
     * @return $this|void
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var Collection $collection
         */
        $collection = $observer->getData('collection');

        $customerSession = $this->sessionFactory->create();

        if ($customerSession->isLoggedIn() && $collection->getSize()) {

            $customerId = $customerSession->getId();
            $ids = $this->getIds($collection);

            if (!empty($ids)) {
                $this->addCustomerPrice->changeCollectionPrice($collection, $ids, $customerId);
            }
        }

        return $this;
    }

    /**
     * Get ids of products
     *
     * @param  Collection $collection
     * @return array
     * @throws Exception
     */
    protected function getIds($collection)
    {
        $ids = [];
        foreach ($collection as $product) {
            $productId = $product->getId();
            $ids[$productId] = $productId;
        }

        return $ids;
    }
}
