<?php
class Studioone_ArCa3d_ProcessingController extends Mage_Core_Controller_Front_Action {

	protected $_message;
	/**
	 * Order instance
	 */
	protected $_order;

	/**
	 *  Get order
	 *
	 *  @return	  Mage_Sales_Model_Order
	 */
	public function getOrder() {
		if ($this -> _order == null) {
		}
		return $this -> _order;
	}

	/**
	 * Send expire header to ajax response
	 *
	 */
	protected function _expireAjax() {
		if (!Mage::getSingleton('checkout/session') -> getQuote() -> hasItems()) {
			$this -> getResponse() -> setHeader('HTTP/1.1', '403 Session Expired');
			exit ;
		}
	}

	public function errorAction() 
	{
	
		$this -> loadLayout() 
		-> getLayout() 
		-> getBlock('head') 
		-> setTitle($this -> __('Arca Payment Error')) 
		;
	
	}

	public function indexAction()
	{
		$checkout = Mage::getSingleton('checkout/session');
		$lastOrderId = $checkout->getLastOrderId();
		$transaction = Mage::getSingleton('core/session')->getArca3dTransaction ();
		$Mdorder = $transaction->getMdorder();
		 
		
		
		
		$this -> loadLayout() 
		-> _initLayoutMessages('checkout/session') 
		-> _initLayoutMessages('catalog/session') 
		-> getLayout() 
		-> getBlock('head') 
		-> setTitle($this -> __('Arca Payment')) 
		-> getLayout() 
		-> getBlock('form.payment')-> setMdorder($Mdorder) ;

		$this -> renderLayout();
	}
	public function successAction() 
	{
		$this -> loadLayout() 
		-> _initLayoutMessages('checkout/session') 
		-> _initLayoutMessages('catalog/session') 
		-> getLayout() 
		-> getBlock('head') 
		-> setTitle($this -> __('Arca Payment Success')) 
		-> getLayout() 
		-> getBlock('arca.success')->setMessage(Mage::helper('core')->__("Payment Success")) ;
		

		$this -> renderLayout();
	}
	public function resultAction() 
	{
	
			$Interface  =   Mage::getModel('arca3d/Interface');
			
			$mdorder = Mage::app()->getRequest()->getParam('MDORDER');
			$answer = Mage::app()->getRequest()->getParam('ANSWER');
			$model = Mage::getModel('arca3d/transactions');
			$transaction = $model->load($mdorder,'mdorder');
			$transaction-> setStatus('completed');
			$transaction->save();
			$order_id = $transaction->getOrderId();
			$order = Mage::getModel("sales/order") -> load($order_id);
			$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
			$comment = Mage::helper('core') -> __('Order %s payed with arca',$order_id);
			$isCustomerNotified = true;
			$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, "Payed with arca", $comment, $isCustomerNotified);
			$order->save();
			$this->_redirect('*/*/success', array('order_id'=>$order_id) );
		
	}

	

}
