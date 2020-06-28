<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Model\ResourceModel\CustomerPrice;

use Cgi\CustomerPrice\Model\CustomerPrice as ModelCustomerPrice;
use Cgi\CustomerPrice\Model\ResourceModel\CustomerPrice;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Cgi\CustomerPrice\Model\ResourceModel\CustomerPrice
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ModelCustomerPrice::class, CustomerPrice::class);
    }
}
