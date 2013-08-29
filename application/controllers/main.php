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
		$ips = '';
		foreach ($arrConts as $arr) {
			//根据文件格式，把连续出现的空格替换成',',方便获取出IP段的开始和结束IP
			$arr = preg_replace('/\s{1,20}/', ',', $arr);
			$row = explode(',', $arr);
			$ips .= $row[0] . "\n";
			$ips .= $row[1] . "\n";
		}
		
		//这样比每一条都去写文件要快多了.
		error_log($ips, 3, APPPATH . 'cache/startip.ini');
	}
	
	/**
	 * 将所有的IP入库。
	 *
	 * @return void
	 * @author ijibu.com@gmail.com
	 */
	public function importIp()
	{
		$ip_file = APPPATH . 'cache/startip.ini';
		
		$conts = file_get_contents($ip_file);
		$arrConts = explode("\n", $conts);
		$sql = '';
		$error_ips = '';
		foreach ($arrConts as $key => $arr) {
			$ip = ip2long(trim($arr));		//注意获取每一行出来，结尾有个空格，所有必须去除空格。否则ip2long会返回false;
			if ($ip !== false) {
				$sql .= "INSERT INTO qqwry VALUES ($ip);" . "\r\n";
			} else {
				$error_ips .= $arr . "\n";
			}
		}
		
		error_log($sql, 3, APPPATH . 'cache/insertip.sql');
		error_log($error_ips, 3, APPPATH . 'cache/errors.sql');
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
			/**
			 * 1.首先判断该记录在IP段是否已经入库
			 * 2.已经入库，更新qqwry表中的记录为已同步。
			 * 3.没有入库，执行入库操作，操作成功，更新qqwry表中的记录为已同步。
			 * 	   操作失败，记录操作失败的日志。
			 */
			$ip = $result['ip'];
			
			
			
			$row = array();
			$row['startIp'] = ip2long($result['start']);
			$row['endIp'] = ip2long($result['end']);
			$row['province'] = $result['province'];
			$row['city'] = $result['city'];
			$row['isp'] = $result['isp'];
			$row['country'] = $result['country'];
			
			$ret = $this->db->insert('ip_area', $row, true);
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