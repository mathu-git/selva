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

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\LocalizedException;
use Cgi\CustomerPrice\Model\ResourceModel\CustomerPrice as ResourceCustomerPrice;

/**
 * Class CacheIdentifier
 *
 * @package Cgi\CustomerPrice\Model
 */
class CacheIdentifier
{
    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var ResourceCustomerPrice
     */
    protected $resourceCustomerPrice;

    /**
     * @var int|null
     */
    protected $customerIdWithPrice = null;

    /**
     * CacheIdentifier constructor.
     *
     * @param SessionFactory $sessionFactory
     * @param ResourceCustomerPrice $resourceCustomerPrice
     */
    public function __construct(
        SessionFactory $sessionFactory,
        ResourceCustomerPrice $resourceCustomerPrice
    ) {
        $this->resourceCustomerPrice = $resourceCustomerPrice;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * Add if customer is assigned with price
     *
     * @param array $result
     * @return array
     * @throws LocalizedException
     */
    public function addCustomerData($result)
    {
        $customerId = '';
        $customerSession = $this->sessionFactory->create();

        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getId();
        }

        if (is_null($this->customerIdWithPrice)) {
            if (!$customerId) {
                return $result;
            }
            if ($this->resourceCustomerPrice->hasAssignCustomer($customerId)) {
                $this->customerIdWithPrice = $customerId;
            }
        }

        if (!empty($result) && !is_null($this->customerIdWithPrice)) {
            $result['cgi_customer_id'] = $customerId;
        }

        return $result;
    }
}
