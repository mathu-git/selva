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

use Cgi\CustomerPrice\Model\CacheIdentifier;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ChangeCacheIdentifier
 *
 * @package Cgi\CustomerPrice\Plugin\Cache
 */
class ChangeCacheIdentifier
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
     * Change cache if customer is assigned with price
     *
     * @param Context $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     */
    public function afterGetData(Context $subject, $result)
    {
        return $this->cacheIdentifier->addCustomerData($result);
    }
}
