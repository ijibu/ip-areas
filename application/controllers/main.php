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
		
		/**
		 * ===================================================对ip进行去重。===========================================
		 */
		$ips = array();
		foreach ($arrConts as $arr) {
			//根据文件格式，把连续出现的空格替换成',',方便获取出IP段的开始和结束IP
			$arr = preg_replace('/\s{1,20}/', ',', $arr);
			$row = explode(',', $arr);
			$long0 = ip2long($row[0]);
			$long1 = ip2long($row[1]);
			
			$ips[$long0] = $row[0];
				
			//将一个ip段分成两个ip段。
			if ($row[0] != $row[2]) {		//纯真IP库中存在startIp==endIp的情况，需要排除
				$ip = floor(($long1 + $long0) / 2);
				if ($ip > $long0) {
					$ips[$ip] = long2ip($ip);
				}
					
				$ips[$long1] = $row[1];
			}
		}
		$ips = implode("\n", $ips);
		error_log($ips, 3, APPPATH . 'cache/uniquestartip.ini');
		exit;
		
		
		/**
		 * =======================================没有对ip进行去重。=============================================
		 */
		$ips = '';
		foreach ($arrConts as $arr) {
			//根据文件格式，把连续出现的空格替换成',',方便获取出IP段的开始和结束IP
			$arr = preg_replace('/\s{1,20}/', ',', $arr);
			$row = explode(',', $arr);
			$ips .= $row[0] . "\n";
			
			//将一个ip段分成两个ip段。
			if ($row[0] != $row[2]) {		//纯真IP库中存在startIp==endIp的情况，需要排除
				$ip = floor((ip2long($row[1]) + ip2long($row[0])) / 2);
				if ($ip > ip2long($row[0])) {
					$ips .= long2ip($ip) . "\n";
				}
					
				$ips .= $row[1] . "\n";
			}
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
		$ip_file = APPPATH . 'cache/uniquestartip.ini';
		
		$conts = file_get_contents($ip_file);
		$arrConts = explode("\n", $conts);
		$sql = '';
		$error_ips = '';
		$createTime = time();
		$i = 0;
		$ipCount = count($arrConts);
		
		//优化，每次插入500条数据，增加插入速度。
		for (; $i < $ipCount; $i +=500) {
			$sql = "INSERT INTO qqwry(ip, createTime) VALUES ";
			$inserts = array();
			
			for ($j = 0; $j < 500; $j++) {
				if (isset($arrConts[$i + $j])) {
					$arr = $arrConts[$i + $j];
					$ip = ip2long(trim($arr));		//注意获取每一行出来，结尾有个空格，所有必须去除空格。否则ip2long会返回false;
					if ($ip !== false) {
						$inserts[] = "($ip, $createTime)";
					} else {
						$error_ips .= $arr . "\n";
					}
				} else {
					break;
				}
			}
			
			if ($inserts) {
				$sql .= implode(',', $inserts) . ";\r\n";
				error_log($sql, 3, APPPATH . 'cache/insertip1.sql');
			}
		}
		if ($error_ips) {
			error_log($error_ips, 3, APPPATH . 'cache/errors.sql');
		}
		
		exit;
		
		//每次插入一条数据。
		foreach ($arrConts as $key => $arr) {
			$ip = ip2long(trim($arr));		//注意获取每一行出来，结尾有个空格，所有必须去除空格。否则ip2long会返回false;
			if ($ip !== false) {
				$sql .= "INSERT INTO qqwry(ip, createTime) VALUES ($ip, $createTime);" . "\r\n";
			} else {
				$error_ips .= $arr . "\n";
			}
		}
		
		error_log($sql, 3, APPPATH . 'cache/insertip.sql');
		if ($error_ips) {
			error_log($error_ips, 3, APPPATH . 'cache/errors.sql');
		}
	}
	
	/**
	 * 分割去重处理后的IP地址到js文件，用于前段js去调用新浪接口。
	 */
	public function segmentIp2js()
	{
		$ip_file = APPPATH . 'cache/uniquestartip.ini';
		$perFileIpCount = 50000;		//每个js文件中的IP数量。
		
		$conts = file_get_contents($ip_file);
		$arrConts = explode("\n", $conts);
		$ipCount = count($arrConts);
		$index = 0;
		
		//优化，每次插入500条数据，增加插入速度。
		for ($i = 0; $i < $ipCount; $i +=$perFileIpCount) {
			$index++;
			$ips = "var ips = ['";
			$inserts = array();
			
			for ($j = 0; $j < $perFileIpCount; $j++) {
				if (isset($arrConts[$i + $j])) {
					$ip = trim($arrConts[$i + $j]);			//注意获取每一行出来，结尾有个空格，所有必须去除空格。否则ip2long会返回false;
					if ($ip !== false) {
						$inserts[] = "$ip";
					}
				} else {
					break;
				}
			}
			
			if ($inserts) {
				$ips .= implode("','", $inserts) . "'];\r\n";
				error_log($ips, 3, APPPATH . "cache/startip{$index}.js");
			}
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
			/**
			 * 1.首先判断该记录在IP段是否已经入库
			 * 2.已经入库，更新qqwry表中的记录为已同步。
			 * 3.没有入库，执行入库操作，操作成功，更新qqwry表中的记录为已同步。
			 * 	   操作失败，记录操作失败的日志。
			 */
			$ip = ip2long($result['ip']);
			
			$this->load->model('iparea_model');
			$this->load->model('qqwry_model');
			$ret1 = $this->iparea_model->get(array('startIp <=' => $ip, 'endIp >=' => $ip));
			if ($ret1) {		//查找到了记录
				$this->qqwry_model->update(array('ip' => $ip, 'status' => 2, 'modifyTime' => time()));
			} else {
				$row = array();
				$row['startIp'] = ip2long($result['start']);
				$row['endIp'] = ip2long($result['end']);
				$row['province'] = $result['province'];
				$row['city'] = $result['city'];
				$row['isp'] = $result['isp'];
				$row['country'] = $result['country'];
					
				$ret = $this->iparea_model->add($row);
				
				if ($ret) {
					$this->qqwry_model->update(array('ip' => $ip, 'status' => 2, 'modifyTime' => time()));
				}
			}
			
			echo $ip;
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