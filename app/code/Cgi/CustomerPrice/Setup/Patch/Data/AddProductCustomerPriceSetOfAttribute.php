<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddProductCustomerPriceAttribute
 * @package Cgi\CustomerPrice\Setup\Patch\Data
 */
class AddProductCustomerPriceSetOfAttribute implements DataPatchInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * AddProductCustomerPriceAttribute constructor.
     * @param EavSetup $eavSetup
     */
    public function __construct(
        EavSetup $eavSetup
    )
    {
        $this->eavSetup = $eavSetup;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $eavSetup = $this->eavSetup;

        $eavSetup->addAttribute(
            Product::ENTITY,
            'customer_price',
            [
                'type' => 'text',
                'label' => 'Customer Price',
                'input' => 'text',
                'backend' => JsonEncoded::class,
                'required' => false,
                'is_configurable' => false,
                'sort_order' => 87,
                'visible' => true,
                'system' => false,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to' => 'simple',
                'used_in_product_listing' => true,
                'user_defined' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false
            ]
        );

        $entityTypeId = $eavSetup->getEntityTypeId('catalog_product'); /* get entity type id so that attribute are only assigned to catalog_product */
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Product customer price', 200);
            $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Product customer price');
            // Add existing attribute to group
            $attributeId = $eavSetup->getAttributeId($entityTypeId, 'customer_price');
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);
        }
    }
}
