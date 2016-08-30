<?php
class Unleaded_All_Helper_Ee extends Mage_Core_Helper_Abstract
{
	public function inc($template,$ee_path = '/'){
		try{
			$isSecure = Mage::app()->getStore()->isCurrentlySecure();
			$currentUrl = Mage::getUrl('',array('_secure'=>$isSecure));
			$parts = parse_url($currentUrl);

			$requestUrl = $parts['scheme'] . '://' . $parts['host'] . $ee_path . $template;
			$result =  $this->getContent($requestUrl);
			if(!$result || $result == ''){
				throw new Exception('Bad or empty response');
			} else {
				return $result;
			}
		} catch (Exception $e){
			$url='http://127.0.0.1' . $ee_path . $template;
			return $this->getContent($url);
		}
	}

	protected function getContent($url){
		$curl = Mage::helper('ulall/curl');
		$curl->setOption(CURLOPT_TIMEOUT,15);
		$curl->setOption(CURLOPT_CONNECTTIMEOUT,10);
		$response = $curl->call($url,Zend_Http_Client::GET);
		return $response;
	}
}
