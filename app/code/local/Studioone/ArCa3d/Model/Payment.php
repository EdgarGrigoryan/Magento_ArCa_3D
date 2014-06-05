<?php
/**
 * Our test CC module adapter
 */
class Studioone_ArCa3d_Model_Payment extends Mage_Payment_Model_Method_Abstract {
	/**
	 * unique internal payment method identifier
	 *
	 * @var string [a-z0-9_]
	 */
	protected $_code = 'arca3d';
	protected $_formBlockType = 'arca3d/form_payment';
	protected $_infoBlockType = 'arca3d/info_payment';
	protected $_redirectUrl = '/arca3d/processing/error';
	/**
	 * Here are examples of flags that will determine functionality availability
	 * of this module to be used by frontend and backend.
	 *
	 * @see all flags and their defaults in Mage_Payment_Model_Method_Abstract
	 *
	 *      It is possible to have a custom dynamic logic by overloading
	 *      public function can* for each flag respectively
	 */

	/**
	 * Is this payment method a gateway (online auth/charge) ?
	 */
	protected $_isGateway = true;

	/**
	 * Can authorize online?
	 */
	protected $_canAuthorize = true;

	/**
	 * Can capture funds online?
	 */
	protected $_canCapture = true;

	/**
	 * Can capture partial amounts online?
	 */
	protected $_canCapturePartial = false;

	/**
	 * Can refund online?
	 */
	protected $_canRefund = false;

	/**
	 * Can void transactions online?
	 */
	protected $_canVoid = true;

	/**
	 * Can use this payment method in administration panel?
	 */
	protected $_canUseInternal = false;

	/**
	 * Can show this payment method as an option on checkout payment page?
	 */
	protected $_canUseCheckout = true;

	/**
	 * Is this payment method suitable for multi-shipping checkout?
	 */
	protected $_canUseForMultishipping = true;

	/**
	 * Can save credit card information for future processing?
	 */
	protected $_canSaveCc = false;
	protected $_config = null;
	/**
	 * Here you will need to implement authorize, capture and void public methods
	 *
	 * @see examples of transaction specific public methods such as
	 *      authorize, capture and void in Mage_Paygate_Model_Authorizenet
	 */
	public function assignData($data) {
		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}

		$this -> getInfoInstance() -> setPoNumber($data -> getPoNumber());
		return $this;
	}

	/**
	 * Return Order place redirect url
	 *
	 * @return string
	 */
	public function getOrderPlaceRedirectUrl() 
	{
		
		 
			
			$urlString = Mage::getUrl('arca3d/processing/index');
			$url = Mage::getSingleton('core/url') -> parseUrl($urlString);
			$path = $url -> getPath();
			return $path;
	}

}
?>