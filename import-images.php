<?php
include_once 'app/Mage.php';
Mage::setIsDeveloperMode(true);
Mage::app();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$baseDir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product';
$api = Mage::getModel('catalog/product_attribute_media_api');

		/*
$products = Mage::getModel('catalog/product')->getCollection();
foreach($products as $product){
	$images = $api->items($product->getId());
	$hashes = array();
	foreach($images as $image){
		$api->remove($product->getId(), $image['file']);
		$hash = hash_file('md5', $baseDir . $image['file']);
		Zend_Debug::dump($hash);
		if(in_array($hash, $hashes)){
			Zend_Debug::dump('Duplicate: ' . $product->getSku());
			$api->remove($product->getId(), $image['file']);
		}else{
			$hashes[] = $hash;
		}
	}
}
		*/

$fp = fopen('./media/images.csv', 'r');

$lastSku = null;
$product = null;
$hashesThumb = array();
$hashesBase = array();

while($row = fgetcsv($fp)){
	$sku = $row[0];
	$thumb = trim($row[1]);
	$base = trim($row[2]);
	if(trim($sku) != ''){
		$lastSku = trim($sku);
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $lastSku);
		$hashesThumb = array();
		$hashesBase = array();
	}

	if($product && $product->getId()){
		if($thumb != ''){
			if(file_exists($baseDir . $thumb)){
				$hash = hash_file('md5', $baseDir . $thumb);
				if(!in_array($hash, $hashesThumb)){
					$product->addImageToMediaGallery($baseDir . $thumb, array('thumbnail'), false, false);
					$product->save();
					Zend_Debug::dump('Added image to: ' . $product->getSku());
					$hashesThumb[] = $hash;
				}
			}else{
				Zend_Debug::dump('File does not exist: ' . $baseDir . $thumb);
			}
		}
		if($base != ''){
			if(file_exists($baseDir . $base)){
				$hash = hash_file('md5', $baseDir . $base);
				if(!in_array($hash, $hashesBase)){
					$product->addImageToMediaGallery($baseDir . $base, array('small_image', 'image'), false, false);
					$product->save();
					Zend_Debug::dump('Added image to: ' . $product->getSku());
					$hashesBase[] = $hash;
				}
			}else{
				Zend_Debug::dump('File does not exist: ' . $baseDir . $base);
			}
		}
	}else{
		Zend_Debug::dump('Cannot find product: ' . $lastSku);
	}
}

fclose($fp);
?>