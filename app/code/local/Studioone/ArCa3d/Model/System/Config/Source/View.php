<?php
class Studioone_ArCa3d_Model_System_Config_Source_View {
	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray() {



		$entityType = Mage::getModel('catalog/product')->getResource()->getTypeId();
		$attributeSetCollection = 
		Mage::getResourceModel('eav/entity_attribute_set_collection') 
		 -> load();
		$attributeset = array();
		foreach ($attributeSetCollection as $id => $attributeSet) {
			$entityTypeId = $attributeSet -> getEntityTypeId();
			if($entityType == $id || $entityType == $entityTypeId)
			{
				$name = $attributeSet -> getAttributeSetName();
				
				$attributeset[] = array('value' => $id, 'label' => $name);
			}
			

		}
		return $attributeset;
	}

	/**
	 * Get options in "key-value" format
	 *
	 * @return array
	 */
	public function toArray() {
		return array(0 => Mage::helper('adminhtml') -> __('Data1'), 1 => Mage::helper('adminhtml') -> __('Data2'), 3 => Mage::helper('adminhtml') -> __('Data3'), );
	}

}
?>