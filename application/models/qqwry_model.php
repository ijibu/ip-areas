<?php
/**
 * desc模型
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Qqwry_Model extends MY_Model 
{
	/**
	 * Specify the primary table to execute queries on
	 *
	 * @var string
	 */
	protected $primary_table = 'qqwry';
	
	/**
	 * Set the primary key for the table
	 *
	 * @var string
	 */
	protected $primary_key = 'ip';
	
	public function __construct() 
	{
		parent::__construct();
	}
	
	
}