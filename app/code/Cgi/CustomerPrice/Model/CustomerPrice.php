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

use Cgi\CustomerPrice\Api\Data\CustomerPriceInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class CustomerPrice
 *
 * @package Cgi\CustomerPrice\Model
 */
class CustomerPrice extends AbstractModel implements CustomerPriceInterface, IdentityInterface
{
    /**
     * Constants in customer price
     */
    const TYPE_PRICE_CUSTOMER = 1;
    const PRICE_TYPE_FIXED = 1;

    const CACHE_TAG = 'cgi_customer_price';

    /**
     * Get Attribute Type
     *
     * @return int|void|null
     */
    public function getAttributeType()
    {
        return $this->getData(self::ATTRIBUTE_TYPE);
    }

    /**
     *  Get Customer Id
     *
     * @return array|int|mixed|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Get Product Id
     *
     * @return array|int|mixed|null
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Get Price
     *
     * @return array|mixed|string|null
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * Set Id
     *
     * @param  mixed $entityId
     * @return CustomerPrice
     */
    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Set Attribute Type
     *
     * @param  $AttributeType
     * @return CustomerPrice|void
     */
    public function setAttributeType($AttributeType)
    {
        return $this->setData(self::ATTRIBUTE_TYPE, $AttributeType);
    }

    /**
     * Set Customer Id
     *
     * @param  $customerId
     * @return CustomerPrice
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set Product Id
     *
     * @param  $productId
     * @return CustomerPrice
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Set Price
     *
     * @param  $customerPrice
     * @return CustomerPrice
     */
    public function setPrice($customerPrice)
    {
        return $this->setData(self::PRICE, $customerPrice);
    }

    /**
     * Get Identity
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Entity Id
     *
     * @return array|int|mixed|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cgi\CustomerPrice\Model\ResourceModel\CustomerPrice');
    }
}
