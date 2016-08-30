<?php
class Unleaded_All_Model_Tools extends Mage_Core_Model_Abstract
{
	public function fixIndex()
	{
		$message = '';
		$resource = Mage::getSingleton('core/resource');
		$adapter = $resource->getConnection('core_read');
		$bind = array('*');

		$multiselectIds = array();
		$multiselect = $adapter->select()
			->from($resource->getTableName('eav/attribute'))
			->where('frontend_input = ?', 'multiselect');

		$rows = $adapter->fetchAll($multiselect, $bind);
		foreach($rows as $row){
			$multiselectIds[] = $row['attribute_id'];
		}

		$varchar = $resource->getTableName('catalog_product_entity_varchar');
		$select = $adapter->select('value')
			->from($varchar)
			->where('attribute_id IN (?)', $multiselectIds)
			->where('value LIKE ?', '%,%');

		$all = $adapter->fetchAll($select);
		foreach($all as $record){
			$oldIds = $record['value'];
			$ids = explode(',', $oldIds);
			$ids = array_unique($ids);
			$ids = implode(',', $ids);
			if($oldIds != $ids){
				$data = array('value' => $ids);
				$where = 'value_id = ' . $record['value_id'];
				$adapter->update($varchar, $data, $where);
				$message .= sprintf('<p>Updated %s record # %s: "%s" > "%s"</p>', $varchar, $record['value_id'], $oldIds, $ids);
			}
		}

		if($message == ''){
			$message = 'Nothing to fix, everything seems ok here.';
		}

		return $message;
	}

	public function fixImages()
	{
		$products = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('sku');

		foreach($products as $product){
			try
			{
				$productId = $product->getId();
				$this->_updateMissingImages($count, $productId);
				$message .= $count . ' > Success:: While Updating Images with ID (' . $productId . ').<br />';
			}catch(Exception $e){
				$message .=  $count .' > Error:: While Upating Images with ID (' . $productId . ') => '.$e->getMessage().'<br />';
			}
			$count++;
		}
		return $message;
	}

	protected function _checkIfRowExists($productId, $attributeId, $value){
		$tableName  = $this->_getTableName('catalog_product_entity_media_gallery');
		$connection = $this->_getConnection('core_read');
		$sql        = "SELECT COUNT(*) AS count_no FROM " . $this->_getTableName($tableName) . " WHERE entity_id = ? AND attribute_id = ?  AND value = ?";
		$count      = $connection->fetchOne($sql, array($productId, $attributeId, $value));
		if($count > 0){
			return true;
		}else{
			return false;
		}
	}

	protected function _insertRow($productId, $attributeId, $value){
		$connection             = $this->_getConnection('core_write');
		$tableName              = $this->_getTableName('catalog_product_entity_media_gallery');

		$sql = "INSERT INTO " . $tableName . " (attribute_id, entity_id, value) VALUES (?, ?, ?)";
		$connection->query($sql, array($attributeId, $productId, $value));
	}

	protected function _updateMissingImages($count, $productId){
		$connection             = $this->_getConnection('core_read');
		$smallImageId           = $this->_getAttributeId('small_image');
		$imageId                = $this->_getAttributeId('image');
		$thumbnailId            = $this->_getAttributeId('thumbnail');
		$mediaGalleryId         = $this->_getAttributeId('media_gallery');

		//getting small, base, thumbnail images from catalog_product_entity_varchar for a product
		$sql    = "SELECT * FROM " . $this->_getTableName('catalog_product_entity_varchar') . " WHERE attribute_id IN (?, ?, ?) AND entity_id = ? AND `value` != 'no_selection'";
		$rows   = $connection->fetchAll($sql, array($imageId, $smallImageId, $thumbnailId, $productId));
		if(!empty($rows)){
			foreach($rows as $_image){
				//check if that images exist in catalog_product_entity_media_gallery table or not
				if(!$this->_checkIfRowExists($productId, $mediaGalleryId, $_image['value'])){
					//insert that image in catalog_product_entity_media_gallery if it doesn't exist
					$this->_insertRow($productId, $mediaGalleryId, $_image['value']);
					/* Output / Logs */
					$missingImageUpdates = $count . ' > Updated:: $productId=' . $productId . ', $image=' . $_image['value'];
					echo $missingImageUpdates.'<br />';
				}
			}
			$separator = str_repeat('=', 100);
			echo $separator . '<br />';
		}
	}

	protected function _getConnection($type = 'core_read'){
		return Mage::getSingleton('core/resource')->getConnection($type);
	}

	protected function _getTableName($tableName){
		return Mage::getSingleton('core/resource')->getTableName($tableName);
	}

	protected function _getAttributeId($attribute_code){
		$connection = $this->_getConnection('core_read');
		$sql = "SELECT attribute_id
					FROM " . $this->_getTableName('eav_attribute') . "
				WHERE
					entity_type_id = ?
					AND attribute_code = ?";
		$entity_type_id = $this->_getEntityTypeId();
		return $connection->fetchOne($sql, array($entity_type_id, $attribute_code));
	}

	protected function _getEntityTypeId($entity_type_code = 'catalog_product'){
		$connection = $this->_getConnection('core_read');
		$sql        = "SELECT entity_type_id FROM " . $this->_getTableName('eav_entity_type') . " WHERE entity_type_code = ?";
		return $connection->fetchOne($sql, array($entity_type_code));
	}
}