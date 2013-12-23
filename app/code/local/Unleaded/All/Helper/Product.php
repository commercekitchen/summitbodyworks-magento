<?php
class Unleaded_All_Helper_Product extends Mage_Catalog_Helper_Product
{
	const SEARCH_BOTH = 0;
	const SEARCH_VALUE = 1;
	const SEARCH_LABEL = 2;

	/**
	 * Retrieve the parent (configurable) product of
	 * a given simple product or product id. If no parent is
	 * found, the original product is returned, even if an id was given
	 *
	 * @param int|Mage_Catalog_Model_Product $productId
	 * @return Mage_Catalog_Model_Product
	 */
	public function getParent($productId)
	{
		if($productId instanceof Mage_Catalog_Model_Product){
			$productId = $productId->getId();
		}
		$parents = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($productId);
		if(count($parents)){
			$productId = $parents[0];
		}
		return Mage::getModel('catalog/product')->load($productId);
	}

	/**
	 * Retrieve a simple product of a configurable based
	 * on a given set of key/value pairs, wherein keys are the
	 * attribute codes and values are either the option label or value
	 *
	 * @param int|Mage_Catalog_Model_Product $productId The configurable product id/object from which to search for simples
	 * @param array $options
	 * @param int $search Restrict the attribute matching to the attribute label, value, or both
	 * @param boolean $strict Use strict matching when comparing labels
	 * @return boolean|Mage_Catalog_Model_Product
	 */
	public function getProductByOptions($productId, array $options, $search = self::SEARCH_BOTH, $strict = false)
	{
		if($productId instanceof Mage_Catalog_Model_Product){
			$productId = $productId->getId();
		}

		$children = Mage::getResourceSingleton('catalog/product_type_configurable')->getChildrenIds($productId);
		if(count($children)){
			foreach($children[0] as $childId){
				$match = true;
				$child = Mage::getModel('catalog/product')->load($childId);
				foreach($options as $key => $val){
					if($search == self::SEARCH_LABEL){
						if($strict){
							if(!($child->getAttributeText($key) == $val)){
								$match = false;
								break;
							}
						}else{
							if(!(strtolower($child->getAttributeText($key)) == strtolower($val))){
								$match = false;
								break;
							}
						}
					}elseif($search == self::SEARCH_VALUE){
						if(!($child->getData($key) == $val)){
							$match = false;
							break;
						}
					}else{
						if($strict){
							if(!($child->getData($key) == $val || $child->getAttributeText($key) == $val)){
								$match = false;
								break;
							}
						}else{
							if(!($child->getData($key) == $val || strtolower($child->getAttributeText($key)) == strtolower($val))){
								$match = false;
								break;
							}
						}
					}
				}

				if($match){
					return $child;
				}
			}
		}
		return false;
	}
}