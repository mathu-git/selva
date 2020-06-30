<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Model\Source;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Customer
 *
 * @package Cgi\CustomerPrice\Model\Source
 */
class Customer implements OptionSourceInterface
{
    /**
     * Customer collection
     *
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * Request interface
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * List of customer names
     *
     * @var array
     */
    protected $customersList;

    /**
     * @param CustomerCollectionFactory $customerCollectionFactory Customer collection
     * @param RequestInterface          $request                   Request
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory,
        RequestInterface $request
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->request = $request;
    }

    /**
     * Customer names as options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getCustomersList();
    }

    /**
     * Retrieve customers list
     *
     * @return array
     */
    protected function getCustomersList()
    {
        if ($this->customersList === null) {
            $collection = $this->customerCollectionFactory->create();

            $collection->addNameToSelect();

            foreach ($collection as $customer) {
                $customerId = $customer->getEntityId();
                if (!isset($customerById[$customerId])) {
                    $customerById[$customerId] = [
                        'value' => $customerId
                    ];
                }
                $customerById[$customerId]['label'] = $customer->getName();
            }
            $this->customersList = $customerById;
        }
        return $this->customersList;
    }
}
