<?php
ini_set('display_errors', 1);
include_once 'app/Mage.php';
Mage::setIsDeveloperMode(true);
Mage::app();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$fp = fopen('./media/core_config_data.csv', 'r');

while($row = fgetcsv($fp)){
	$path = $row[3];
	$val = $row[4];
	Mage::getModel('core/config')->saveConfig($path, $val);
}

fclose($fp);
?>