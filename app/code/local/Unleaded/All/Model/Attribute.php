<?php
class Unleaded_All_Model_Attribute extends Mage_Core_Model_Abstract
{
	const CATEGORY	= 'category';
	const CUSTOMER 	= 'customer';
	const ADDRESS 	= 'customer_address';
	const QUOTE 	= 'quote';
	const ORDER 	= 'order';
	const PRODUCT	= 'product';

	const TYPE_DATE 	= 'date';
	const TYPE_TEXT 	= 'text';
	const TYPE_SELECT 	= 'select';
	const TYPE_YESNO	= 'yesno';
	const TYPE_HIDDEN 	= 'hidden';
	const TYPE_TEXTAREA	= 'textarea';

	const FORM_ADMIN_CHECKOUT					= 'adminhtml_checkout';
	const FORM_ADMIN_CUSTOMER					= 'adminhtml_customer';
	const FORM_ADMIN_ADDRESS					= 'adminhtml_customer_address';
	const FORM_CHECKOUT_REGISTER				= 'checkout_register';
	const FORM_CHECKOUT_REGISTER_GUEST			= 'checkout_onepage_register_guest';
	const FORM_CHECKOUT_BILLING_ADDRESS			= 'checkout_onepage_billing_address';
	const FORM_CHECKOUT_SHIPPING_ADDRESS		= 'checkout_onepage_shipping_address';
	const FORM_CHECKOUT_MULTISHIPPING_REGISTER 	= 'checkout_multishipping_register';
	const FORM_CUSTOMER_ACCOUNT_CREATE			= 'customer_account_create';
	const FORM_CUSTOMER_ACCOUNT_EDIT			= 'customer_account_edit';
	const FORM_CUSTOMER_ADDRESS_EDIT			= 'customer_address_edit';
	const FORM_CUSTOMER_REGISTER_ADDRESS		= 'customer_register_address';

	const VALIDATE_ALPHANUMERIC = 'alphanumeric';
	const VALIDATE_NUMERIC 		= 'numeric';
	const VALIDATE_ALPHA 		= 'alpha';
	const VALIDATE_EMAIL 		= 'email';
	const VALIDATE_URL 			= 'url';
	const VALIDATE_DATE 		= 'date';

	protected $_attribute;

	protected $_code;

	protected $_type;

	protected $_entity = array();

	protected $_validation = array();

	protected $_forms = array();

	/**
	 * Instantiate new instance of an attribute
	 *
	 * @param string $type Attribute type, one of self::CUSTOMER or self::ADDRESS
	 * @param string $title The attribute label
	 * @param string $code The new attribute code
	 * @return Unleaded_All_Model_Attribute
	 */
	public function getAttribute($type, $title, $code)
	{
		$this->_type = $type;
		$this->_code = $code;
		$entity = $this->_getEntity($type);
		if($entity){
			$this->_entity = $entity;
			$this->_attribute = $this->_getDefaultAttribute($title);
		}
		return $this;
	}

	/**
	 * Called after the creation of the attribute.
	 * Adds the new attribute to any of the forms defined using addForm()
	 *
	 * @return Unleaded_All_Model_Attribute
	 */
	public function addForms()
	{
		if(count($this->_forms)){
			$attr = Mage::getSingleton('eav/config')->getAttribute($this->_type, $this->_code);
			$attr->setData('used_in_forms', $this->_forms);
			$attr->save();
		}
	}

	/**
	 * Build the attribute, return the array format of the attribute entity
	 * Returns false on error
	 *
	 * @return array|boolean
	 */
	public function saveAttribute()
	{
		if($this->_attribute && count($this->_entity)){
			$this->_attribute = $this->_attribute->getData();
			if(count($this->_validation)){
				$this->_attribute['validate_rules'] = serialize($this->_validation);
			}
			reset($this->_entity);
			$key = key($this->_entity);
			$this->_entity[$key]['attributes'][$this->_code] = $this->_attribute;

			switch($this->_type){
				case self::CUSTOMER :
				case self::ADDRESS :
				case self::CATEGORY :
					$setup = Mage::getModel('eav/entity_setup', 'core_setup');
					$setup->installEntities($this->_entity);
					$this->addForms();
					break;
				case self::ORDER :
				case self::QUOTE :
					$setup = Mage::getResourceModel('sales/setup', 'sales_setup');
					foreach($this->_entity[$key]['attributes'] as $code => $attr){
						$attr['grid'] = true;
						$setup->addAttribute($this->_type, $code, $attr);
					}
					break;
			}
		}
		return $this;
	}

	/**
	 * Retrieve the default attribute definition,
	 * any property of the attribute can be modified after the fact
	 *
	 * @param string $title The new attribute label
	 * @return Varien_Object
	 */
	protected function _getDefaultAttribute($title)
	{
		return new Varien_Object(array(
			'type' 			=> 'varchar',
			'label' 		=> $title,
			'input' 		=> 'text',
			'required' 		=> false,
			'sort_order' 	=> 150,
			'visible' 		=> false,
			'system' 		=> false,
			'position' 		=> 150,
			'user_defined'	=> true,
			'global'		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
		));
	}

	/**
	 * Retrieve the entity definition provided an attribute type,
	 * returns false if the type specified is not one of the available options
	 *
	 * @param string $type
	 * @return array|boolean
	 */
	protected function _getEntity($type)
	{
		switch($type){
			case self::CUSTOMER :
				return array(
					'customer' => array(
						'entity_model' 					=> 'customer/customer',
						'attribute_model' 				=> 'customer/attribute',
						'table' 						=> 'customer/entity',
						'increment_model' 				=> 'eav/entity_increment_numeric',
						'additional_attribute_table' 	=> 'customer/eav_attribute',
						'entity_attribute_collection' 	=> 'customer/attribute_collection',
						'attributes' 					=> array()
					)
				);
			case self::ADDRESS :
				return array(
					'customer_address' => array(
						'entity_model'                 => 'customer/address',
						'attribute_model'              => 'customer/attribute',
						'table'                        => 'customer/address_entity',
						'additional_attribute_table'   => 'customer/eav_attribute',
						'entity_attribute_collection'  => 'customer/address_attribute_collection',
						'attributes'                   => array()
					)
				);
			case self::QUOTE :
				return array(
					'quote' => array(
						'entity_model' 	=> 'sales/quote',
						'table' 		=> 'sales/quote',
						'attributes' 	=> array()
					)
				);
			case self::ORDER :
				return array(
					'order' => array(
						'entity_model'          => 'sales/order',
						'table'                 => 'sales/order',
						'increment_model'       => 'eav/entity_increment_numeric',
						'increment_per_store'   => true,
						'backend_prefix'        => 'sales_entity/order_attribute_backend',
						'attributes' 			=> array()
					)
				);
			case self::CATEGORY :
				return array(
					'catalog_category' => array(
						'entity_model'					=> 'catalog/category',
						'attribute_model'				=> 'catalog/resource_eav_attribute',
						'table'							=> 'catalog/category',
						'additional_attribute_table'	=> 'catalog/eav_attribute',
						'entity_attribute_collection'	=> 'catalog/category_attribute_collection',
						'default_group'					=> 'General Information',
						'attributes'					=> array()
					)
				);
			case self::PRODUCT :
				return array(
					'catalog_product' => array(
						'entity_model'                   => 'catalog/product',
						'attribute_model'                => 'catalog/resource_eav_attribute',
						'table'                          => 'catalog/product',
						'additional_attribute_table'     => 'catalog/eav_attribute',
						'entity_attribute_collection'    => 'catalog/product_attribute_collection',
						'attributes'                     => array()
					)
				);
		}
		return false;
	}

	/**
	 * Add key/value pair to the validation rules, prior to adding
	 * the attribute validation rules are serialized and saved to the attribute
	 *
	 * @param string $name
	 * @param string $val
	 * @return Unleaded_All_Model_Attribute
	 */
	protected function _addValidation($name, $val)
	{
		$this->_validation[$name] = $val;
		return $this;
	}

	/**
	 * Add the attribute to one of the available forms on
	 * either the frontend or backend of the site
	 *
	 * @param string $form Any one of the constants above prefixed with FORM_
	 * @return Unleaded_All_Model_Attribute
	 */
	public function addForm($form)
	{
		$forms = array(
			'adminhtml_checkout',
			'adminhtml_customer',
			'adminhtml_customer_address',
			'checkout_register',
			'checkout_onepage_register_guest',
			'checkout_onepage_billing_address',
			'checkout_onepage_shipping_address',
			'checkout_multishipping_register',
			'customer_account_create',
			'customer_account_edit',
			'customer_address_edit',
			'customer_register_address'
		);

		if(in_array($form, $forms)){
			$this->_forms[] = $form;
			$this->_forms = array_unique($this->_forms);
		}
		return $this;
	}

	/**
	 * Set the options of the new attribute. Also automatically converts
	 * the attribute input type to "select" in preparation for the options
	 *
	 * @param array $options An array of option labels. Keys are ignored.
	 * @return Unleaded_All_Model_Attribute
	 */
	public function setOptions(array $options)
	{
		$this->setOption(array('values' => $options))
			 ->setInput('select')
			 ->setType('int')
			 ->setSource('eav/entity_attribute_source_table');
		return $this;
	}

	/**
	 * Shortcut method to set the attribute type to any number of input types
	 *
	 * @param string $type Any one of the above constants prefixed with TYPE_
	 * @return Unleaded_All_Model_Attribute
	 */
	public function setAttributeType($type)
	{
		switch($type){
			case self::TYPE_TEXT :
				$this->setInput('text')
					 ->setType('varchar');
				break;
			case self::TYPE_TEXTAREA :
				$this->setInput('textarea')
					 ->setType('text');
				break;
			case self::TYPE_DATE :
				$this->setInput('date')
					 ->setType('datetime');
				break;
			case self::TYPE_SELECT :
				$this->setInput('select')
					 ->setType('int')
					 ->setSource('eav/entity_attribute_source_table');
				break;
			case self::TYPE_YESNO :
				$this->setInput('select')
					 ->setType('int')
					 ->setSource('eav/entity_attribute_source_boolean');
				break;
			case self::TYPE_HIDDEN :
				$this->setInput('hidden')
					 ->setType('varchar');
				break;
			case self::TYPE_MULTILINE :
				$this->setInput('text')
					 ->setType('multiline')
					 ->setBackend('customer/entity_address_attribute_backend_street')
					 ->setMultilineCount(2);
				break;
		}
		return $this;
	}

	/**
	 * Set the order of the new attribute in forms
	 * Shortcut method, simultaneously sets the position and sort order
	 *
	 * @param int $position
	 * @return Unleaded_All_Model_Attribute
	 */
	public function setOrder($position)
	{
		return $this->setPosition($position)->setSortOrder($position);
	}

	/**
	 * Add a minimum length to the text input field
	 *
	 * @param int $val The minimum length of the new text field attribute
	 * @return Unleaded_All_Model_Attribute
	 */
	public function setMinLength($val)
	{
		return $this->_addValidation('min_text_length', $val);
	}

	/**
	 * Add a maximum length to the text input field
	 *
	 * @param int $val The maximum length of the text field attribute
	 * @return Unleaded_All_Model_Attribute
	 */
	public function setMaxLength($val)
	{
		return $this->_addValidation('max_text_length', $val);
	}

	/**
	 * Add input validation to the text input field
	 *
	 * @param string $type Any one of the above constants prefixed with VALIDATE_
	 * @return Unleaded_All_Model_Attribute
	 */
	public function addInputValidation($type)
	{
		$types = array(
			'alphanumeric',
			'numeric',
			'alpha',
			'email',
			'url',
			'date'
		);

		if(in_array($type, $types)){
			$this->_addValidation('input_validation', $type);
		}
		return $this;
	}

	/**
	 * Magic method to call getters/setters on the attribute data
	 *
	 * @param string $name
	 * @param string $val
	 * @return Unleaded_All_Model_Attribute
	 */
	public function __call($name, $val)
	{
		try
		{
			call_user_func_array(array($this->_attribute, $name), $val);
		}catch(Exception $e){
			Mage::logException($e);
		}
		return $this;
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
	public function getOptionIdByValue($value, $attrCode, $store)
	{
		$resource = Mage::getSingleton('core/resource');
		$adapter = $resource->getConnection('core_read');
		$bind = array('*');
		$type = Mage::getModel('eav/config')->getEntityType('catalog_product');
		$attr = Mage::getModel('eav/entity_attribute');
		Mage::getResourceModel('eav/entity_attribute')->loadByCode($attr, $type->getEntityTypeId(), $attrCode);

		if(!$attr->getId()){
			return false;
		}

		$optionIds = array();
		$options = $adapter->select()
			->from($resource->getTableName('eav/attribute_option'))
			->where('attribute_id = ?', $attr->getAttributeId());

		$rows = $adapter->fetchAll($options, $bind);
		foreach($rows as $row){
			$optionIds[] = $row['option_id'];
		}

		$select = $adapter->select()
			->from($resource->getTableName('eav/attribute_option_value'))
			->where('value = ?', $value)
			->where('option_id IN (?)', $optionIds)
			->where('store_id = ?', $store);

		$optionId = $adapter->fetchRow($select, $bind);

		if($optionId){
			return $optionId['option_id'];
		}
		return false;
	}
}