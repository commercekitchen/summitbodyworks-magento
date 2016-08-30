<?php
class Unleaded_All_Model_Profiler extends Mage_Core_Model_Abstract
{
	public function saveProfiler($request){
		if(Mage::getStoreConfig('dev/debug/profiler')){
			$timers = Varien_Profiler::getTimers();
			$outLines = '';
			foreach ($timers as $name => $timer){
				$sum = Varien_Profiler::fetch($name,'sum');
				$count = Varien_Profiler::fetch($name,'count');
				$realmem = Varien_Profiler::fetch($name,'realmem');
				$emalloc = Varien_Profiler::fetch($name,'emalloc');

				if ($sum<.1 && $count<500 && $emalloc<3000000) {
					continue;
				}

				$outLines .= str_pad($name,110)."\t "
					.	number_format($sum,4)." \t "
					.	str_pad($count,5)." \t "
					.	str_pad(number_format($emalloc),12)." \t "
					.	str_pad(number_format($realmem),12)."\r\n";
			}

			if($outLines != ''){
				$date = date('Ymd');
				$fname = Mage::getBaseDir('var') . DS . 'log' . DS . 'profile_' . $date . '.log';
				$fp = fopen($fname,'a');

				$line = str_pad("-",165,"-") . "\r\n";

				$ua = Mage::helper('core/http')->getHttpUserAgent();
				$ip = Mage::helper('core/http')->getRemoteAddr(false);
				$url = $request->getOriginalPathInfo();
				$route = $request->getPathInfo();

				$output  = $line . $line . $line;
				$output .= " $url | $route \r\n";
				$output .= $line;
				$output .= " $ua | $ip \r\n";
				$output .= $line;
				$output .= " " . date('Y-m-d H:i:s') . ' | Memory usage: real: '.memory_get_usage(true).', emalloc: '.memory_get_usage(). "\r\n";
				$output .= $line;
				$output .= str_pad('Code Profiler',110) . " \t RunTime \t Count \t Emalloc \t RealMem\r\n";
				$output .= $line;
				$output .= $outLines;
				$output .= $line;

				$sqlRpt = Varien_Profiler::getSqlProfiler(Mage::getSingleton('core/resource')->getConnection('core_write'));
				if($sqlRpt){
					$queries = explode('<br>', $sqlRpt);
					for($i=0;$i<count($queries)-2;$i++){
						$output .= $queries[$i] . "\r\n" . $line;
					}

					$lqParts = explode(":",$queries[count($queries)-3]);
					$lqTime = trim($lqParts[1]);

					if($lqTime > .07){
						$output .= substr($queries[count($queries)-1],0,2500);
						$output .= "\r\n" . $line;
					}
				}

				fwrite($fp,$output);
				fclose($fp);
			}
		}
	}
}