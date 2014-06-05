<?php

class Studioone_ArCa3d_Model_Interface  extends Mage_Payment_Model_Method_Abstract {

	// const MERCHANT2RBSURL = 'https://epay.arca.am/svpg/Merchant2Rbs';
	const MERCHANT2RBSURL = 'https://91.199.226.7:7002/svpg/Merchant2Rbs';
	public function Merchant2Rbs(&$transaction) {

		$urlString = Mage::getUrl('arca3d/processing/result');
		$url = Mage::getSingleton('core/url') -> parseUrl($urlString);
		$date = date('Ymd');
		$request['MERCHANTNUMBER'] = Mage::getStoreConfig('payment/arca3d/merchantnumber');
		$request['MERCHANTPASSWD'] = Mage::getStoreConfig('payment/arca3d/merchanpasswd');
		$request['ORDERNUMBER'] = date('dmy'). $transaction -> getId();
		$request['AMOUNT'] = $transaction -> getTotal() * 100;
		$request['BACKURL'] = $urlString;
		
		$request['$ORDERDESCRIPTION'] = $this->getOrderDerdescription($transaction);
		$request['LANGUAGE'] = 'EN';
		$request['MODE'] = 1;
		$request['DEPOSITFLAG'] = 0;
		Mage::log(print_r($request, 1), null, __CLASS__ . '.log');
		if (($MDORDER = $this -> makeRerquest($request)) != false) {
			if (strstr($MDORDER, '=null')) {
				throw new Exception($MDORDER);
			}

			$transaction -> setMdorder(trim($MDORDER));
			$transaction -> setStatus('registred');
			$transaction -> save();
			
			Mage::log($MDORDER, null, __CLASS__ . __FUNCTION__ . '.log');
		}

		return true;
	}

	public function getOrderDerdescription(&$transaction) 
	{
		$order_id = $transaction -> getOrderId();
		$order = Mage::getModel("sales/order") -> load($order_id);
		$items = $order->getAllItems();
		$return = array();
		foreach ($items as $itemId => $item)
        {
				$return [] = Mage::helper('sales') -> __("#%s\t%s\t%s\t%s\t%s",$itemId, $item->getName(),$item->getPrice(),$item->getQtyToInvoice(),$item->getPrice()*$item->getQtyToInvoice());
		}
		
		
		return implode("<br/>",$return );
		
	}
	public function createInvoce(&$transaction) 
	{
		$order_id = $transaction -> getOrderId();
		$order = Mage::getModel("sales/order") -> load($order_id);

		if (!$order -> canInvoice()) {
			Mage::throwException(Mage::helper('core') -> __('Cannot create an invoice.'));
		}
		$invoice = Mage::getModel('sales/service_order', $order) -> prepareInvoice();
		if (!$invoice -> getTotalQty()) {
			Mage::throwException(Mage::helper('core') -> __('Cannot create an invoice without products.'));
		}
		$invoice -> setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
		$invoice -> register();
		$transactionSave = Mage::getModel('core/resource_transaction') -> addObject($invoice) -> addObject($invoice -> getOrder());
		$transactionSave -> save();
		$transaction -> setInvoceId($invoice -> getId());

	}

	protected function makeRerquest($data) {
		$client = new Varien_Http_Client(self::MERCHANT2RBSURL);
		$client -> setMethod(Varien_Http_Client::POST);

		foreach ($data as $key => $value) {
			$client -> setParameterPost($key, $value);
		}

		$response = $client -> request();

		if ($response -> isSuccessful()) {

			$resp = $response -> getBody();
			Mage::log($resp, null, __CLASS__ . __FUNCTION__ . '.log');

			return $resp;
		} else {
			return false;
		}

	}

}

//$this->LOAD('Services_JSON');
?>
