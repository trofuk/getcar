<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Auction\Controller\Index;

use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostBidderName extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProductHelper;

    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var CustomerExtractor */
    protected $customerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerExtractor $customerExtractor
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        CustomerExtractor $customerExtractor
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerExtractor = $customerExtractor;
        $this->_catalogProductHelper = $catalogProductHelper;
        parent::__construct($context);
    }

    /**
     * Change customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $customerId = $this->session->getCustomerId();
        $currentCustomer = $this->customerRepository->getById($customerId);
        $currentCustomer->setCustomAttribute('bidder_name',$this->getRequest()->getParam('bidder_name'));

        try {
            $this->customerRepository->save($currentCustomer);
        } catch (AuthenticationException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (InputException $e) {
            $this->messageManager->addException($e, __('Invalid input'));
        } catch (\Exception $e) {
            $message = __('We can\'t save the customer.')
                . $e->getMessage()
                . '<pre>' . $e->getTraceAsString() . '</pre>';
            $this->messageManager->addException($e, $message);
        }
        $this->messageManager->addSuccess(__('You created a bidder name.'));
        $productUrl = $this->_catalogProductHelper->getProductUrl($this->getRequest()->getParam('product_id'));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($productUrl);
        return $resultRedirect;
    }
}
