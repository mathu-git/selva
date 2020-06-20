<?php

namespace Cgi\CustomerPrice\Model\Source;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options tree for "Customer" field
 */
class Customer implements OptionSourceInterface
{
    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $customerTree;

    /**
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory,
        RequestInterface $request
    )
    {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getCustomerTree();
    }

    /**
     * Retrieve customer tree
     *
     * @return array
     */
    protected function getCustomerTree()
    {
        if ($this->customerTree === null) {
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
            $this->customerTree = $customerById;
        }
        return $this->customerTree;
    }
}
