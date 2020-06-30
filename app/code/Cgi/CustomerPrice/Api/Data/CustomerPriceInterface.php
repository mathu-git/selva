<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Api\Data;

/**
 * Interface CustomerPriceInterface
 *
 * @package Cgi\CustomerPrice\Api\Data
 */
interface CustomerPriceInterface
{
    /**
     * Constants of customer price
     */
    public const ENTITY_ID = 'entity_id';
    public const ATTRIBUTE_TYPE = 'attribute_type';
    public const CUSTOMER_ID = 'customer_id';
    public const PRODUCT_ID = 'product_id';
    public const PRICE = 'price';

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get attribute_type
     *
     * @return int|null
     */
    public function getAttributeType();

    /**
     * Get Customer_id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get product_id
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Get price
     *
     * @return string|null
     */
    public function getPrice();

    /**
     * Set id
     *
     * @param int $id Id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Set attribute_type
     *
     * @param int $AttributeType Attribute type
     *
     * @return $this
     */
    public function setAttributeType($AttributeType);

    /**
     * Set customer_id
     *
     * @param int $customerId Customer id
     *
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Set product_id
     *
     * @param int $productId Product id
     *
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Set price
     *
     * @param string $customerPrice Price
     *
     * @return $this
     */
    public function setPrice($customerPrice);
}
