<?php

/**
 * 基础控制器
 * 
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class MY_Model extends CI_Model 
{
	protected $table = '';
	
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * 保存信息
	 */
	protected function _save($data)
	{
		$data = var_export($data, true);
		file_put_contents("data/{$this->table}.php", "<?php \r\nreturn $data;");
	}
	
	/**
	 * 生成ID值
	 */
	protected function _generateId()
	{
		return mt_rand(100000, 999999);
	}
}