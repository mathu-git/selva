<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Observer\Admin;

use Cgi\CustomerPrice\Model\CustomerPrice;
use Cgi\CustomerPrice\Model\CustomerPriceFactory;
use Cgi\CustomerPrice\Model\CustomerPriceRepository;
use Cgi\CustomerPrice\Model\ResourceModel\CustomerPrice as ResourceCustomerPrice;
use Cgi\CustomerPrice\Model\ResourceModel\Product\Indexer\CustomerPrice as IndexCustomerPrice;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class CatalogProductSaveAfterObserver
 *
 * @package Cgi\CustomerPrice\Observer\Admin
 */
class CatalogProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var CustomerPriceFactory
     */
    protected $customerPriceFactory;

    /**
     * @var CustomerPriceRepository
     */
    protected $customerPriceRepository;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ResourceCustomerPrice
     */
    protected $customerPriceResourceModel;

    /**
     * @var IndexCustomerPrice
     */
    protected $indexer;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;


    /**
     * CatalogProductSaveAfterObserver constructor.
     *
     * @param ResourceCustomerPrice $customerPriceResourceModel
     * @param IndexCustomerPrice $indexer
     * @param IndexerRegistry $indexerRegistry
     * @param CustomerPriceFactory $customerPriceFactory
     * @param CustomerPriceRepository $customerPriceRepository
     * @param SerializerInterface $serializer
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ResourceCustomerPrice $customerPriceResourceModel,
        IndexCustomerPrice $indexer,
        IndexerRegistry $indexerRegistry,
        CustomerPriceFactory $customerPriceFactory,
        CustomerPriceRepository $customerPriceRepository,
        SerializerInterface $serializer,
        ManagerInterface $messageManager
    ) {
        $this->customerPriceResourceModel = $customerPriceResourceModel;
        $this->indexer = $indexer;
        $this->indexerRegistry = $indexerRegistry;
        $this->customerPriceFactory = $customerPriceFactory;
        $this->customerPriceRepository = $customerPriceRepository;
        $this->serializer = $serializer;
        $this->messageManager = $messageManager;
    }

    /**
     * Save and reindex after the product is saved with customer price
     *
     * @param  EventObserver $observer
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {

        /**
         * @var Product $product
         */
        $product = $observer->getEvent()->getProduct();

        $productId = $product->getId();
        $productTypeId = $product->getTypeId();
        $customerPrice = $product->getData('customer_price');
        $attributeType = CustomerPrice::TYPE_PRICE_CUSTOMER;
        $priceType = CustomerPrice::PRICE_TYPE_FIXED;
        $customerPriceData = $this->serializer->unserialize($customerPrice);
        $updatedCustomerIds = [];
        $savedCustomerIds = [];

        if (($productTypeId == 'simple') && isset($customerPrice) && (!empty($customerPriceData))) {
            $existingCustomerIds = $this->customerPriceResourceModel->getCustomerIdsByProductId($productId);
            foreach ($customerPriceData as $customerPrice) {
                $data = [
                    'attribute_type' => $attributeType,
                    'customer_id' => $customerPrice['customer'],
                    'product_id' => $productId,
                    'price' => abs(floatval($customerPrice['price_per_customer'])),
                    'price_type' => $priceType,
                ];

                $updatedCustomerIds[] = $customerPrice['customer'];
                $customerPriceModel = $this->customerPriceFactory->create();
                $customerPriceModel->setData($data);

                $this->deleteData($productId, $customerPrice['customer']);
                $this->customerPriceRepository->save($customerPriceModel);
                $savedCustomerIds = $this->customerPriceResourceModel->getCustomerIdsByProductId($productId);

                /* reindex data */
                $this->indexer->setTypeId($productTypeId);
                $this->indexer->reindexEntityCustomer([$productId], [$data['customer_id']]);
            }

            /*remove the data from custom table as soon as customer price is deleted in the admin*/
            $deleteData = array_diff($updatedCustomerIds, $savedCustomerIds);
            if (empty($deleteData)) {
                $customersToBeDeleted = array_diff($existingCustomerIds, $updatedCustomerIds);
                if (!empty($customersToBeDeleted)) {
                    $this->deleteCustomers($customersToBeDeleted, $productId);
                }
            }
            return $this;
        }
    }

    /**
     * Clean old data and update
     *
     * @param  $productId
     * @param  $customerId
     * @throws LocalizedException
     */
    protected function deleteData($productId, $customerId)
    {
        $this->customerPriceResourceModel->deleteProductCustomerPrice($productId, $customerId);
        $this->customerPriceResourceModel->deleteRowInCgiCatalogProductEntityDecimalCustomerPrice(
            $productId,
            $customerId
        );
        $this->customerPriceResourceModel->deleteRowInCgiCatalogProductIndexPrice(
            $productId,
            $customerId
        );
    }

    /**
     * Delete the customers deleted in admin from the custom table
     *
     * @param  array $customersToBeDeleted
     * @param  $productId
     * @throws LocalizedException
     */
    protected function deleteCustomers($customersToBeDeleted, $productId)
    {
        foreach ($customersToBeDeleted as $customerToBeDeleted) {
            $this->customerPriceResourceModel->deleteProductCustomerPrice($productId, $customerToBeDeleted);
            $this->customerPriceResourceModel->deleteRowInCgiCatalogProductEntityDecimalCustomerPrice(
                $productId,
                $customerToBeDeleted
            );
            $this->customerPriceResourceModel->deleteRowInCgiCatalogProductIndexPrice(
                $productId,
                $customerToBeDeleted
            );
        }
    }
}
