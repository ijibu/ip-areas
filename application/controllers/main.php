<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Main extends MY_Controller {

	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * 显示新浪IP查询接口页面
	 *
	 * @author ijibu.com@gmail.com
	 */
	public function index()
	{
		$this->output->enable_profiler(TRUE);
		$results = array();
		$step = intval($this->input->get_post('step'));
		if ($step <= 0) {
			$step = 1;
		}
		$results['step'] = $step;
		
		
		$this->load->view('index.php', $results);
	}
	
	/**
	 * 解析纯真IP库地址，获得所有的startIp地址。
	 * 
	 * @return void
	 * @author ijibu.com@gmail.com
	 */
	public function parseIpTxt()
	{
		ini_set("max_execution_time", 0);
	
		$ip_file = APPPATH . 'cache/ip.txt';
		
		$conts = file_get_contents($ip_file);
		$arrConts = explode("\n", $conts);
		foreach ($arrConts as $arr) {
			$row = explode(' ', $arr);
			error_log($row[0] . "\r\n", 3, APPPATH . 'cache/startip.ini');
		}
	}
	
	/**
	 * 根据将新浪微博ip查询接口返回的数据入库。
	 * 
	 * @author ijibu.com@gmail.com
	 */
	public function syncIpName()
	{
		$result = $_POST;
		if ($result['ret'] == 1) {
			$row = array();
			$row['startIp'] = ip2long($result['start']);
			$row['endIp'] = ip2long($result['end']);
			$row['province'] = $result['province'];
			$row['city'] = $result['city'];
			$row['isp'] = $result['isp'];
			$row['country'] = $result['country'];
			
			echo $this->db->insert('ip_area', $row, true);
		}
	}
	
	/**
	 * 记录获取IP地址错误的日志
	 *
	 * @return void
	 * @author liuhui05 at 2013-8-28
	 */
	public function getIpErrors() {
		$ipErrors = $this->input->get_post('errorIp');
		if ($ipErrors) {
			$file = 'iperrors.log';
			
			error_log($ipErrors . "\r\n", 3, $file);
		}
	}
}