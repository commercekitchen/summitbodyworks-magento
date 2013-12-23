<?php
class Unleaded_All_Block_Tools extends Mage_Core_Block_Template
{
	protected function _toHtml()
	{
		$ip = Mage::helper('core/http')->getRemoteAddr();
		$ips = explode(',', Mage::getStoreConfig('dev/restrict/allow_ips'));
		if(in_array($ip, $ips)){
			$buttonStyle = 'display: inline-block; color: #333; background: #ccc; padding: 5px;';
			$html  = '<div style="z-index: 9999; position: fixed; left: 0; top: 0">';

			$html .= '<div style="' . $buttonStyle . '">';
			$html .= '<form action="' . $this->getUrl('ulall/tools/template') . '">';
			$html .= '<input type="hidden" name="back" value="' . $this->_getCurrentUrl() . '" />';
			$html .= '<input style="padding: 3px;" type="submit" value="Toggle Path Hints" />';
			$html .= '</form>';
			$html .= '</div>';

			$html .= '<div style="' . $buttonStyle . '">';
			$html .= '<form action="' . $this->getUrl('ulall/tools/cache') . '">';
			$html .= '<input type="hidden" name="back" value="' . $this->_getCurrentUrl() . '" />';
			$html .= '<input style="padding: 3px;" type="submit" value="Clear Cache" />';
			$html .= '</form>';
			$html .= '</div>';

			$html .= '<div style="' . $buttonStyle . '">';
			$html .= '<form action="' . $this->getUrl('ulall/tools/reindex') . '">';
			$html .= '<input type="hidden" name="back" value="' . $this->_getCurrentUrl() . '" />';
			$html .= '<select name="process" style="padding: 3px; margin-right: 5px;">';
			$html .= '<option value="all">All Indexes</option>';
			$processes = Mage::getModel('index/indexer')->getProcessesCollection();
			foreach($processes as $process){
				$html .= '<option value="' . $process->getIndexerCode() . '" style="color: #fff; background: ' . $this->_getIndexStatusColor($process->getStatus()) . ';">' . $process->getIndexer()->getName() . '</option>';
			}
			$html .= '</select>';
			$html .= '<input style="padding: 3px;" type="submit" value="Reindex" />';
			$html .= '</form>';
			$html .= '</div>';

			$html .= '</div>';
			return $html;
		}
		return '';
	}

	protected function _getIndexStatusColor($status)
	{
		switch($status){
			case Mage_Index_Model_Process::STATUS_RUNNING :
				return '#F55600';
			case Mage_Index_Model_Process::STATUS_PENDING :
				return '#3CB861';
			case Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX :
				return '#E41101';
		}
		return '';
	}

	protected function _getCurrentUrl()
	{
		return Mage::helper('core/url')->getCurrentUrl();
	}
}