<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Main extends MY_Controller {

	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * ��ʾ����IP��ѯ�ӿ�ҳ��
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
	 * ��������IP���ַ��������е�startIp��ַ��
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
	 * ���ݽ�����΢��ip��ѯ�ӿڷ��ص�������⡣
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
	 * ��¼��ȡIP��ַ�������־
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