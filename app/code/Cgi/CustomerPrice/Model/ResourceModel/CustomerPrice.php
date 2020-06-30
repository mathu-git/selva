<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Model\ResourceModel;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class CustomerPrice
 *
 * @package Cgi\CustomerPrice\Model\ResourceModel
 */
class CustomerPrice extends AbstractDb
{
    /**
     * Eav config
     *
     * @var Config
     */
    protected $eavConfig;

    /**
     * Adapter interface
     *
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * CustomerPrice constructor.
     *
     * @param Context $context   Context
     * @param Config  $eavConfig Eav configuration
     */
    public function __construct(
        Context $context,
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
        $this->connection = $this->getConnection();
    }

    /**
     * Get price attribute id
     *
     * @return int|mixed|null
     * @throws LocalizedException
     */
    public function getPriceAttributeId()
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, 'price')->getAttributeId();
    }

    /**
     * Get product type id
     *
     * @param int $productId Product id
     *
     * @return null
     */
    public function getTypeId($productId)
    {
        $select = $this->connection->select()->from(
            [$this->getTable('catalog_product_entity')]
        )->where('entity_id', $productId);

        $data = $this->connection->fetchRow($select);

        if (!empty($data['type_id'])) {
            return $data['type_id'];
        }

        return null;
    }

    /**
     * Get entity ids
     *
     * @param array $referenceIds Entity ids
     *
     * @return array
     * @throws Exception
     */
    public function getEntityIds($referenceIds)
    {
        $select = $this->connection->select()
            ->from(
                $this->getTable('catalog_product_entity'),
                ['entity_id']
            )
            ->where('entity_id' . ' IN(?)', $referenceIds);

        $ids = $this->connection->fetchCol($select);

        return array_combine($ids, $ids);
    }

    /**
     * Get data by customer id for the products
     *
     * @param array $ids        Entity ids
     * @param int   $customerId Customer id
     *
     * @return array
     */
    public function getCalculatedProductsDataByCustomer(array $ids, $customerId)
    {
        $tableName = $this->getTable('cgi_catalog_product_entity_decimal_customer_price');
        $select = $this->connection->select()
            ->from($tableName)
            ->where('customer_id = ?', $customerId)
            ->where('entity_id' . ' IN(?)', $ids);

        return $this->connection->fetchAll($select);
    }

    /**
     * Get data by customer id for a product
     *
     * @param int $id         Entity id
     * @param int $customerId Customer id
     *
     * @return array
     */
    public function getCalculatedProductDataByCustomer($id, $customerId)
    {
        $tableName = $this->getTable('cgi_catalog_product_entity_decimal_customer_price');
        $select = $this->connection->select()
            ->from($tableName)
            ->where('customer_id = ?', $customerId)
            ->where('entity_id' . ' = ?', $id);

        return $this->connection->fetchAll($select);
    }

    /**
     * Delete customer price in custom table
     *
     * @param int $productId  Product id
     * @param int $customerId Customer id
     *
     * @return $this|void
     * @throws LocalizedException
     */
    public function deleteProductCustomerPrice($productId, $customerId)
    {
        $tableName = $this->getMainTable();
        $this->connection->delete(
            $tableName,
            [
                'product_id' . ' = ?' => $productId,
                'customer_id' . ' = ?' => $customerId
            ]
        );
    }

    /**
     * Delete row in custom entity decimal price table
     *
     * @param int $entityId   Entity id
     * @param int $customerId Customer id
     *
     * @return $this|void
     * @throws LocalizedException
     */
    public function deleteRowInCgiCatalogProductEntityDecimalCustomerPrice($entityId, $customerId)
    {
        $priceAttributeId = $this->getPriceAttributeId();
        $specialPriceAttribute = [$priceAttributeId];

        $this->connection->delete(
            $this->getTable('cgi_catalog_product_entity_decimal_customer_price'),
            [
                'entity_id' . ' = ?' => $entityId,
                'customer_id = ?' => $customerId,
                'attribute_id IN(?)' => $specialPriceAttribute
            ]
        );
    }

    /**
     * Delete row in custom index price table
     *
     * @param int $entityId   Entity id
     * @param int $customerId Customer id
     *
     * @return $this|void
     */
    public function deleteRowInCgiCatalogProductIndexPrice($entityId, $customerId)
    {
        $this->connection->delete(
            $this->getTable('cgi_catalog_product_index_price'),
            [
                'entity_id = ?' => $entityId,
                'customer_id = ?' => $customerId
            ]
        );
    }

    /**
     * Return array customer_id by product_id
     *
     * @param int $productId product id
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerIdsByProductId($productId)
    {
        $select = $this->connection->select()
            ->from($this->getMainTable(), 'customer_id')
            ->where('product_id = ?', $productId);

        $ids = $this->connection->fetchCol($select);

        return array_combine($ids, $ids);
    }

    /**
     * Check if the customer is assigned price in custom table
     *
     * @param int $customerId Customer id
     *
     * @return bool
     * @throws LocalizedException
     */
    public function hasAssignCustomer($customerId)
    {
        $select = $this->connection->select()
            ->from(['cp' => $this->getMainTable()])
            ->where('cp.customer_id = ?', $customerId);
        $data   = $this->connection->fetchRow($select);

        return (bool)$data;
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cgi_customer_price', 'entity_id');
    }
}
