<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\CustomerPrice\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\RequestInterface;

/**
 * Class CustomerPrice
 *
 * @package Cgi\CustomerPrice\Ui\DataProvider\Product\Form\Modifier
 */
class CustomerPrice extends AbstractModifier
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * CustomerPrice constructor.
     *
     * @param RequestInterface $request
     * @param LocatorInterface $locator
     */
    public function __construct(
        RequestInterface $request,
        LocatorInterface $locator
    ) {
        $this->request = $request;
        $this->locator = $locator;
    }

    /**
     * Modify data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Modify meta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if (($this->getProductType() == "simple")) {
            $config = $meta['customer-prices']
            ['children']
            ['container_customer_price']
            ['children']
            ['customer_price']
            ['arguments']
            ['data']
            ['config'];
            $config['visible'] = '0';
            $config['formElement'] = 'hidden';

            $meta['customer-prices']
            ['children']
            ['container_customer_price']
            ['children']
            ['customer_price']
            ['arguments']
            ['data']
            ['config'] = $config;
            return $meta;
        }
        return $meta;
    }

    /**
     * Get product type
     *
     * @return null|string
     */
    protected function getProductType()
    {
        return (string)$this->request->getParam('type', $this->locator->getProduct()->getTypeId());
    }
}
