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

use Cgi\CustomerPrice\Api\CustomerPriceRepositoryInterface;
use Cgi\CustomerPrice\Api\Data\CustomerPriceInterface;
use Cgi\CustomerPrice\Model\ResourceModel\CustomerPrice as CustomerPriceResourceModel;
use Cgi\CustomerPrice\Model\ResourceModel\CustomerPrice\CollectionFactory as CustomerPriceCollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Class CustomerPriceRepository
 *
 * @package Cgi\CustomerPrice\Model
 */
class CustomerPriceRepository implements CustomerPriceRepositoryInterface
{
    /**
     * @var CustomerPriceCollectionFactory
     */
    protected $customerPriceCollectionFactory;

    /**
     * @var CustomerPriceResourceModel
     */
    protected $customerPriceResourceModel;

    /**
     * @var CustomerPriceFactory
     */
    protected $priceModelFactory;

    /**
     * @var CustomerPrice[]
     */
    protected $instances = [];

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * @var |null
     */
    protected $instancesById;

    /**
     * CustomerPriceRepository constructor.
     *
     * @param CustomerPriceCollectionFactory $customerPriceCollectionFactory
     * @param CustomerPriceResourceModel     $customerPriceResourceModel
     * @param CustomerPriceFactory           $priceModelFactory
     * @param SearchResultsInterfaceFactory  $searchResultsFactory
     * @param SearchCriteriaBuilder          $searchCriteriaBuilder
     * @param Serialize                      $serializer
     */
    public function __construct(
        CustomerPriceCollectionFactory $customerPriceCollectionFactory,
        CustomerPriceResourceModel $customerPriceResourceModel,
        CustomerPriceFactory $priceModelFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serialize $serializer
    ) {
        $this->customerPriceCollectionFactory = $customerPriceCollectionFactory;
        $this->customerPriceResourceModel = $customerPriceResourceModel;
        $this->priceModelFactory = $priceModelFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serializer = $serializer;
    }

    /**
     * Save customer price in custom table
     *
     * @param  CustomerPriceInterface $price
     * @param  bool                   $saveOptions
     * @return CustomerPriceInterface|void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function save(CustomerPriceInterface $price, $saveOptions = false)
    {
        try {
            unset($this->instances[$price->getId()]);
            $this->customerPriceResourceModel->save($price);
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $exception) {
            throw InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $price->getData($exception->getAttributeCode()),
                $exception
            );
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Unable to save Price'));
        }

        unset($this->instances[$price->getId()]);

        if (!$price->getId()) {
            return;
        } else {
            return $this->get($price->getId());
        }
    }

    /**
     * Get product customer price for customers
     *
     * @param  $customerPriceId
     * @param  bool $editMode
     * @param  null $storeId
     * @param  bool $forceReload
     * @return mixed|null
     * @throws NoSuchEntityException
     */
    public function get($customerPriceId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instances[$customerPriceId][$cacheKey]) || $forceReload) {
            $price = $this->priceModelFactory->create();
            if ($editMode) {
                $price->setData('_edit_mode', true);
            }
            $this->customerPriceResourceModel->load($price,$customerPriceId);
            if (!$price->getId()) {
                throw new NoSuchEntityException(__('Requested Record doesn\'t exist'));
            }
            $this->instances[$customerPriceId][$cacheKey] = $price;
        }

        return $this->instances[$customerPriceId][$cacheKey];
    }

    /**
     * Get cache key
     *
     * @param  array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }

        return sha1($this->serializer->serialize($serializeData));
    }

    /**
     * Load customer price data collection by given search criteria
     *
     * @param  SearchCriteriaInterface $criteria
     * @param  bool                    $returnRawObjects
     * @return SearchResultsInterface|ResourceRate\Collection
     */
    public function getList(SearchCriteriaInterface $criteria, $returnRawObjects = false)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->customerPriceCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter(
                    $filter->getField(),
                    [
                        $condition => $filter->getValue()
                    ]
                );
            }
        }

        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /**
             * @var SortOrder $sortOrder
             */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $items = $collection->load()->getItems();

        if (is_array($items)) {
            $searchResults->setItems($items);
        }

        return $searchResults;
    }

    /**
     * Delete product customer price for customers
     *
     * @param  CustomerPriceInterface $price
     * @return bool
     * @throws CouldNotSaveException
     * @throws StateException
     */
    public function delete(CustomerPriceInterface $price)
    {
        $priceId = $price->getId();
        try {
            unset($this->instances[$priceId]);
            $this->customerPriceResourceModel->delete($price);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (Exception $e) {
            throw new StateException(
                __('Unable to remove Prices')
            );
        }
        unset($this->instances[$priceId]);

        return true;
    }
}
