<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Model\ResourceModel\Product\Indexer;

use Cgi\CustomerPrice\Model\ResourceModel\CustomerPrice as ResourceCustomerPrice;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Eav\Model\Config;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\Table\StrategyInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Module\Manager;
use Zend_Db_Expr;
use Zend_Db_Select;

class CustomerPrice extends DefaultPrice
{
    /**
     * Product type code
     *
     * @var string
     */
    protected $_typeId;

    /**
     * Product Type is composite flag
     *
     * @var bool
     */
    protected $isComposite = false;

    /**
     * Core data
     *
     * @var Manager
     */
    protected $moduleManager;

    /**
     * Core event manager proxy
     *
     * @var ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var ResourceCustomerPrice
     */
    protected $customerPriceResourceModel;

    /**
     * @var bool|null
     */
    private $hasEntity = null;

    /**
     * CustomerPrice constructor.
     *
     * @param Context               $context
     * @param StrategyInterface     $tableStrategy
     * @param Config                $eavConfig
     * @param ManagerInterface      $eventManager
     * @param Manager               $moduleManager
     * @param string|null           $connectionName
     * @param ResourceCustomerPrice $customerPriceResourceModel
     */
    public function __construct(
        Context $context,
        StrategyInterface $tableStrategy,
        Config $eavConfig,
        ManagerInterface $eventManager,
        Manager $moduleManager,
        $connectionName = null,
        ResourceCustomerPrice $customerPriceResourceModel
    ) {
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        $this->customerPriceResourceModel = $customerPriceResourceModel;
        parent::__construct($context, $tableStrategy, $eavConfig, $eventManager, $moduleManager, $connectionName);
    }

    /**
     * Get Table strategy
     *
     * @return StrategyInterface
     */
    public function getTableStrategy()
    {
        return $this->tableStrategy;
    }

    /**
     * Define main price index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cgi_catalog_product_index_price', 'entity_id');
    }

    /**
     * Set Product Type code
     *
     * @param string $typeCode
     * @return $this
     */
    public function setTypeId($typeCode)
    {
        $this->_typeId = $typeCode;

        return $this;
    }

    /**
     * Retrieve Product Type Code
     *
     * @return string
     * @throws LocalizedException
     */
    public function getTypeId()
    {
        if ($this->_typeId === null) {
            throw new LocalizedException(
                __('A product type is not defined for the indexer.')
            );
        }

        return $this->_typeId;
    }

    /**
     * Set Product Type Composite flag
     *
     * @param  bool $flag
     * @return $this
     */
    public function setIsComposite($flag)
    {
        $this->isComposite = (bool)$flag;

        return $this;
    }

    /**
     * Check product type is composite
     *
     * @return bool
     */
    public function getIsComposite()
    {
        return $this->isComposite;
    }

    /**
     * Reindex temporary (price result data) for all products
     *
     * @return $this
     * @throws Exception
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);
        $this->beginTransaction();
        try {
            $this->reindex();
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Reindex temporary (price result data) for defined product(s)
     *
     * @param  array $entityIds
     * @param  array $customerIds
     * @return $this
     * @throws LocalizedException
     */
    public function reindexEntityCustomer($entityIds, $customerIds)
    {
        $this->reindexCustomer($entityIds, $customerIds);

        return $this;
    }

    /**
     * Reindex prices.
     *
     * @param array $entityIds
     * @param array $customerIds
     * @return $this
     * @throws LocalizedException
     * @throws Exception
     */
    protected function reindexCustomer($entityIds, $customerIds)
    {
        if (!empty($entityIds) || $this->hasEntity()) {
            $this->_prepareFinalPriceDataCustomer($entityIds, $customerIds);
            $this->_movePriceDataToIndexTable($entityIds);
        }

        return $this;
    }

    /**
     * @return bool|null
     * @throws LocalizedException
     */
    protected function hasEntity()
    {
        if ($this->hasEntity === null) {
            $reader = $this->getConnection();

            $select = $reader->select()->from(
                [$this->getTable('catalog_product_entity')],
                ['count(entity_id)']
            )->where(
                'type_id =?',
                $this->getTypeId()
            );

            $this->hasEntity = (int)$reader->fetchOne($select) > 0;
        }

        return $this->hasEntity;
    }

    /**
     * Prepare products default final price in temporary index table
     *
     * @param  array $entityIds
     * @param  array $customerIds
     * @return CustomerPrice
     * @throws LocalizedException
     * @throws Exception
     */
    protected function _prepareFinalPriceDataCustomer($entityIds, $customerIds)
    {
        return $this->prepareFinalPriceDataForTypeCustomer($entityIds, $this->getTypeId(), $customerIds);
    }

    /**
     * Prepare products default final price in temporary index table
     *
     * @param  array $entityIds
     * @param  $type
     * @param  array $customerIds
     * @return $this
     * @throws Exception
     */
    protected function prepareFinalPriceDataForTypeCustomer($entityIds, $type, $customerIds)
    {
        $this->_prepareDefaultFinalPriceTable();
        $this->prepareCgiCatalogProductEntityDecimalCustomerPrice($entityIds, $customerIds);

        $select = $this->getSelect($entityIds, $type);
        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable(), [], false);
        $this->getConnection()->query($query);

        return $this;
    }

    /**
     * Prepare default final price table
     *
     * @return $this|CustomerPrice
     */
    protected function _prepareDefaultFinalPriceTable()
    {
        $this->getConnection()->delete($this->_getDefaultFinalPriceTable());

        return $this;
    }

    /**
     * Retrieve final price temporary index table name
     *
     * @return string
     */
    protected function _getDefaultFinalPriceTable()
    {
        return $this->tableStrategy->getTableName('cgi_catalog_product_index_price_final');
    }

    /**
     * Prepare table cgi_catalog_product_entity_decimal_customer_price
     *
     * @param array $entityIds
     * @param array $customerIds
     */
    protected function prepareCgiCatalogProductEntityDecimalCustomerPrice($entityIds, $customerIds)
    {
        $connection = $this->getConnection();
        $linkField = 'entity_id';

        $originalTable = $this->getTable('catalog_product_entity_decimal');
        $tableCustomerPrice = $this->getTable('cgi_customer_price');
        $select = $connection->select()->from($originalTable)->where(
            $originalTable . '.' . $linkField . ' IN(?)',
            $entityIds
        );

        $cond = 'cp.product_id = ' . $originalTable . '.' . $linkField;
        $select->joinLeft(
            ['cp' => $tableCustomerPrice],
            $cond
        );
        if (!empty($customerIds)) {
            $select->where('cp.customer_id IN(?)', $customerIds);
        }

        $newPrice = "cp.price";

        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array(
                    'value_id',
                    'attribute_id' => $originalTable . '.attribute_id',
                    'store_id' => $originalTable . '.store_id',
                    $linkField => $originalTable . '.' . $linkField,
                    'value' => new Zend_Db_Expr($newPrice),
                    'customer_id' => 'cp.customer_id'
                )
            );

        $newTable = $this->getTable('cgi_catalog_product_entity_decimal_customer_price');
        $table = $this->getTable($newTable);

        /* clean old data */
        $where = ['customer_id IN(?)' => $customerIds, $linkField . ' IN(?)' => $entityIds];
        $connection->delete($table, $where);
        $query = $select->insertFromSelect($newTable, [], false);
        $connection->query($query);
    }

    /**
     * Framing select query with conditions
     *
     * @param  array|null $entityIds
     * @param  null       $type
     * @return Select
     * @throws Exception
     */
    protected function getSelect($entityIds = null, $type = null)
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $connection = $this->getConnection();
        $select = $this->joinBaseTable($connection);

        if ($type !== null) {
            $select->where('e.type_id = ?', $type);
        }

        // add enable products limitation
        $statusCond = $connection->quoteInto(
            ' =?',
            Status::STATUS_ENABLED
        );
        $this->_addAttributeToSelect(
            $select,
            'status',
            'e.' . $metadata->getLinkField(),
            'cs.store_id',
            $statusCond,
            true
        );
        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->_addAttributeToSelect(
                $select,
                'tax_class_id',
                'e.' . $metadata->getLinkField(),
                'cs.store_id'
            );
        } else {
            $taxClassId = new Zend_Db_Expr('0');
        }
        $select->columns(['tax_class_id' => $taxClassId]);

        $newPrice = $this->_addAttributeToSelectPriceCustomer(
            $select,
            'price',
            'e.' . $metadata->getLinkField(),
            'cs.store_id'
        );
        $finalPrice = $newPrice;

        $select->columns(
            [
                'orig_price' => $connection->getIfNullSql($newPrice, 0),
                'price' => $connection->getIfNullSql($finalPrice, 0),
                'min_price' => $connection->getIfNullSql($finalPrice, 0),
                'max_price' => $connection->getIfNullSql($finalPrice, 0),
                'customer_id' => 'ta_price.customer_id'
            ]
        );

        if ($entityIds !== null) {
            $linkField = 'entity_id';
            $select->where('e.' . $linkField . ' IN(?)', $entityIds);
        }

        return $select;
    }

    /**
     * Join tables for select query
     *
     * @param  $connection
     * @return Select
     */
    protected function joinBaseTable($connection)
    {
        /**
         * @var Select $select
         */
        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->join(
            ['cw' => $this->getTable('store_website')],
            '',
            ['website_id']
        )->join(
            ['cwd' => $this->_getWebsiteDateTable()],
            'cw.website_id = cwd.website_id',
            []
        )->join(
            ['csg' => $this->getTable('store_group')],
            'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id',
            []
        )->join(
            ['cs' => $this->getTable('store')],
            'csg.default_store_id = cs.store_id AND cs.store_id != 0',
            []
        )->join(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id AND pw.website_id = cw.website_id',
            []
        );

        return $select;
    }

    /**
     * Get index website table
     *
     * @return string
     */
    protected function _getWebsiteDateTable()
    {
        return $this->getTable('catalog_product_index_website');
    }

    /**
     * Custom Attribute to be selected
     *
     * @param  $select
     * @param  $attrCode
     * @param  $entity
     * @param  $store
     * @param  null $condition
     * @param  bool $required
     * @return Zend_Db_Expr
     * @throws Exception
     * @throws LocalizedException
     */
    protected function _addAttributeToSelectPriceCustomer(
        $select,
        $attrCode,
        $entity,
        $store,
        $condition = null,
        $required = false
    ) {
        $attribute = $this->_getAttribute($attrCode);
        $attributeId = $attribute->getAttributeId();

        $attributePriceId = $this->customerPriceResourceModel->getPriceAttributeId();

        if ($attributeId == $attributePriceId) {
            $attributeTable =
                $this->getTable('cgi_catalog_product_entity_decimal_customer_price');
        } else {
            $attributeTable = $attribute->getBackend()->getTable();
        }

        $connection = $this->getConnection();
        $joinType = $condition !== null || $required ? 'join' : 'joinLeft';
        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();

        if ($attribute->isScopeGlobal()) {
            $alias = 'ta_' . $attrCode;

            if ($alias == 'ta_price') {
                $select->{$joinType}(
                    [$alias => $attributeTable],
                    "{$alias}.{$productIdField} = {$entity} AND {$alias}.attribute_id = {$attributeId}" .
                    " AND {$alias}.store_id = 0",
                    []
                );
            }

            $expression = new Zend_Db_Expr("{$alias}.value");
        } else {
            $dAlias = 'tad_' . $attrCode;
            $sAlias = 'tas_' . $attrCode;

            $select->{$joinType}(
                [$dAlias => $attributeTable],
                "{$dAlias}.{$productIdField} = {$entity} AND {$dAlias}.attribute_id = {$attributeId}" .
                " AND {$dAlias}.store_id = 0",
                []
            );

            $select->joinLeft(
                [$sAlias => $attributeTable],
                "{$sAlias}.{$productIdField} = {$entity} AND {$sAlias}.attribute_id = {$attributeId}" .
                " AND {$sAlias}.store_id = {$store}",
                []
            );

            $expression = $connection->getCheckSql(
                $connection->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                "{$sAlias}.value",
                "{$dAlias}.value"
            );
        }

        if ($condition !== null) {
            $select->where("{$expression}{$condition}");
        }

        return $expression;
    }

    /**
     * Mode Final Prices index to primary temporary index table
     *
     * @param int[]|null $entityIds
     * @return $this
     * @throws Exception
     */
    protected function _movePriceDataToIndexTable($entityIds = null)
    {
        $columns = [
            'entity_id' => 'entity_id',
            'website_id' => 'website_id',
            'tax_class_id' => 'tax_class_id',
            'price' => 'orig_price',
            'final_price' => 'price',
            'min_price' => 'min_price',
            'max_price' => 'max_price',
            'customer_id' => 'customer_id'
        ];

        $connection = $this->getConnection();

        $table = $this->_getDefaultFinalPriceTable();
        $select = $connection->select()->from($table, $columns);
        $indexTable = $this->getTable('cgi_catalog_product_index_price');

        /* clean old data */
        $connection->delete(
            $indexTable,
            [
                'entity_id IN(?)' => $this->customerPriceResourceModel->getEntityIds($entityIds),
            ]
        );

        $query = $select->insertFromSelect(
            $indexTable,
            [],
            false
        );

        $connection->query($query);
        $connection->delete($table);

        return $this;
    }

    /**
     * Retrieve temporary index table name
     *
     * @param  string $table
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdxTable($table = null)
    {
        return $this->tableStrategy->getTableName('cgi_catalog_product_index_price');
    }
}
