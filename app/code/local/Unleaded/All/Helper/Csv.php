<?php
class Unleaded_All_Helper_Csv extends Mage_Core_Helper_Abstract
{
	protected $_basePath;

	protected $_tableA;

	protected $_tableB;

	protected $_mapA = array();

	protected $_mapB = array();

	protected $_delimiter;

	protected $_enclosure;

	public function __construct()
	{
		$this->_basePath = Mage::getBaseDir() . 'var' . DS . 'import' . DS;
	}

	public function linkTables($tableA, $tableB, $delim = ',', $enclosure = '"')
	{
		$this->_delimiter = $delim;
		$this->_enclosure = $enclosure;
		$tableA = array_pop(explode('/', $tableA));
		$tableB = array_pop(explode('/', $tableB));

		if(!$this->_tableA = fopen($this->_basePath . $tableA, 'r')){
			Mage::throwException(sprintf('Unable to open %s for reading. Please make sure file exists in %s', $tableA, $this->_basePath));
		}

		if(!$this->_tableB = fopen($this->_basePath . $tableB, 'r')){
			Mage::throwException(sprintf('Unable to open %s for reading. Please make sure file exists in %s', $tableB, $this->_basePath));
		}

		$this->_mapTables();
	}

	public function getLinkedValue($var, $link)
	{

	}

	public function getJoinedTable($link, $download = false)
	{
		$link = explode('=', $link);
		$linkA = trim($link[0]);
		$linkB = trim($link[1]);

		foreach($this->_mapA as $row){
			
		}
	}

	protected function _mapTables()
	{
		$headerA = fgetcsv($this->_tableA, 0, $this->_delimiter, $this->_enclosure);
		while($row = fgetcsv($this->_tableA, 0, $this->_delimiter, $this->_enclosure)){
			$item = array_combine($headerA, $row);
			$this->_mapA[] = $item;
		}

		$headerB = fgetcsv($this->_tableB, 0, $this->_delimiter, $this->_enclosure);
		while($row = fgetcsv($this->_tableB, 0, $this->_delimiter, $this->_enclosure)){
			$item = array_combine($headerB, $row);
			$this->_mapB[] = $item;
		}
	}
}