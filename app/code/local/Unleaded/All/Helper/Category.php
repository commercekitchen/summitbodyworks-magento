<?php
class Unleaded_All_Helper_Category extends Mage_Catalog_Helper_Category
{
	/**
	 * Check if one category is a subcategory of another
	 * A category is not considered a child if the child/parent ids match
	 *
	 * @param int|Mage_Catalog_Model_Category $child
	 * @param int|Mage_Catalog_Model_Category $parent
	 * @param int Optional, child must be within X number of levels from parent
	 * @param int|Mage_Core_Model_Store $store Optional store id or store object from which to retrieve the root category id
	 * @return boolean
	 */
	public function isChildOf($child, $parent, $level = null, $store = null)
	{
		if(is_int($child)){
			$child = Mage::getModel('catalog/category')->load($child);
		}

		if($parent instanceof Mage_Catalog_Model_Category){
			$parent = $parent->getId();
		}

		if($child->getId() && $parent){
			if($child->getId() == $parent){
				return false;
			}

			$root = $this->_getRootCategoryId($store);
			$iterations = 0;
			$i = 0;
			while($child->getId() != $root){
				if($level != null && ($iterations++ > $level)){
					break;
				}
				if($child->getId() == $parent){
					return true;
				}
				$child = $child->getParentCategory();

				if($i++ >= 100){
					// If we've iterated more than 100 times,
					// exit as we're probably stuck in the loop
					break;
				}
			}
		}
		return false;
	}

	/**
	 * Check if one category is a direct subcategory of another
	 *
	 * @param int|Mage_Catalog_Model_Category $child
	 * @param int|Mage_Catalog_Model_Category $parent
	 * @param int|Mage_Core_Model_Store $store Optional store id or store object from which to retrieve the root category id
	 * @return boolean
	 */
	public function isDirectChildOf($child, $parent, $store = null)
	{
		return $this->isChildOf($child, $parent, 1, $store);
	}

	/**
	 * Check if two categories are siblings of each other (exist on the same "level")
	 *
	 * @param int|Mage_Catalog_Model_Category $source
	 * @param int|Mage_Catalog_Model_Category $target
	 * @return boolean
	 */
	public function isSiblingOf($source, $target)
	{
		if(is_int($source)){
			$source = Mage::getModel('catalog/category')->load($source);
		}

		if(is_int($target)){
			$target = Mage::getModel('catalog/category')->load($target);
		}

		if($source->getId() && $target->getId()){
			$sourcePath = explode('/', $source->getPath());
			$targetPath = explode('/', $target->getPath());

			array_pop($sourcePath);
			array_pop($targetPath);

			return (implode('/', $sourcePath) == implode('/', $targetPath));
		}
		return false;
	}

	/**
	 * Creates a category tree using xpath syntax ("/Men's/Jackets/Winter")
	 * Works similarly to unix command "mkdir -p" in that it will not duplicate categories,
	 * if a category already exists, subcategories are automatically appended
	 *
	 * @param string $path The category path, e.g. - "/Men's/Jackets & Vests/Fleece Jackets"
	 * @param int $active
	 * @param int $anchor
	 * @param int|Mage_Core_Model_Store $store The store id from which to base the root category off of
	 */
	public function createCategory($path, $active = 1, $anchor = 0, $store = null)
	{
		$path = ltrim($path, '/');
		$names = explode('/', $path);
		$parent = $this->_getRootCategoryId($store);

		foreach($names as $name){
			$name = trim($name);
			if($name != ''){
				$current = $this->_getChildCategoryByName($name, $parent);
				if(!$current){
					$urlKey = $this->_getUrlKey($name);
					$category = Mage::getModel('catalog/category')
						->setName($name)
						->setUrlKey($urlKey)
						->setIsActive($active)
						->setIsAnchor($anchor)
						->setDisplayMode('PRODUCTS');

					$parentCategory = Mage::getModel('catalog/category')->load($parent);
					$category->setPath($parentCategory->getPath());
					$category->save();
					$parent = $category->getId();
				}else{
					$parent = $current->getId();
				}
			}else{
				break;
			}
		}
		return $this;
	}

	/**
	 * Fetch a category by name, optional to limit the search
	 * to a specific category "depth" (number of levels from the stores root category)
	 *
	 * @param string $name
	 * @param int $depth Optional depth at which to limit the search to
	 * @param int|Mage_Core_Model_Store $store
	 * @return Mage_Catalog_Model_Category
	 */
	public function getCategoryByName($name, $depth = 0, $store = null)
	{
		$categories = Mage::getModel('catalog/category')->getCollection()
			->addAttributeToSelect(array('name'))
			->addAttributeToFilter('name', array('eq' => $name));

		if($depth > 0){
			$categories->addAttributeToFilter('level', array('lteq' => 1+$depth));
		}

		if(!is_null($store)){
			$storeId = $this->_getRootCategoryId($store);
			$categories->addAttributeToFilter('path', array('like' => '1/' . $storeId . '/%'));
		}

		if($categories->getSize()){
			return $categories->getFirstItem();
		}
		return false;
	}

	/**
	 * Provided a category name, will strip out invalid characters
	 * and return a valid url key for the category
	 *
	 * @param string $name
	 * @return string
	 */
	protected function _getUrlKey($name)
	{
		$name = strtolower($name);
		$name = str_replace('\'', '', $name);
		return preg_replace('/[^a-z0-9]+/', '-', $name);
	}

	/**
	 * Search for an immediate child category with a given name,
	 * returns false if the category could not be found
	 *
	 * @param string $name The category name to search for
	 * @param int $parent The parent category id
	 * @return Mage_Catalog_Model_Category|boolean
	 */
	protected function _getChildCategoryByName($name, $parent)
	{
		$source = Mage::getModel('catalog/category');
		foreach($source->getCategories($parent, 0, true, true) as $category){
			if($category->getName() == $name){
				return $category;
			}
		}
		return false;
	}

	/**
	 * Retrieve current stores root category id
	 *
	 * @param int|Mage_Core_Model_Store $store Optional store id or store object from which to retrieve the root category id
	 * @return int
	 */
	protected function _getRootCategoryId($store = null)
	{
		if($store instanceof Mage_Core_Model_Store){
			return $store->getRootCategoryId();
		}

		if(is_int($store)){
			$store = Mage::getModel('core/store')->load($store);
			if($store->getId()){
				return $store->getRootCategoryId();
			}
		}

		return Mage::app()->getStore()->getRootCategoryId();
	}
}