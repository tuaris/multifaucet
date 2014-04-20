<?php
//Basic Settings Object


class Settings{
	public $config = array();

	public function __construct(&$_config){
		$this->config = $_config;
	}

	public function get($key){
		return isset($this->config[$key]) ? $this->config[$key] : '';
	}
}

?>