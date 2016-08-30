<?php
class Unleaded_All_Helper_Customer extends Mage_Core_Helper_Abstract
{
	/**
	 * Retrieve all customer groups as an array
	 * with a key/value format of group id/group name
	 *
	 * @return array
	 */
	public function getAllGroups()
	{
		$groupsArr = array(array('value' => 0, 'label' => Mage::helper('ulall')->__('NOT LOGGED IN')));
		$groupsArr = array_merge($groupsArr, Mage::getResourceModel('customer/group_collection')
			->setRealGroupsFilter()
			->load()
			->toOptionArray());

		$groups = array();

		foreach($groupsArr as $group){
			$groups[$group['value']] = $group['label'];
		}

		return $groups;
	}

	/**
	 * Retrieve the current label of the customer group
	 *
	 * @return string
	 */
	public function getCurrentGroupName()
	{
		$groups = $this->getAllGroups();
		$groupId = $this->_getCustomer()->getGroupId();
		return $groups[$groupId];
	}

	/**
	 * Retrieve the current customer object instance
	 *
	 * @return Mage_Customer_Model_Customer
	 */
	protected function _getCustomer()
	{
		return Mage::getSingleton('customer/session')->getCustomer();
	}
}