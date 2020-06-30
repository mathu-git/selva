<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Model;

use Cgi\CustomerPrice\Model\ResourceModel\CustomerPrice as ResourceCustomerPrice;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class AddCustomerPrice
 *
 * @package Cgi\CustomerPrice\Model
 */
class AddCustomerPrice
{
    /**
     * Customer price resource model
     *
     * @var ResourceCustomerPrice
     */
    protected $customerPriceResourceModel;

    /**
     * Price data cache
     *
     * @var array
     */
    protected $cachePricesData;

    /**
     * AddCustomerPrice constructor.
     *
     * @param ResourceCustomerPrice $customerPriceResourceModel Customer price resource model
     */
    public function __construct(
        ResourceCustomerPrice $customerPriceResourceModel
    ) {
        $this->customerPriceResourceModel = $customerPriceResourceModel;
    }

    /**
     * Change Collection Price
     *
     * @param Collection $collection Customer price collection
     * @param array      $ids        Product ids
     * @param int        $customerId Customer id
     *
     * @return $this|void
     * @throws Exception
     */
    public function changeCollectionPrice($collection, $ids, $customerId)
    {
        $calculatedProductsPricesByCollectionIds = $this->customerPriceResourceModel->getCalculatedProductsDataByCustomer(
            $ids,
            $customerId
        );

        if (!empty($calculatedProductsPricesByCollectionIds)) {
            $priceAttributeId = $this->customerPriceResourceModel->getPriceAttributeId();
        }

        foreach ($collection as $product) {
            $productId = $product->getId();
            if (!empty($this->cachePricesData[$productId][$customerId]['price']) || is_null($product->getPrice())) {
                continue;
            }

            foreach ($calculatedProductsPricesByCollectionIds as $productPrice) {
                if ($productId != $productPrice['entity_id']) {
                    continue;
                }
                if (isset($productPrice['value']) && $productPrice['value'] >= 0) {
                    /**
                     * Price attribute id
                     *
                     * @var PriceAttributeId $priceAttributeId
                     */
                    if ($priceAttributeId == $productPrice['attribute_id']) {
                        $this->cachePricesData[$productId][$customerId]['price'] = (float)$productPrice['value'];
                    }
                }
            }
        }

        foreach ($collection as $product) {
            $this->changeProductPrice($product, $customerId);
            $this->changeProductRegularPrice($product, $customerId);
        }
    }

    /**
     * Change Product Price
     *
     * @param Product $product    Product
     * @param int     $customerId Customer id
     *
     * @return float|null
     * @throws Exception
     */
    public function changeProductPrice($product, $customerId)
    {
        if (is_null($product->getPrice())) {
            return null;
        }
        $productId = $product->getId();
        if (!isset($this->cachePricesData[$productId][$customerId]['price'])) {
            $this->cachePricesData[$productId][$customerId]['price'] = null;

            $calculatedCustomerProductPrices = $this->customerPriceResourceModel->getCalculatedProductDataByCustomer(
                $productId,
                $customerId
            );

            if (!empty($calculatedCustomerProductPrices)) {
                $priceAttributeId = $this->customerPriceResourceModel->getPriceAttributeId();
            }

            foreach ($calculatedCustomerProductPrices as $productPrice) {
                if (isset($productPrice['value']) && $productPrice['value'] >= 0) {
                    /**
                     * Price attribute id
                     *
                     * @var PriceAttributeId $priceAttributeId
                     */
                    if ($priceAttributeId == $productPrice['attribute_id']) {
                        $price = (float)$productPrice['value'];
                        $product->setData('price', $price);
                        $this->cachePricesData[$productId][$customerId]['price'] = $price;
                    }
                }
            }
        }

        return $this->cachePricesData[$productId][$customerId]['price'];
    }

    /**
     * Change Product Regular Price
     *
     * @param Product $product    Product
     * @param int     $customerId Customer id
     *
     * @return float|null
     * @throws Exception
     */
    public function changeProductRegularPrice($product, $customerId)
    {
        $productId = $product->getId();
        if (!isset($this->cachePricesData[$productId][$customerId]['regular_price'])) {
            $this->cachePricesData[$productId][$customerId]['regular_price'] = null;

            $calculatedCustomerProductPrices = $this->customerPriceResourceModel->getCalculatedProductDataByCustomer(
                $productId,
                $customerId
            );

            if (!empty($calculatedCustomerProductPrices)) {
                $priceAttributeId = $this->customerPriceResourceModel->getPriceAttributeId();
            }

            foreach ($calculatedCustomerProductPrices as $productPrice) {
                /**
                 * Price attribute id
                 *
                 * @var PriceAttributeId $priceAttributeId
                 */
                if (isset($productPrice['value']) && $productPrice['value'] >= 0
                    && $priceAttributeId == $productPrice['attribute_id']
                ) {
                    $regularPrice = (float)$productPrice['value'];
                    $product->setData('price', $regularPrice);
                    $this->cachePricesData[$productId][$customerId]['regular_price'] = $regularPrice;
                }
            }
        }

        return $this->cachePricesData[$productId][$customerId]['regular_price'];
    }

    /**
     * Change Product Special Price
     *
     * @param Product $product    Product
     * @param int     $customerId Customer id
     *
     * @return float|null
     * @throws Exception
     */
    public function changeProductSpecialPrice($product, $customerId)
    {
        if (is_null($product->getPrice())) {
            return null;
        }
        $productId = $product->getId();
        if (!isset($this->cachePricesData[$productId][$customerId]['special_price'])) {
            $this->cachePricesData[$productId][$customerId]['special_price'] = null;

            $calculatedCustomerProductPrices = $this->customerPriceResourceModel->getCalculatedProductDataByCustomer(
                $productId,
                $customerId
            );

            if (!empty($calculatedCustomerProductPrices)) {
                $priceAttributeId = $this->customerPriceResourceModel->getPriceAttributeId();
            }

            foreach ($calculatedCustomerProductPrices as $productPrice) {
                if (isset($productPrice['value']) && $productPrice['value'] >= 0) {
                    /**
                     * Price attribute id
                     *
                     * @var PriceAttributeId $priceAttributeId
                     */
                    if ($priceAttributeId == $productPrice['attribute_id']) {
                        $specialPrice = (float)$productPrice['value'];
                        $product->setData('special_price', $specialPrice);
                        $this->cachePricesData[$productId][$customerId]['special_price'] = $specialPrice;
                    }
                }
            }
        }

        return $this->cachePricesData[$productId][$customerId]['special_price'];
    }

    /**
     * Change Product Tier Price
     *
     * @param Product $product    Product
     * @param int     $customerId Customer id
     *
     * @return float|null
     * @throws Exception
     */
    public function changeProductTierPrice($product, $customerId)
    {
        if (is_null($product->getPrice())) {
            return null;
        }
        $productId = $product->getId();
        if (!isset($this->cachePricesData[$productId][$customerId]['tier_price'])) {
            $this->cachePricesData[$productId][$customerId]['tier_price'] = null;

            $calculatedCustomerProductPrices = $this->customerPriceResourceModel->getCalculatedProductDataByCustomer(
                $productId,
                $customerId
            );

            if (!empty($calculatedCustomerProductPrices)) {
                $priceAttributeId = $this->customerPriceResourceModel->getPriceAttributeId();
            }

            foreach ($calculatedCustomerProductPrices as $productPrice) {
                if (isset($productPrice['value']) && $productPrice['value'] >= 0) {
                    /**
                     * Price attribute id
                     *
                     * @var PriceAttributeId $priceAttributeId
                     */
                    if ($priceAttributeId == $productPrice['attribute_id']) {
                        $tierPrice = (float)$productPrice['value'];
                        $product->setData('tier_price', $tierPrice);
                        $this->cachePricesData[$productId][$customerId]['tier_price'] = $tierPrice;
                    }
                }
            }
        }

        return $this->cachePricesData[$productId][$customerId]['tier_price'];
    }
}
