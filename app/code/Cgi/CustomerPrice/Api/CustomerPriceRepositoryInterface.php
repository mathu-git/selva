<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Api;

use Cgi\CustomerPrice\Api\Data\CustomerPriceInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CustomerPriceRepositoryInterface
 *
 * @package Cgi\CustomerPrice\Api
 */
interface CustomerPriceRepositoryInterface
{
    /**
     * Save Customer Price
     *
     * @param CustomerPriceInterface $price       Customer price
     * @param bool                   $saveOptions Save or not
     *
     * @return CustomerPriceInterface
     */
    public function save(CustomerPriceInterface $price, $saveOptions = false);

    /**
     * Delete Customer Price
     *
     * @param CustomerPriceInterface $price Customer price
     *
     * @return bool return true if deleted
     */
    public function delete(CustomerPriceInterface $price);

    /**
     * Retrieve customer price matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria   Search criteria
     * @param bool                    $returnRawObjects Return objects or not
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $returnRawObjects = false);
}
