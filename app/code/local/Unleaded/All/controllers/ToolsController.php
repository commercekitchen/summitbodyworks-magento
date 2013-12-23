<?php
class Unleaded_All_ToolsController extends Mage_Core_Controller_Front_Action
{
	public function templateAction()
	{
		$store = Mage::app()->getStore();
		$scopeCode = $store->getCode();
		$scopeId = $store->getId();
		$back = $this->getRequest()->getParam('back');
		$config = Mage::getModel('core/config');
		$hints = Mage::getStoreConfigFlag(Mage_Core_Block_Template::XML_PATH_DEBUG_TEMPLATE_HINTS, $scopeId);
		$blocks = Mage::getStoreConfigFlag(Mage_Core_Block_Template::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS, $scopeId);

		if($hints){
			$config->saveConfig(Mage_Core_Block_Template::XML_PATH_DEBUG_TEMPLATE_HINTS, '0', $scopeCode, $scopeId);
			$config->saveConfig(Mage_Core_Block_Template::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS, '0', $scopeCode, $scopeId);
		}else{
			$ips = explode(',', Mage::getStoreConfig('dev/restrict/allow_ips', $scopeId));
			$ip = Mage::helper('core/http')->getRemoteAddr();
			if(!in_array($ip, $ips)){
				$ips[] = $ip;
			}
			$config->saveConfig('dev/restrict/allow_ips', implode(',', $ips), $scopeCode, $scopeId);
			$config->saveConfig(Mage_Core_Block_Template::XML_PATH_DEBUG_TEMPLATE_HINTS, '1', $scopeCode, $scopeId);
			$config->saveConfig(Mage_Core_Block_Template::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS, '1', $scopeCode, $scopeId);
		}
		$this->_redirectUrl($back);
	}

	public function cacheAction()
	{
		$back = $this->getRequest()->getParam('back');
		Mage::app()->getCacheInstance()->flush();
		$this->_redirectUrl($back);
	}

	public function reindexAction()
	{
		$back = $this->getRequest()->getParam('back');
		$process = $this->getRequest()->getParam('process');
		if($process == 'all'){
			$indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
			foreach($indexingProcesses as $proc){
				$proc->reindexEverything();
			}
		}else{
			$proc = Mage::getSingleton('index/indexer')->getProcessByCode($process);
			$proc->reindexEverything();
		}
		$this->_redirectUrl($back);
	}

	public function fixAdminImagesAction()
	{
		$result = Mage::getModel('ulall/tools')->fixImages();
		$font = "<link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>";
		$this->getResponse()->setBody($font . '<div style="font-family: Roboto; font-size: 14px;">' . $result . '</div>');
	}

	public function fixIndexAction()
	{
		$result = Mage::getModel('ulall/tools')->fixIndex();
		$font = "<link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>";
		$this->getResponse()->setBody($font . '<div style="font-family: Roboto; font-size: 14px;">' . $result . '</div>');
	}
}