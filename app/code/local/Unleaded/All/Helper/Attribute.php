<?php
class Unleaded_All_Helper_Attribute extends Mage_Core_Helper_Abstract
{
	protected $_attributes = array();

	/**
	 * Instantiate new instance of an attribute
	 *
	 * @param string $type Attribute type, one of Unleaded_All_Model_Attribute::CUSTOMER or Unleaded_All_Model_Attribute::ADDRESS
	 * @param string $title The attribute label
	 * @param string $code The new attribute code
	 * @return Unleaded_All_Model_Attribute
	 */
	public function addAttribute($type, $title, $code)
	{
		$this->_setup = Mage::getModel('eav/entity_setup', 'core_setup');
		$attribute = Mage::getModel('ulall/attribute')->getAttribute($type, $title, $code);
		$this->_attributes[] = $attribute;
		return $attribute;
	}

	/**
	 * After modifying a new attribute returned from addAttribute(),
	 * save the new attribute to the database and add it to any forms
	 *
	 * @return Unleaded_All_Helper_Attribute
	 */
	public function save()
	{
		foreach($this->_attributes as $attr){
			$attr->saveAttribute();
		}
	}

	/**
	 * Retrieve the option id in a select/multiselect
	 * attribute given the options text value, return false on error
	 *
	 * @param string $value
	 * @param string $attrCode The attribute code to search for the option
	 * @param int $store OPTIONAL Store ID to match the option value against
	 * @return int|boolean
	 */
	public function getOptionIdByValue($value, $attrCode, $store = 0)
	{
		return Mage::getModel('ulall/attribute')->getOptionIdByValue($value, $attrCode, $store);
	}

	/**
	 * Check if product has a provided attribute group
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @param string $name The group name to search for
	 * @return bool
	 */
	public function hasAttributeGroup(Mage_Catalog_Model_Product $product, $name)
	{
		foreach($product->getAttributes() as $attr){
			$groupId = $attr->getData('attribute_group_id');
			$group = Mage::getModel('eav/entity_attribute_group')->load($groupId);
			if($group->getAttributeGroupName() == $name){
				return true;
			}
		}
		return false;
	}

	/**
	 * Retrieve a collection of attributes within a defined
	 * attribute group
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @param string $name The group name to fetch attributes from
	 * @return array
	 */
	public function getGroupAttributes(Mage_Catalog_Model_Product $product, $name)
	{
		$attrs = array();
		foreach($product->getAttributes() as $attr){
			$groupId = $attr->getAttributeGroupId();
			$group = Mage::getModel('eav/entity_attribute_group')->load($groupId);
			if($group->getAttributeGroupName() == $name){
				$attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection');
				$attributesCollection->setAttributeGroupFilter($groupId);
				foreach($attributesCollection as $attribute){
					if($attribute->getFrontendInput() == 'select'){
						$attribute->setData('value', $product->getAttributeText($attribute->getAttributeCode()));
					}else{
						$attribute->setData('value', $product->getData($attribute->getAttributeCode()));
					}
					$attrs[] = $attribute;
				}
				break;
			}
		}
		return $attrs;
	}

	/**
	 * Retrieve a collection of attributes within
	 * a defined attribute set
	 *
	 * @param string $name The attribute set name
	 * @return array
	 */
	public function getSetAttributes($name)
	{
		$entityType = Mage::getModel('catalog/product')->getResource()->getEntityType();
		$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
			->setEntityTypeFilter($entityType->getId());

		if($collection->getSize()){
			$setId = $collection->getFirstItem()->getAttributeSetId();
			$attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection');
			$attributesCollection->setAttributeSetFilter($setId);
			return $attributesCollection;
		}
		return array();
	}

	/**
	 * Retrieve the options of a select/multiselect
	 * attribute sorted against "position" in the
	 * direction specified
	 *
	 * @param string $attrCode The attribute code to fetch options from
	 * @param string $dir OPTIONAL sort direction, default is ascending
	 * @return Varien_Data_Collection
	 */
	public function getAttributeOptionsSorted($attrCode, $dir = Zend_Db_Select::SQL_ASC)
	{
		$attribute = Mage::getModel('eav/entity_attribute')->load($attrCode, 'attribute_code');
		$options = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter($attribute->getId())
			->setStoreFilter()
			->setPositionOrder($dir);

		return $options;
	}
}