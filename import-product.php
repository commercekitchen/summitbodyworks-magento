<?php
include_once 'app/Mage.php';
Mage::setIsDeveloperMode(true);
Mage::app();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$fp = fopen('./media/sbw.csv', 'r');

while($row = fgetcsv($fp)){
	$sku = trim($row[0]);
	$cost = trim($row[1]);
	$price = trim($row[2]);
	$weight = trim($row[3]);

	$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

	if($product && $product->getId()){
		/*
		if(is_numeric($cost)){
			$product->setCost($cost);
		}

		if(is_numeric($price)){
			$product->setPrice($price);
		}

		if(is_numeric($weight)){
			$product->setWeight($weight);
		}else{
			$product->setWeight(0);
		}
		*/

		if($product->getWeight() == 0){
			Zend_Debug::dump('Updated: ' . $sku);
		}

		//$product->save();
	}else{
		Zend_Debug::dump('Cannot find product: ' . $sku);
	}
}

fclose($fp);
?>