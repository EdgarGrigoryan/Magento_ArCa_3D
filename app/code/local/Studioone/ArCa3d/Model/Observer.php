<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Studioone
 * @package    Studioone_ArCa3d
 * @copyright  Copyright (c) 2012 Skrill Holdings Ltd. (http://www.skrill.com)
 */

class Studioone_ArCa3d_Model_Observer {

	public function sales_order_place_after($observer) 
	{
		try
		{
			
			$order = $observer->getEvent()->getOrder(); 
			$customer = Mage::getSingleton('customer/session') -> getCustomer();
			$model = Mage::getModel('arca3d/transactions');
			 
			$model -> setCustomerId($customer -> getId());
			$model -> setStoreId(Mage::app() -> getStore() -> getId());
			$model -> setOrderId($order -> getId());
			$model -> setCreateDate(date('Y-m-d H:i:s'));
			$model -> setUpdateDate(date('Y-m-d H:i:s'));
			$model -> setTotal($order -> getGrandTotal());
			$model -> setStatus('pendding');
			$model -> save();
			
			
			
			$order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true)->save();
			
			
			Mage::log('Id:'.$model -> getId() , null, __CLASS__ . '.log');
			Mage::log('StoreId:'.$model -> getStoreId() , null, __CLASS__ . '.log');
			Mage::log('OrderId:'.$model -> getOrderId() , null, __CLASS__ . '.log');
			Mage::log('Total:'.$model -> getTotal() , null, __CLASS__ . '.log');
			Mage::log('Status:'.$model -> getStatus() , null, __CLASS__ . '.log');
			
			
			
			
			
			$Interface  =   Mage::getModel('arca3d/Interface');
			if(($isRegistred = $Interface->Merchant2Rbs($model))!= false)
			{
			
				$Interface->createInvoce($model); 
				Mage::getSingleton('core/session')->setArca3dTransaction($model);
			} 
			 
			
		  }
		  catch(Exception $e)
		  {
			var_dump($e);
			
		  }
	}

	public function hookOrderSave($observer) {
		$order = $observer->getEvent()->getOrder();
		Mage::log(__FUNCTION__, null, __CLASS__ . '.log');
	}
	
	public function saveOrderAfterSubmit($observer) {

		Mage::log(__FUNCTION__, null, __CLASS__ . '.log');
		return $this;
	}

	public function hookOrderSaveBefore($observer) {
		
		$arg_list = func_get_args();
		Mage::log(__FUNCTION__ . count($arg_list), null, __CLASS__   . '.log');
		
		// return $this;
	}

	public function isDeveloper(Varien_Event_Observer $observer) {

		$event = $observer -> getEvent();
		$method = $event -> getMethodInstance();
		$result = $event -> getResult();
		
		if ('arca3d' == $method -> getCode()) 
		{

			$isTestMode = Mage::getStoreConfig('payment/arca/testmode');
			
			$isDeveloper = (strstr(Mage::getStoreConfig('dev/restrict/allow_ips'), Mage::helper('core/http') -> getRemoteAddr())) ? true : false;

			Mage::log(__FUNCTION__ . "\t isDeveloper \t" . ($isDeveloper ? 'Yes' : 'No') . "\t isTestMode " . $isTestMode . "\t" . $method -> getCode() . "\t" . Mage::getStoreConfig('dev/restrict/allow_ips'), null, __CLASS__ . '.log');

			if ($isTestMode == '1' && !$isDeveloper) {
				$observer -> getEvent() -> getResult() -> isAvailable = false;
			}

		}

	}

	public function isArcaOnly(Varien_Event_Observer $observer) {

		$event = $observer -> getEvent();
		$method = $event -> getMethodInstance();
		$result = $event -> getResult();

		if ('arca' == $method -> getCode()) {
			$attribute_set = Mage::getStoreConfig('payment/arca/attribute_set');
			$attribute_sets = explode(',', $attribute_set);
			Mage::log(__FUNCTION__ . "\t" . $attribute_set . $method -> getCode(), null, __CLASS__ . '.log');

			if (empty($attribute_sets)) {
				return;
			}
			$arca = true;

			foreach (Mage::getSingleton('checkout/cart')->getQuote()->getAllVisibleItems() as $item) {
				if (!in_array($item -> getProduct() -> getAttributeSetId(), $attribute_sets)) {
					$observer -> getEvent() -> getResult() -> isAvailable = false;
					return;
				}
			}

		}
	}

	public function isMethodActive(Varien_Event_Observer $observer) {

		$event = $observer -> getEvent();
		$method = $event -> getMethodInstance();
		$result = $event -> getResult();

		$isTestMode = Mage::getStoreConfig('payment/arca/testmode');

		Mage::log(Mage::getStoreConfig('dev/restrict/allow_ips'), null, __CLASS__ . __FUNCTION__ . '.log');

		$isDeveloper = (strstr(Mage::getStoreConfig('dev/restrict/allow_ips'), Mage::helper('core/http') -> getRemoteAddr())) ? true : false;

		$isMethodActiove = ($isTestMode == '1' && !$isDeveloper) ? 'NO' : 'Yes';

		Mage::log('$isMethodActiove=' . $isMethodActiove, null, __CLASS__ . __FUNCTION__ . '.log');

		if ($isTestMode == '1' && !$isDeveloper) {
			return false;
		}

		return $this -> arcaOnly($observer);

	}

	public function arcaOnly(Varien_Event_Observer $observer) {
		$event = $observer -> getEvent();
		$method = $event -> getMethodInstance();
		$result = $event -> getResult();
		$cardonly = true;

		foreach (Mage::getSingleton('checkout/cart')->getQuote()->getAllVisibleItems() as $item) {
			if (!$item -> getProduct() -> getArcaOnly()) {
				$cardonly = false;
			}
		}

		if ($method -> getCode() !== "arca" && $cardonly) {
			$result -> isAvailable = false;
		}

	}

}
