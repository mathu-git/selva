<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Plugin\Cache;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\AbstractBlock;
use Cgi\CustomerPrice\Model\CacheIdentifier;

/**
 * Class ChangeWidgetCacheIdentifier
 *
 * @package Cgi\CustomerPrice\Plugin\Cache
 */
class ChangeWidgetCacheIdentifier
{
    /**
     * @var CacheIdentifier
     */
    protected $cacheIdentifier;

    /**
     * ChangeCacheIdentifier constructor.
     *
     * @param CacheIdentifier $cacheIdentifier
     */
    public function __construct(
        CacheIdentifier $cacheIdentifier
    ) {
        $this->cacheIdentifier = $cacheIdentifier;
    }

    /**
     * Change widget cache if customer is assigned with price
     *
     * @param AbstractBlock $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     */
    public function afterGetCacheKeyInfo(AbstractBlock $subject, $result)
    {
        return $this->cacheIdentifier->addCustomerData($result);
    }
}
