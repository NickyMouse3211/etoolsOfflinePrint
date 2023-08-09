<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	START Core Helper        
*/

	function dd( $var, $exit = true )
	{
		$CI = &get_instance();
		echo '<pre>';
		if ( $var == 'lastdb' ){
			print_r($var);
			// print_r($CI->db->last_query());
		} else if ( $var == 'post' ){
			print_r($CI->input->post());
		} else if ( $var == 'get' ){
			print_r($CI->input->get());
		} else {
			print_r( $var );
		}
		echo '</pre>';

		if ( $exit )
		{
			exit();
		}
	}

	function staticValue($name,$value){
		$data['glblfet_type']['image'] 	= '1';
		$data['glblfet_type']['text'] 	= '2';
		$data['glblfet_type']['icon'] 	= '3';

		$data['status_request']['success'] 				= 200;
		$data['status_request']['unauthorized'] 		= 401;
		$data['status_request']['not_found'] 			= 404;
		$data['status_request']['locked'] 				= 423;
		$data['status_request']['not_acceptable'] 		= 406;
		$data['status_request']['error'] 				= 500;

		$data['discount_type']['discount'] 				= 1;
		$data['discount_type']['coupon'] 				= 2;

		$data['status']['aktif']						= 1;
		$data['status']['tidak_aktif']					= 0;
		$data['status']['dihapus']						= 99;

		$data['global']['true']						= 1;
		$data['global']['false']					= '0';
		$data['global']['trueboolean']				= true;
		$data['global']['falseboolean']				= false;

		$data['payment_status']['paid']						= 1;
		$data['payment_status']['unpaid']					= '0';

		return (@$data[$name][$value] != '' ? $data[$name][$value] : null );
	}
	
	function like_match($pattern, $subject)
	{
		$pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
		return (bool) preg_match("/^{$pattern}$/i", $subject);
	}

	function getLocalIP(){
		$ip_address  = '127.0.0.1';
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

		if($ip_address == '::1'){
			$ip_address = 'localhost';
		}

		return $ip_address;
	}
?>