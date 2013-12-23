<?php
class Unleaded_All_Model_Observer
{
	public function onPageLoad(Varien_Event_Observer $o)
	{
		$layout = Mage::app()->getLayout();
		$content = $layout->getBlock('content');
		if($content) {
			$tools = $layout->createBlock('ulall/tools')
				->setBlockId('ulall.tools')
				->setOutput('toHtml');
			$content->append($tools);
		}
	}

	public function onPostDispatch(Varien_Event_Observer $o){
		Mage::getModel('ulall/profiler')->saveProfiler($o->getControllerAction()->getRequest());
	}
}
