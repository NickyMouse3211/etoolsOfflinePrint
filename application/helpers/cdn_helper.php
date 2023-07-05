<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	START Core Helper        
*/

# untuk print_f
function pre( $var, $exit = null )
{
	$CI = &get_instance();
	echo '<pre>';
	if (is_array($var)) {
		foreach ($var as $key => $value) {
			print_r($value);
		}		
	}else{
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
	}
	echo '</pre>';

	if ( $exit )
	{
		exit();
	}
}

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

function md5_mod($str, $salt){

	$str = md5(md5($str).$salt);
	return $str;
}

function strEncrypt($str, $forDB = FALSE, $keys = null){
	$CI =& get_instance();
	$key    = $CI->config->item('encryption_key');
	$key = (@$CI->session->userdata('userdata')->salt_code == '' ? $key : $CI->session->userdata('userdata')->salt_code );
	if ($keys != null) {
		$key = $keys;
	}
	
	// dd($key);
	$str    = ($forDB) ? 'md5(concat(\'' . $key . '\',' . $str . '))' : md5($key . $str);
	return $str;
}

function sencode($str){
	$CI =& get_instance();
	$key    = $CI->config->item('encryption_key');
	return salt($str , $key , 'encrypt');
}

function temporarySparator(){
	$CI 	=& get_instance();
	$key    = '001123'.$CI->config->item('encryption_key').'321100';
	return $key;
}

function sdecode($str){
	$CI =& get_instance();
	$key    = $CI->config->item('encryption_key');
	return salt($str , $key , 'decrypt');
}

function salt($str, $key, $status='encrypt'){
	$CI =& get_instance();
	$CI->encryption->initialize(
	        array(
	                'cipher' => 'aes-256',
	                'mode' => 'ctr',
	                'key' => $key
	        )
	);
	if ($status == 'encrypt') {
		$encrypted_string = $CI->encryption->encrypt($str);
	}elseif ($status == 'decrypt') {
		$encrypted_string = $CI->encryption->decrypt($str);
	}else{
		$encrypted_string = $str;
	}
	return $encrypted_string;
}

function encryptions( $string, $action = 'e', $salt = '' ) {
    // you may change these values to your own
	if ($salt == '') {
		$CI =& get_instance();
		$salt = @$CI->session->userdata('userdata')->salt_code;
	}
    $secret_key = $salt;
    $secret_iv = 'NickyMouse';

    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash( 'sha256', $secret_key );
    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

    if( $action == 'e' ) {
        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }
    else if( $action == 'd' ){
        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }

    return $output;
}

function strintime($date,$format='default'){
	$return = null;
	if ($format == 'd-m-Y') {
		$date = explode('-', $date);
		$return = strtotime(implode('-', array($date[2],$date[1],$date[0]))); 
	}elseif($format == 'Y-m-d H:i:s'){
		$thedate = explode(' ', $date);
		$date = explode('-', $thedate[0]);
		$time = $thedate[1];
		$return = strtotime(implode('-', array($date[2],$date[1],$date[0])).' '.$time);
	}elseif ($format == 'd/m/Y') {
		$date = explode('/', $date);
		$return = strtotime(implode('-', array($date[2],$date[1],$date[0]))); 
	}elseif($format == 'Y/m/d H:i:s'){
		$thedate = explode(' ', $date);
		$date = explode('/', $thedate[0]);
		$time = $thedate[1];
		$return = strtotime(implode('-', array($date[2],$date[1],$date[0])).' '.$time);
	}else{
		$return = strtotime($date);
	}
	return $return;
}


function set_session_login($username,$password,$encryptstat = 'unencrypted',$usefor="site",$oldsalt = null){
	$CI =& get_instance();
	if ($encryptstat == 'unencrypted') {
		# code...
	}
	$join = array(
				// array('role','user_role = role_code'),
				array('salt','user_id = salt_user_id and user_username = salt_username'),
			);
	$select_user = @$CI->m_global->get_data_all('user', $join, array('user_username' => $username, 'user_password' => $password))[0];
	if(empty($select_user)){
		return false;
	}
	// dd(array($select_user,$username));

	$rolejoin = array(
				array('role as rl','uar_role_code = rl.role_code'),
				array('apps','rl.role_app_code = app_code'),
				array('packages','rl.role_package_code = package_code'),
			);
	$roleWheree = 'uar_app_expired_date >= CURDATE()';
	$get_role 	 = @$CI->m_global->get_data_all('user_app_roles' , $rolejoin, array('uar_username' => $username, 'uar_status' => 'active', 'app_status' => 'active', 'package_status' => 'active'), '*', $roleWheree, array('app_order asc, package_order asc'), 0, NULL /*, array('uar_username','app_code') */ );

	$usjoin = array(
				array('user_default_settings','uds_id = us_uds_id'),
			);
	$get_user_setting = @$CI->m_global->get_data_all('user_settings', $usjoin, array('us_user_id' => $select_user->user_id), 'us_id,us_uds_id,uds_name,us_value', NULL, array('us_uds_id asc'));
	$decryptedid = $select_user->user_id;
	if ($select_user) {
		try {
			$now 		= date('Y-m-d h:i:s');
			$username 	= $username;
			$email 		= $select_user->user_email;
			if ($usefor == "site") {
				$salt 		= strEncrypt($username.$now.$email);
				$newpassword= encryptions($password,'d',$select_user->salt_code);
			}else{
				$salt 			= $oldsalt;
				$newpassword 	= $password;
			}
			
			$shorting_role = array();
			foreach ($get_role as $key => $value) {
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['code']         =  encryptions($value->app_code,'e',$salt);
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['name']         = $value->app_name;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['status']       = $value->app_status;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['note']         = $value->app_note;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['logo_icon']    = $value->app_logo_icon;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['color']        = $value->app_color;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['have_mobile']  = $value->app_have_mobile_app;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['mobile_route'] = $value->app_mobile_route;

				$shorting_role[ encryptions($value->app_code,'e',$salt)]['package'][ encryptions($value->package_code,'e',$salt)]['name'] 		= $value->package_name;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['package'][ encryptions($value->package_code,'e',$salt)]['status'] 	= $value->package_status;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['package'][ encryptions($value->package_code,'e',$salt)]['note'] 		= $value->package_note;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['package'][ encryptions($value->package_code,'e',$salt)]['role_code']	= $value->role_code;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['package'][ encryptions($value->package_code,'e',$salt)]['role_menu']	= $value->role_menu;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['package'][ encryptions($value->package_code,'e',$salt)]['uar_id']		= $value->uar_id;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['package'][ encryptions($value->package_code,'e',$salt)]['uar_status']	= $value->uar_status;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['package'][ encryptions($value->package_code,'e',$salt)]['uar_note']	= $value->uar_note;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['package'][ encryptions($value->package_code,'e',$salt)]['uar_start']	= $value->uar_app_start_date;
				$shorting_role[ encryptions($value->app_code,'e',$salt)]['package'][ encryptions($value->package_code,'e',$salt)]['uar_end']	= $value->uar_app_expired_date;
			}
			$select_user->roles 		= $shorting_role; 
			$select_user->salt_code 	= $salt; 
			$select_user->user_password = encryptions($newpassword,'e',$salt); 
			$select_user->user_id 		= encryptions($select_user->user_id,'e',$salt); 
			// dd(reset($select_user->roles)['code']);

			$activeAPP['app']     = reset($select_user->roles)['code'];
			$activeAPP['package'] = @array_keys(reset($select_user->roles)['package'])[0];

			if($usefor == "site"){
				$CI->session->set_userdata('userdata', $select_user);
			}
			$allowed_menu = allowed_menu("api",$select_user);
			
			$usettings = array();
			foreach ($get_user_setting as $key => $value) {
				$uds_id 		= encryptions($value->us_uds_id,'e',$salt);
				$us_id 			= encryptions($value->us_id,'e',$salt);
				$usettings[$uds_id]['us_id'] 		= $us_id;
				$usettings[$uds_id]['uds_name'] 	= $value->uds_name;
				$usettings[$uds_id]['us_value'] 	= $value->us_value;
				$usettings[$uds_id]['additional'] 	= array();
			}

			if($usefor == "site"){
				// $CI->session->set_userdata('userdata', $select_user);
				$CI->session->set_userdata('active_app', $activeAPP);
				// dd($CI->session->userdata('active_app'));
				$CI->session->set_userdata('allowed_menu', $allowed_menu);
				$CI->session->set_userdata('user_settings', $usettings);
				$insertuser['user_password'] 	 	= encryptions($newpassword,'e',$salt);

				$insertsalt['salt_code'] 	 		= $salt;
				$insertsalt['salt_last_date'] 		= $now;

				$update_user						= $CI->m_global->update('user', $insertuser, array('user_username' => $username, 'user_id' =>$decryptedid ));
				$update_salt						= $CI->m_global->update('salt', $insertsalt, array('salt_username' => $username, 'salt_user_id' => $decryptedid ));
				return true;
			}elseif ($usefor == "api") {
				$ress["userdata"] = $select_user;
				$ress["active_app"] = $activeAPP;
				$ress["allowed_menu"] = $allowed_menu;
				$ress["user_settings"] = $usettings;

				return $ress;
			}else{
				return false;
			}
		}
		catch(Exception $e){
			return false;
		}
		// dd($CI->session->userdata());
	}else{
		return false;
	}
}

function set_detail_user_settings($usefor = "site", $data_setting = null){
		
		$CI          =& get_instance();

		if ($usefor == "site") {
			$user_setting_list = $CI->session->userdata('user_settings');
		}else{
			$user_setting_list = $data_setting;
		}
		//language detail
			$index 	= array_search('language', array_column($user_setting_list, 'uds_name'));
			$key    = array_keys($user_setting_list)[$index];
			$value  = $user_setting_list[$key]['us_value'];
			$select_language = @$CI->m_global->get_data_all('languages', null, array('language_code' => $value),'language_name')[0];
			$user_setting_list[$key]['additional'] = $select_language;
		//end of language detail
	
		if ($usefor == "site") {
			$CI->session->set_userdata('user_settings',$user_setting_list);
			return true;
		}elseif ($usefor == "api") {
			return $user_setting_list;
		}
}

function get_user_settings($name){
		
	$CI          =& get_instance();
	$user_setting_list = $CI->session->userdata('user_settings');
	$index 	= array_search($name, array_column($user_setting_list, 'uds_name'));
	$key    = array_keys($user_setting_list)[$index];
	$value  = $user_setting_list[$key];
	return $value;
}

function get_app_settings($name = 'all'){
		
	$CI          =& get_instance();
	$setting_list = $CI->session->userdata('setting');
	return (@$setting_list[$name] != '' ? $setting_list[$name] : $setting_list);
}

function get_role_settings($roleCode,$name = 'all'){
		
	$CI          =& get_instance();
	$getRoleSettings = $CI->m_global->get_data_all('role_settings',null,array('rolesetting_role_code' => $roleCode),'rolesetting_name,rolesetting_value',NULL,NULL,0,NULL,NULL,array('array','rolesetting_name,rolesetting_value','a-b'));
	return (@$getRoleSettings[$name] != '' ? $getRoleSettings[$name] : $getRoleSettings);
}

function get_app_package($appCode){
		
	$CI          	=& get_instance();
	$app 			= encryptions($appCode,'e',$CI->session->userdata('userdata')->salt_code);
	// dd(array($app,$CI->session->userdata('userdata')));
	$currentPackage = $CI->session->userdata('userdata')->{'roles'}[$app];
	return $currentPackage;
}

function get_user_app_system_settings($username,$name = '',$app = null){
	$CI          	=& get_instance();

	$joinforuass 	= array(
			array('user' , 'user_username=uass_username','inner')
		);
	$where['user_username'] = $CI->session->userdata('userdata')->user_username;
	if($app != null){
		$where['uass_app_code'] = get_app_settings('app_cashier_catalog');
	}
	$get 			= $CI->m_global->get_data_all('user_app_system_settings', $joinforuass, $where,'uass_name,uass_value',NULL,NULL,0,NULL,NULL,array('array','uass_name,uass_value','a-b'));
	return ($name != '' ? @$get[$name] : $get);
}

function get_fetch($table,$value,$text,$string,$forSelect2 = false){
		
	$CI          =& get_instance();
	$userdata = $CI->session->userdata('userdata');

	if($string == ''){
		return null; 
	}
	$dstring = $string;
	$get_db  = @$CI->m_global->get_data_all($table,null,array($value => $dstring),implode(',', array($value,$text)))[0]; 
	
	$val = null;
	if (!empty($get_db)) {
		if ($forSelect2 == true) {
			$val = "<option value='".encryptions(@$get_db->{$value},'e')."' selected >".@$get_db->{$text}."</option>";
			// $val = "<option value='".encryptions(@$get_db->{$value},'e',$salt)."' selected >".@$get_db->{$text}."</option>";
		}else{
			$val['value'] = @$get_db->{$value};
			$val['text'] = @$get_db->{$text};
		}
	}
	return $val;
}

// function new_salt($username,$email,$password){
// 	$CI          =& get_instance();
// 	$now         = date('Y-m-d h:i:s');
// 	$salt        = strEncrypt($username.$now.$email);
// 	$newpassword = encryptions($password,'d',$select_user->salt_code);

// 	$insertuser['user_password']  = encryptions($newpassword,'e',$salt);
	
// 	$insertsalt['salt_username']  = $username;
// 	$insertsalt['salt_email']     = $email;
// 	$insertsalt['salt_code']      = $salt;
// 	$insertsalt['salt_last_date'] = $now;
// 	$new_salt                     = $CI->m_global->insert('salt', $insertsalt);
// }

function sidebar_menu( $menu, $url )
{
	// pre($menu,1);
	foreach ( $menu as $key => $value ) 
	{

		// echo ( @$value['header'] != '' ? '
		// 	<li class="heading">
		// 		<h3 class="uppercase">'.$value['header'].'</h3>
		// 	</li>' : '' );

		if ((strrpos($value['icon'], 'fa-')) >= 0) {
			$iconclass 	= ' fa '.$value['icon'];
			$icontag 	= 'i';
		}else{
			$iconclass = ' material-icons ';
			$icontag = 'span';
		}
		echo ('<li class="' . 
			/*
				Jika nama path dari menu helper sama dengan path
			*/
			( $value['path'] == $url 
				? ' '.$value['color'].' active' 
				: ' '.$value['color']
			).' " title="'.$value['name'].'" data-original-title="'.$value['name'].'">

			<a ' .
			/*
				Mempunyai sub menu atau tidak
				untuk link href
			*/
			(is_array( @$value['link'] ) 
				//tambahkan class colapsed untuk tertutup
				? 'href="javascript:;" class="" title="'.$value['name'].'" ' 
				: 'class="ajaxifysidebar " href="'.base_url(@$value['link']).'"') . 'title="'.$value['name'].'"'.
			'>

			<'.$icontag.' class="nav-icon '.$iconclass.'">'.($icontag == 'i' ? '' : $value['icon']).'</'.$icontag.'>'.
			'<span>'.$value['name'].'</span>'.
			((is_array(@$value['link'])) ? '<span class="toogle-sub-nav material-icons">keyboard_arrow_right</span>' : '').'</a>'.

			/*
				Mempunyai sedang aktif
			*/
			// ($key == 0 
			// 	? '<span class="selected"></span>' 
			// 	: ''
			// ) .

			/*
				Mempunyai sub menu atau tidak
				untuk menampilkan arrow
			*/
			(is_array(@$value['link']) 
				? ''
				: ''
			));
			
			sub_menu( $value, $url, 0 );

		echo '</li>';
	}
	// pre('',1);
}

function sub_menu( $value, $url, $segment, $submenu = 1 ){

	/*
		Mempunyai sub menu atau tidak
		untuk menampilkan sub link
	*/
	$tab = '';
	$lebar = 0;
	for ($i = 0; $i <= $submenu; $i++) {
		$lebar += 4;
		$tab = 'padding-left : '.$lebar.'px';
	}
	if ( is_array(@$value['link']) )
	{
		//in colapse for open colepse for close
		echo '<ul id="" class="sub-nav">';

		$CI =& get_instance();

		/*
			Menampilkan sub menu
		*/
		// pre($value['link'],1);
		foreach ( $value['link'] as $kSub => $kValue ) 
		{
			// pre($kValue,1);
			$sub_url = $CI->uri->segment($segment);

			if ((strrpos($kValue['icon'], 'fa-')) >= 0) {
				$iconclass 	= ' fa '.$kValue['icon'];
				$icontag 	= 'i';
			}else{
				$iconclass = ' material-icons ';
				$icontag = 'span';
			}

			/*
				Jika path parent sama dengan uri sebelumnya
				dan path sekarang sama dengan uri sekarang
			*/

			echo '<li class="' .
				($kValue['path'] == $sub_url  
					? 'active' 
					: ''
				) . '" >

				<a ' .

				/*
					Jika mempunyai sub, maka href=javascript (tidak ada link)
					jika tidak, maka href berisi link
				*/

				(is_array($kValue['link']) 
					? 'href="javascript:;" class="nav-link nav-toggle" title="'.$kValue['name'].'" data-original-title="'.$kValue['name'].'"' 
					: 'class="nav-link ajaxifysidebar" title="'.$kValue['name'].'" data-original-title="'.$kValue['name'].'" href="'.base_url($kValue['link']).'"'
				) . 

				' style="position:relative !important;">
					<'.$icontag.' class="nav-icon '.$iconclass.'" style="position:absolute;right:-14px;bottom:-14px;top:unset;font-size:36px;padding-left:'.$lebar.'px;opacity:0.15;">'.($icontag == 'i' ? '' : $kValue['icon']).'</'.$icontag.'>'
					.'<span class="sub-menu" style="'.$tab.'">'.$kValue['name'].'</span>'.
					((is_array(@$kValue['link'])) ? '<span class="toogle-sub-nav material-icons">keyboard_arrow_right</span>' : '')

				/*
					Jika mempunyai sub dan path parent sama dengan uri sekarang
					maka arrow open (sub menu sedang aktif)
					selain itu, hanya menampilkan arrow (mempunyai sub menu tapi tidak aktif)
				*/

				.
				'
					</a>';
				
				/*
					cek lagi gan sub menu level selanjutnya
				*/
				$submenu++;
				sub_menu( $kValue, $sub_url, $segment+1, $submenu );

			 echo '</li>';
		}
		echo '</ul>';
	}
}

function number( $var, $dec="0", $separator = false )
{
	if ( empty($var) ) return 0;

	if ($separator == false) {
		return number_format(str_replace(',','.',$var),$dec,',','.');
	} else {
		return number_format(str_replace(',','.',$var),$dec);
	}
}

function list_name($array)
{
	$data   = '';
	$count  = count($array);

	if($count == 1) {
		$data = $array[0];
	} else if ($count == 2) {
		$data = $array[0] . ' dan ' . $array[1];
	} else if ($count > 2) {
		foreach ($array as $key => $val) {
			($key == ($count - 1)) ?
			$data .= ' dan ' . $val :
			$data .= $val . ', ';
		}
	}

	return $data;
}

function list_name2($array)
{
	$data   = '';
	$count  = count($array);

	if($count == 1) {
		$data = $array[0];
	} else if ($count > 1) {
		foreach ($array as $key => $val) {
			($key == ($count - 1)) ?
			$data .= $val :
			$data .= $val . ',';
		}
	}

	return $data;
}

function convert_get_date($data, $return = 'Y-m-d')
{
	$CI     = &get_instance();

	$datas  = str_replace('.', '-', $data);
	$date   = date($return, strtotime($datas));

	return $date;
}

function mybranch($mode='array',$all = false, $code=null, $where = array(), $where_e = null){
	$CI     = &get_instance();
	$branch = $code;
	if ($code == null) {
		$branch = $CI->session->userdata('userdata')->user_embarkasi;
	}
	
	if (($branch != null || $branch != '') && $all == false ) {
		$where['embarkasi_kode'] = $branch;
	}
	$branchlist = $CI->m_global->get_data_all('embarkasi',null,$where,'embarkasi_kode , embarkasi_name, embarkasi_jml_kloter', $where_e);
	if ($branch != null && $mode == 'single') {
	// echo $CI->db->last_query();exit();
		return $branchlist[0];
	}else{
		return $branchlist;
	}
	
}

function dateStringFormat($string,$sparator){
	$date = explode($sparator,$string);
	$datecount = count($date);
	if ($datecount == 1) {
		if ($date[0] > 0 && $date[0] < 13) {
			$returndate = date('F', strtotime($date[0].'/01'.'/2017'));
			return $returndate;
		}
	}elseif($datecount == 2){
		if (strlen($date[0]) == 4 || strlen($date[1]) == 4) {
			if (strlen($date[0]) == 4) {
				$returndate = date('F', strtotime($date[1].'/01'.'/2017')).' '.$date[0];
				return $returndate;
			}elseif (strlen($date[1]) == 4) {
				$returndate = date('F', strtotime($date[0].'/01'.'/2017')).' '.$date[1];
				return $returndate;
			}
		}
	}else{
		if (strlen($date[0]) == 4 || strlen($date[2]) == 4) {
			if (strlen($date[0]) == 4) {
				$returndate = $date[2].' '.date('F', strtotime('01/'.$date[1].'/2017')).' '.$date[0];
				return $returndate;
			}elseif (strlen($date[2]) == 4) {
				$returndate = $date[0].' '.date('F', strtotime('01/'.$date[1].'/2017')).' '.$date[2];
				return $returndate;
			}
		}
	}
}

function comparisonValue($valueA, $valueB, $expresion = 'more'){
	if ($expresion == 'more') {
		if ($valueA < $valueB) {
			return $valueB;
		}else{
			return $valueA;
		}
	}elseif($expresion == 'less'){
		if ($valueA > $valueB) {
			return $valueB;
		}else{
			return $valueA;
		}
	}
}

function exittab($time = 1000){
	echo "
	    <script type='text/javascript'>
	         setTimeout('self.close()',".$time.");
	    </script>
	";
}

function allowed_menu($usefor = "site", $userdata = null){
	// opsi lain adalah dengan memisahkan parent dan child pada field berbeda pada tabel role
	// cara itu lebih menghemat bandwith karena nantinya bisa di tembak dengan single query
	$CI     = &get_instance();
	// $menu    = $CI->m_global->get_data_all('role',null,['role_code' => $CI->session->userdata('userdata')->user_role],'role_menu')[0];
	if ($usefor == "site") {
		$all_app_menu   = $CI->session->userdata('userdata')->roles;
	}else{
		$all_app_menu   = $userdata->roles;
	}
	$arrmenu = array();
	foreach ($all_app_menu as $keyapp => $valueapp) {
		foreach ($valueapp['package'] as $keypkg => $valuepkg) {
			$json_menu_list = json_decode($valuepkg['role_menu'], true);
			// dd($json_menu_list);
			array_push($arrmenu, $json_menu_list);
		}
		
	}
	$parent  = array();
	$child   = array();
	foreach ($arrmenu as $key => $value) {
		foreach ($value as $key2 => $value2) {
			array_push($parent, str_replace('menu-','',$key2));
			$child[] = $value2;
		}
	}
	$rechild = array();
	foreach ($child as $key => $value) {
		if (!is_object($value) && !is_array($value)) {
			// if (!is_array($value)) {
			// 	$child[$key] = str_replace('sub-menu-','',$value);
			// }
			// else{
				foreach ($value as $key2 => $value2) {
					$child[$key] = str_replace('sub-menu-','',$value2);
					// pre($value2,1);
					
				}
			// }
		}else{
			foreach ($value as $key2 => $value2) {
				
				if (!is_object($value2) && !is_array($value2)) {
					if (!is_array($value2)) {
						$child[][] = str_replace('sub-menu-','',$value2);
					}else{
						foreach ($value2 as $key3 => $value3) {
							$child[][] = str_replace('sub-menu-','',$value3);
						}
					}
				}else{
					foreach ($value2 as $key3 => $value3) {
						// pre($value2);
						if (!is_object($value3) && !is_array($value3)) {
							if (!is_array($value3)) {
								$child[][] = str_replace('sub1-menu-','',$value3);
							}else{
								foreach ($value3 as $key4 => $value4) {
									$child[][] = str_replace('sub1-menu-','',$value4);
								}
							}
						}else{
							foreach ($value3 as $key4 => $value4) {
								if (!is_object($value4) && !is_array($value4)) {
									if (!is_array($value4)) {
										$child[][] = str_replace('sub2-menu-','',$value4);
									}else{
										foreach ($value4 as $key5 => $value5) {
											$child[][] = str_replace('sub2-menu-','',$value5);
										}
									}
								}else{
									foreach ($value4 as $key5 => $value5) {
										if (!is_object($value5) && !is_array($value5)) {
											if (!is_array($value5)) {
												$child[][] = str_replace('sub3-menu-','',$value5);
											}else{
												foreach ($value5 as $key6 => $value6) {
													$child[][] = str_replace('sub3-menu-','',$value6);
												}
											}
										}else{
											foreach ($value5 as $key6 => $value6) {
												if (!is_object($value6) && !is_array($value6)) {
													if (!is_array($value6)) {
														$child[][] = str_replace('sub4-menu-','',$value6);
													}else{
														foreach ($value6 as $key7 => $value7) {
															$child[][] = str_replace('sub4-menu-','',$value7);
														}
													}
												}else{

												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
			$child[$key] = array();
		}
	}
	// pre($child,1);
	$child = call_user_func_array('array_merge', $child);
	// pre($child,1);
	

	$menuparent = array();
	if ($parent != null) {
		$parent_e		=  ' menu_id IN ('.implode(',',$parent).')';
		$menuparent    	= $CI->m_global->get_data_all('menus',null,array('menu_link !=' => ''),'menu_link as link, menu_id as id',$parent_e);
	}
	
	$menuchild = array();
	if ($child != null) {
		$child_e		=  ' submenu_id IN ('.implode(',',$child).')';
		$menuchild    	= $CI->m_global->get_data_all('submenus',null,array('submenu_link !=' => ''),'submenu_link as link, submenu_id as id',$child_e);
	}
	
	
	$allowed_menu_arr = array_merge($menuparent,$menuchild);
	
	$allowed_menu = array();
	foreach ($allowed_menu_arr as $key => $value) {
		$allowed_menu[] = $value->link;
	}

	return $allowed_menu;
}

function list_menu(){

	//cuman support untuk parent menu , bukan parent submenu
	//
	$CI     = &get_instance();

	$menuparent    	= $CI->m_global->get_data_all('menus',null,null,'menu_name as name, menu_id as id, menu_order as order',null, array('menu_order','asc'));

	$menu = array();
	foreach ($menuparent as $key => $value) {
		$menu['menu-'.$value->id]['name']  = $value->name;
		$menu['menu-'.$value->id]['order'] = $value->order;
		$menu['menu-'.$value->id]['check'] = false;
		$menu['menu-'.$value->id]['sub']   = array();
	}

	$maxchild = @$CI->m_global->get_data_all('submenus',null,array('submenu_submenu_parent !=' => 'false'),'submenu_submenu_parent',null,array('submenu_submenu_parent','desc'))[0];
	// pre($maxchild,1);

	$submenuparent    	= $CI->m_global->get_data_all('submenus',null,array('submenu_submenu_parent' => 'false'),'submenu_name as name, submenu_id as id, submenu_order as order, submenu_parent as parent, submenu_submenu_parent as submenu_parent',null, array('submenu_order','asc'));

	foreach ($submenuparent as $key => $value) {
		if ($value->submenu_parent == 'false') {
			$menu['menu-'.$value->parent]['sub']['menu-'.$value->id]['name'] = $value->name;
			$menu['menu-'.$value->parent]['sub']['menu-'.$value->id]['order'] = $value->order;
			$menu['menu-'.$value->parent]['sub']['menu-'.$value->id]['check'] = false;
			$menu['menu-'.$value->parent]['sub']['menu-'.$value->id]['sub'] = '-BAN0-'.$value->id.'-';
		}
	}

	$jazz	  = json_encode($menu);
	// if (count($maxchild) > 0) {
	if ($maxchild->submenu_submenu_parent) {

		$maxchild = explode('-', $maxchild->submenu_submenu_parent);
		$max 	  = $maxchild[1]+1;
		for ($i = 0; $i <= $max; $i++) {
			$submenuparent    	= $CI->m_global->get_data_all('submenus',null,array('submenu_submenu_parent' => 'true-'.$i),'submenu_name as name, submenu_id as id, submenu_order as order, submenu_parent as parent, submenu_submenu_parent as submenu_parent',null, array('submenu_order','asc'));
			// pre($submenuparent);
			$j = 1;
			$subes = array();
			foreach ($submenuparent as $value) {
				$j++;
				// if ($i==0) {
					$subes['-BAN'.($i).'-'.$value->parent.'-']['menu-'.$value->id]['name']  = $value->name;
					$subes['-BAN'.($i).'-'.$value->parent.'-']['menu-'.$value->id]['order'] = $value->order;
					$subes['-BAN'.($i).'-'.$value->parent.'-']['menu-'.$value->id]['check'] = false;
					$subes['-BAN'.($i).'-'.$value->parent.'-']['menu-'.$value->id]['sub']   = '-BAN'.($i+1).'-'.$value->id.'-';
					// $jazz .= '"menu-'.$value->id.'":{"name":"'.$value->name.'","order":"'.$value->order.'","check":false,"sub":[]}';
				// }
				// if ($j < count($submenuparent)) {
				// 	$jazz .= ',';
				// }
			}
			foreach ($subes as $key => $value) {
				$jsubes = json_encode($value);
				$jazz = str_replace('"'.$key.'"', $jsubes, $jazz);
			}
			
			// pre(json_decode($jazz),1);
			// pre($subes,1);
		}
	}
	
	$menu = json_decode($jazz, true);
	// pre($menu,1);
	return $menu;
}

function parseJSON2($url = '', $id = '', $password = ''){
     
    $ch = curl_init();     
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
    curl_setopt($ch, CURLOPT_URL, $url);  
    if ($password != '' && $id != '') {
      curl_setopt($ch, CURLOPT_USERPWD, $id.":".$password);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //see link below  
    $result = curl_exec($ch);
    $xml = simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);
    $json = json_encode($xml);
    $array = json_decode($json,TRUE);

    curl_close($ch);  
    return $array;
}

function threedigit($no){
	$no = (int)$no;
	if ($no < 10) {
		$no = '00'.$no;
	}elseif ($no >= 10 && $no < 100) {
		$no = '0'.$no;
	}
	return $no;
}

function tanggalan($tanggal)
{
	$bulan = array (1 =>   'Januari',
				'Februari',
				'Maret',
				'April',
				'Mei',
				'Juni',
				'Juli',
				'Agustus',
				'September',
				'Oktober',
				'November',
				'Desember'
			);
	$split = explode('-', $tanggal);
	return $split[2] . ' ' . $bulan[ (int)$split[1] ] . ' ' . $split[0];
}


function HijriToJD($param,$format=null){
	$exp = explode(' ', $param);
	$d   = $exp[0];
	$m   = $exp[1];
	$y   = $exp[2];
   	$int = (int)((11 * $y + 3) / 30) + 354 * $y + 30 * $m - (int)(($m - 1) / 2) + $d + 1948440 - 385;

   	if($format == 'd'){
   		return date('d',strtotime(jdtogregorian($int)));
   	}else if($format == 'm'){
   		return date('m',strtotime(jdtogregorian($int)));
   	}else if($format == 'y'){
   		return date('Y',strtotime(jdtogregorian($int)));
   	}else{
   		return date('d m Y',strtotime(jdtogregorian($int)));
   	}
}

function excelcol($numb = 1){
	$alph = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	// echo $numb.'<br/>';
	if ($numb > 26) {
		$newnumb = $numb;
		$awal  = 0;
		$akhir = 0;
		for ($i=$newnumb; $i > 26 ; $i = $i - 26) { 
			$awal++;
			$akhir = $i - 26;
		}
		// echo $awal;
		return $alph[($awal-1)].$alph[($akhir-1)];
	}else{
		$numb = $numb-1;
		return $alph[$numb];
	}
	
}

function listpreftable( $tipe = 'prefix'){
	//pakai seperti ini karena penamaan field pada DB tidak standard
	if ($tipe == 'prefix') {
			//normal nya dipakai untuk dinamik export ,  karena where nya masi perlu di sesuaikan
			$array = array(
								'reservasi1'    => 'reservasi_',
								'reservasi2'    => 'reservasi_',
								'mutasi1'       => 'log_mut_',
								'mutasi2'       => 'log_mut_',
								'reservasigab1' => 'reservasi_',
								'reservasigab2' => 'reservasi_',
								'penerbangan'   => 'flight_',
								'siskohat'   	=> 'sisko_',
		                    );
	}else{
			//normal nya dipakai untuk dinamik Import ,  karena where nya masi perlu di sesuaikan
			$array = array(
								'reservasi1_embarkasi'    => 'reservasi_embarkasi',
								'reservasi1_kloter'       => 'reservasi_kloter',
								'reservasi1_tahun'        => 'reservasi_tahun',
								'reservasi2_embarkasi'    => 'reservasi_embarkasi',
								'reservasi2_kloter'       => 'reservasi_kloter',
								'reservasi2_tahun'        => 'reservasi_tahun',

								'reservasi_gab1_embarkasi'    => 'reservasi_embarkasi_asal',
								'reservasi_gab1_kloter'       => 'reservasi_kloter',
								'reservasi_gab1_tahun'        => 'reservasi_tahun',
								'reservasi_gab2_embarkasi'    => 'reservasi_embarkasi_asal',
								'reservasi_gab2_kloter'       => 'reservasi_kloter',
								'reservasi_gab2_tahun'        => 'reservasi_tahun',

								'log_p1_mutasi_embarkasi' => 'log_mut_embarkasi',
								'log_p1_mutasi_kloter'    => 'log_mut_kloter',
								'log_p1_mutasi_tahun'     => 'log_mut_tahun',
								'log_p2_mutasi_embarkasi' => 'log_mut_embarkasi',
								'log_p2_mutasi_kloter'    => 'log_mut_kloter',
								'log_p2_mutasi_tahun'     => 'log_mut_tahun',

								'sisko_embarkasi'	=>	'sisko_embarkasi',
								'sisko_kloter'		=>	'sisko_kloter',
								'sisko_tahun'		=>	'sisko_tahun',
		                    );
	}
	
	return $array;
}

function syncordermenufrom($id,$table,$field,$fieldid,$parent = null,$parpos = null){
    $CI      = &get_instance();
    $where 	 = array();
    if ($parent != null) {
    	$where['submenu_parent'] = $parent;
    }
    if ($parpos != null) {
    	$where['submenu_submenu_parent'] = $parpos;
    }
    $cekorder = @$CI->m_global->get_data_all($table, null, $where, $field.','.$fieldid, $field.' >= '.$id, '', '', '', '', 'array');

    $arrayupdate = array();
    foreach ($cekorder as $value) {
    	$arrayupdate[] = array(
    						$fieldid 	=> $value[$fieldid],
    						$field 		=> ($value[$field]+1),
    					);
    }
    if (count($arrayupdate) > 0) {
    	$result = $CI->m_global->update_batch($table,$arrayupdate,$fieldid);
    }
    if (!$arrayupdate) {
        $arrayupdate = null;
    }
    return $arrayupdate;
}

function syncordermenufromin($id,$oldid,$table,$field,$fieldid){
    $CI      = &get_instance();
    $cekorder = @$CI->m_global->get_data_all($table, null, null, $field.','.$fieldid, $field.' > '.$oldid.' and '.$field.' <= '.$id, '', '', '', '', 'array');
    // pre($CI->db->last_query(),1);
    $arrayupdate = array();
    foreach ($cekorder as $value) {
    	$arrayupdate[] = array(
    						$fieldid 	=> $value[$fieldid],
    						$field 		=> ($value[$field]-1),
    					);
    }
    if (count($arrayupdate) > 0) {
    	$result = $CI->m_global->update_batch($table,$arrayupdate,$fieldid);
    }
    if (!$arrayupdate) {
        $arrayupdate = null;
    }
    return $arrayupdate;
}

function syncordermenubefore($id,$oldid,$table,$field,$fieldid){
    $CI      = &get_instance();
    $cekorder = @$CI->m_global->get_data_all($table, null, null, $field.','.$fieldid, $field.' >= '.$id.' and '.$field.' < '.$oldid, '', '', '', '', 'array');
    // pre($cekorder,1);
    $arrayupdate = array();
    foreach ($cekorder as $value) {
    	$arrayupdate[] = array(
    						$fieldid 	=> $value[$fieldid],
    						$field 		=> ($value[$field]+1),
    					);
    }
    if (count($arrayupdate) > 0) {
    	$result = $CI->m_global->update_batch($table,$arrayupdate,$fieldid);
    }
    if (!$arrayupdate) {
        $arrayupdate = null;
    }
    return $arrayupdate;
}

function syncmenudelete($id,$table,$field,$fieldid,$return='array'){
    $CI      = &get_instance();
    $cekorder = @$CI->m_global->get_data_all($table, null, null, $field.','.$fieldid, $field.' > '.$id, '', '', '', '', 'array');

    $arrayupdate = array();
    $result = true;
    foreach ($cekorder as $value) {
    	$arrayupdate[] = array(
    						$fieldid 	=> $value[$fieldid],
    						$field 		=> ($value[$field]-1),
    					);
    }
    if (count($arrayupdate) > 0) {
    	$result = $CI->m_global->update_batch($table,$arrayupdate,$fieldid);
   	}
    if (!$arrayupdate) {
        $arrayupdate = null;
    }
    if ($return == 'array') {
    	return $arrayupdate;
    }else{
    	return $result;
    }
}

function syncleveldeletemenu($id,$return='array'){
	$CI      = &get_instance();
	$ceklevel= @$CI->m_global->get_data_all('role', null, null, 'role_code,role_menu', ' role_menu like "%menu-'.$id.'%"', '', '', '', '', 'array');
	$arrayupdate = array();
	$result = true;
	foreach ($ceklevel as $value) {
		$arrmenu = json_decode($value['role_menu']);
		unset($arrmenu->{'menu-'.$id});
		$arrayupdate[] = array(
							'role_code' 		=> $value['role_code'],
							'role_menu' 	=> json_encode($arrmenu),
						);
	}
	if (count($arrayupdate) > 0) {
    	$result = $CI->m_global->update_batch('role',$arrayupdate,'role_code');
	}

	if (!$arrayupdate) {
	    $arrayupdate = null;
	}

	if($return=='array'){
		return $arrayupdate;
	}else{
		return $result;
	}
}

function deletesubbymenu($id){
	$CI      = &get_instance();

	$cekdata = @$CI->m_global->get_data_all('submenus', null, array('submenu_submenu_parent' => 'false', 'submenu_parent' => $id), 'submenu_id');
	foreach ($cekdata as $value) {
		$deletesub = deletesubmenu($value->submenu_id,'','','','false');
	}

	$deletechild = @$CI->m_global->delete('submenus', array('submenu_submenu_parent ' => 'false', 'submenu_parent' => $id));
}

function deletesubmenu($id,$typeparent = '',$idparent = '',$order = '',$syncparent = 'true'){
	$CI      = &get_instance();
	
	$aid = array($id);
	do {
		$aid = implode(',', $aid);
	    $selectchild = @$CI->m_global->get_data_all('submenus', null, array('submenu_submenu_parent !=' => 'false'), 'submenu_id', 'submenu_parent in ('.$aid.')');
	    if (count($selectchild) > 0) {
	    	$deletechild = @$CI->m_global->delete('submenus', array('submenu_submenu_parent !=' => 'false'), 'submenu_parent in ('.$aid.')');
	    // pre($CI->db->last_query(),1);
	    	$aid = array();
	    	foreach ($selectchild as $value) {
	    		array_push($aid, $value->submenu_id);
	    	}
	    }else{
	    	break;
	    }
	} while (count($aid) > 0);

    $arrayupdate = array();
	if ($syncparent == 'true') {
			$cekorder = @$CI->m_global->get_data_all('submenus', null, array('submenu_submenu_parent =' => $typeparent, 'submenu_parent' => $idparent), 'submenu_id,submenu_order', 'submenu_order > '.$order, '', '', '', '', 'array');
			// pre($CI->db->last_query());
		    foreach ($cekorder as $value) {
		    	$arrayupdate[] = array(
									'submenu_id'    => $value['submenu_id'],
									'submenu_order' => ($value['submenu_order']-1),
		    					);
		    }
			// pre($arrayupdate);
		    if (count($arrayupdate) > 0) {
		    	$result = $CI->m_global->update_batch('submenus',$arrayupdate,'submenu_id');
				// pre($CI->db->last_query(),1);

		   	}
	}
	
    if (!$arrayupdate) {
        $arrayupdate = null;
    }
    return $arrayupdate;
}

// function menutosubmenu($id,$oldparent,$newparent,$order,$oldorder,$thechild,$theoldchild){
// 	$CI      	 = &get_instance();
	
// 	$ochild   = 0;
// 	$nchild   = 0;
// 	$distance = 0;
// 	$position = '';
// 	if ($thechild != 'false') {
// 		$val = explode('-',$thechild);
// 		$nchild   = (int)$val[1];
// 	}else{
// 		$nchild   = -1;
// 	}
// 	if ($theoldchild != 'false') {
// 		$val = explode('-',$theoldchild);
// 		$nchild   = (int)$val[1];
// 	}else{
// 		$nchild   = -1;
// 	}

// 	if ($ochild > $nchild) {
// 		$distance = $ochild - $nchild;
// 		$position = 'plus';
// 	}elseif ($ochild < $nchild) {
// 		$distance = $nchild - $ochild;
// 		// pre($distance);
// 		$position = 'min';
// 	}else{
// 		$distance = 0;
// 		$position = 'same';
// 	}

// 	if ($oldparent != $newparent || $thechild != $theoldchild ) {
// 		$whereold['submenu_parent'] 		 = $oldparent;
// 		$whereold['submenu_submenu_parent']  = $theoldchild;
// 		$whereold_e 						 = ' submenu_order > '.$oldorder;

// 		$dataoldsubmenu = $CI->m_global->get_data_all('submenus', null, $whereold, 'submenu_id , submenu_order', $whereold_e, '', '', '', '', 'array');
		

// 	    $arrayupdate = array();
// 	    foreach ($dataoldsubmenu as $value) {
// 	    	$arrayupdate[] = array(
// 								'submenu_id'    => $value['submenu_id'],
// 								'submenu_order' => ($value['submenu_order']-1),
// 	    					);
// 	    }

// 	    $wherenew['submenu_parent'] 		 = $newparent;
// 		$wherenew['submenu_submenu_parent']  = $thechild;
// 		$wherenew_e 						 = ' submenu_order >= '.$order;

// 		$dataoldsubmenu = $CI->m_global->get_data_all('submenus', null, $wherenew, 'submenu_id,submenu_order', $wherenew_e, '', '', '', '', 'array');

// 	    foreach ($dataoldsubmenu as $value) {
// 	    	$arrayupdate[] = array(
// 								'submenu_id'    => $value['submenu_id'],
// 								'submenu_order' => ($value['submenu_order']+1),
// 	    					);
// 	    }

// 	    $datasubmenu 		= $CI->m_global->get_data_all('submenus',null,array(strEncryptcode('submenu_id', true) => $id))[0];

// 	    $aid = array($datasubmenu->submenu_id);
// 	    do {
// 	    	$aid = implode(',', $aid);
// 	        $selectchild = @$CI->m_global->get_data_all('submenus', null, array('submenu_submenu_parent !=' => 'false'), 'submenu_id,submenu_submenu_parent', 'submenu_parent in ('.$aid.')');
// 	        if (count($selectchild) > 0) {

// 	        	$aid = array();
// 	        	foreach ($selectchild as $value) {
// 	        		$subsubparent = explode('-',$value->submenu_submenu_parent);
// 	        		$newsubsub = 0;
// 	        		if ($position == 'min') {
// 	        			$newsubsub = ((int)$subsubparent[1]-$distance);
// 	        		}elseif($position == 'min'){
// 	        			$newsubsub = ((int)$subsubparent[1]+$distance);
// 	        		}else{
// 	        			$newsubsub = ((int)$subsubparent[1]);
// 	        		}
// 	        		$arrayupdate[] = array(
// 								'submenu_id'    			=> $value->submenu_id,
// 								'submenu_submenu_parent' 	=> 'true-'.$newsubsub,
// 	    					);
// 	        		array_push($aid, $value->submenu_id);
// 	        	}
// 	        }else{
// 	        	break;
// 	        }
// 	    } while (count($aid) > 0);


// 	    if (count($arrayupdate) > 0) {
// 	    	$result = $CI->m_global->update_batch('submenus',$arrayupdate,'submenu_id');
// 			// pre($CI->db->last_query(),1);

// 	   	}

// 	}else{
// 		if ($order != $oldorder) {
// 			if ($order > $oldorder) {
// 				$result = syncordermenufromin($order,$oldorder,'submenus','submenu_order','submenu_id');
// 			}elseif ($order < $oldorder) {
// 				$result = syncordermenubefore($order,$oldorder,'submenus','submenu_order','submenu_id');
// 			}
// 		}
// 	}
// 	return @$result;

// }

function count_dimension($Array, $count = 0) {
	pre($Array,1);
   if(is_array($Array)) {
      return count_dimension(current($Array), ++$count);
   } else {
      return $count;
   }
}
function kekata($x) {
    $x = abs($x);
    $angka = array("", "satu", "dua", "tiga", "empat", "lima",
    "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $temp = "";
    if ($x <12) {
        $temp = " ". $angka[$x];
    } else if ($x <20) {
        $temp = kekata($x - 10). " belas";
    } else if ($x <100) {
        $temp = kekata($x/10)." puluh". kekata($x % 10);
    } else if ($x <200) {
        $temp = " seratus" . kekata($x - 100);
    } else if ($x <1000) {
        $temp = kekata($x/100) . " ratus" . kekata($x % 100);
    } else if ($x <2000) {
        $temp = " seribu" . kekata($x - 1000);
    } else if ($x <1000000) {
        $temp = kekata($x/1000) . " ribu" . kekata($x % 1000);
    } else if ($x <1000000000) {
        $temp = kekata($x/1000000) . " juta" . kekata($x % 1000000);
    } else if ($x <1000000000000) {
        $temp = kekata($x/1000000000) . " milyar" . kekata(fmod($x,1000000000));
    } else if ($x <1000000000000000) {
        $temp = kekata($x/1000000000000) . " trilyun" . kekata(fmod($x,1000000000000));
    }     
        return $temp;
}
function terbilang($x, $style=4) {
    if($x<0) {
        $hasil = "minus ". trim(kekata($x));
    } else {
        $hasil = trim(kekata($x));
    }     
    switch ($style) {
        case 1:
            $hasil = strtoupper($hasil);
            break;
        case 2:
            $hasil = strtolower($hasil);
            break;
        case 3:
            $hasil = ucwords($hasil);
            break;
        default:
            $hasil = ucfirst($hasil);
            break;
    }     
    return $hasil;
}

function colour($tint) {

    $frag = range(0,255);

    $red = "";
    $green = "";
    $blue = "";

    for (;;) {

        $red = $frag[mt_rand(0, count($frag)-1)];
        $green = $frag[mt_rand(0, count($frag)-1)];
        $blue = $frag[mt_rand(0, count($frag)-1)];

        switch ($tint) {
            case 'light':
                if (($red + $green + $blue / 3) >= 200) break 2;
                break;
            case 'dark' :
            default:
                if (($red + $green + $blue / 3) <= 50) break 2;
                break;
        }
    }
    return sprintf("#%02s%02s%02s", dechex($red), dechex($green),dechex($blue));
}

function color_inverse($color , $bw = false){
    $color = str_replace('#', '', $color);
    if (strlen($color) != 6){ return '000000'; }
    $rgb = '';
    $countbw = 0;
    $thebw = array(
    	0 => 0.299, //r
    	1 => 0.587, //g
    	2 => 0.114, //b
    );
    for ($x=0;$x<3;$x++){
        $c = 255 - hexdec(substr($color,(2*$x),2));
        $c = ($c < 0) ? 0 : dechex($c);
        $countbw = $countbw + (hexdec(substr($color,(2*$x),2)) * $thebw[$x]);
        $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
    }

    if ($bw == true) {
    	$rgb = ($countbw > 186 ? '000000' : 'ffffff' );
    }
    return '#'.$rgb;
}

function array_column_manual(array $input, $columnKey, $indexKey = null) {
    $array = array();
    foreach ($input as $value) {
        if ( !array_key_exists($columnKey, $value)) {
            trigger_error("Key \"$columnKey\" does not exist in array");
            return false;
        }
        if (is_null($indexKey)) {
            $array[] = $value->{$columnKey};
        }
        else {
            if ( !array_key_exists($indexKey, $value)) {
                trigger_error("Key \"$indexKey\" does not exist in array");
                return false;
            }
            if ( ! is_scalar($value[$indexKey])) {
                trigger_error("Key \"$indexKey\" does not contain scalar value");
                return false;
            }
            $array[$value[$indexKey]] = $value[$columnKey];
        }
    }
    return $array;
}

function like_match($pattern, $subject)
{
    $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
    return (bool) preg_match("/^{$pattern}$/i", $subject);
}

function GenerateNumber($field,$zeroprefix = 1,$format = '||numbering||',$useYear = 'yes',$useMonth = 'no') {
	$CI      	 = &get_instance();
	$thefield = 'global';
	if ($field != '') {
		$thefield = $field;
	}
	
	$records = @$CI->m_global->get_data_all('auto_numbering',null,array('numbering_table' => $thefield),'numbering_number,numbering_year')[0];
	$number = '1';        
	if (!empty($records)) {
		$number = $records->numbering_number+1; 
		// dd($number);
		if($useYear == "yes"){
			if ($records->numbering_year != date('Y')) {
				$number = 1;
			}
			if($useMonth == "yes"){
				if ($records->numbering_month != date('m')) {
					$number = 1;
				}
			}					
		}
	}

	$number = str_pad($number, $zeroprefix, '0', STR_PAD_LEFT);

	$output = str_replace('||numbering||', $number, $format);
	$output = str_replace('||year||', date('Y'), $output);
	$output = str_replace('||month||', date('m'), $output);

	return $output;
}

function UpdateNumber($field,$useYear = 'no',$useMonth = 'no') {
	$CI      	 = &get_instance();
	$thefield = 'global';
	if ($field != '') {
		$thefield = $field;
	}
	$records = @$CI->m_global->get_data_all('auto_numbering',null,array('numbering_table' => $thefield),'numbering_number,numbering_year,numbering_month')[0];
	if (!empty($records)) {
		$number = $records->numbering_number+1; 
		// dd($number);
		if($useYear == "yes"){
			if ($records->numbering_year != date('Y')) {
				$number = 1;
			}
			if($useMonth == "yes"){
				if ($records->numbering_month != date('m')) {
					$number = 1;
				}
			}					
		}
		$newone['numbering_number']          = $number;
		$newone['numbering_year']            = date('Y');
		$newone['numbering_month']           = date('m');
		$updateorcreate = @$CI->m_global->update('auto_numbering',$newone,array('numbering_table' => $thefield));
	}else{
		$newone['numbering_table']           = $thefield;
		$newone['numbering_number']          = 1;
		$newone['numbering_year']            = date('Y');
		$newone['numbering_month']           = date('m');
		$updateorcreate = @$CI->m_global->insert('auto_numbering',$newone);
	}

	return $updateorcreate;

}

function tablestatus($intstatus){
	if($intstatus == '1'){
		return langText('aktif');
	}elseif($intstatus == '0'){
		return langText('tidak_aktif');
	}elseif($intstatus == '99'){
		return langText('dihapus');
	}
}

function myId(){
	$CI      	 = &get_instance();
	return encryptions($CI->session->userdata('userdata')->user_id,'d',$CI->session->userdata('userdata')->salt_code);
}

function dblast(){
	$CI      	 = &get_instance();
	return $CI->db->last_query();
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

function getSetting(){
	$CI      	 = &get_instance();
	$query = $CI->db->get('setting');
	return $query->BAN_array_field('setting_name,setting_value','a-b');
	// return $CI->m_global->get_data_all('setting',NULL,NULL,'*',NULL,NULL,0,NULL,NULL,array('array','setting_name,setting_value','a-b'));
}

function generateTokenQrData($code,$token,$tokenMasterKey,$verificationCode){
	include APPPATH."/third_party/phpqrcode/qrlib.php";

	$tempdir = './assets/images/product/token/'.$code.'/'; //Nama folder tempat menyimpan file qrcode
    if (!file_exists($tempdir)){
		mkdir($tempdir, 0755, TRUE); //Buat folder
	} 
	$generateTokenQR = '';
	if (!file_exists($tempdir.'/token.png')){
		$codeContents = base_url('wildCard/'.$token);
		$generateTokenQR = generateQR($tempdir,$codeContents,'token');
	}else{
		$generateTokenQR = $tempdir.'token.png';
	}
	$generateTokenMasterQR = '';
	if (!file_exists($tempdir.'/master-token.png')){
		$codeContents = base_url('wildCard/masterCard/'.$tokenMasterKey);
		$generateTokenMasterQR = generateQR($tempdir,$codeContents,'master-token');
	}else{
		$generateTokenMasterQR = $tempdir.'master-token.png';
	}

	return array(
		'generateTokenQR' => $generateTokenQR,
		'generateTokenMasterQR' => $generateTokenMasterQR,
	);
}

function random_str($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateQR($tempdir,$codeContents,$name){
	//ambil logo
    $logopath= base_url('assets/page/images/logo/NickyMouse_Developer_head.jpg');

	//simpan file qrcode
 	QRcode::png($codeContents, $tempdir.$name.'.png', QR_ECLEVEL_H, 10,4);

	// ambil file qrcode
	$QR = imagecreatefrompng($tempdir.$name.'.png');

	// memulai menggambar logo dalam file qrcode
	$logo = imagecreatefromstring(file_get_contents($logopath));
	
	imagecolortransparent($logo , imagecolorallocatealpha($logo , 0, 0, 0, 127));
	imagealphablending($logo , false);
	imagesavealpha($logo , true);

	$QR_width = imagesx($QR);
	$QR_height = imagesy($QR);

	$logo_width = imagesx($logo);
	$logo_height = imagesy($logo);

	// Scale logo to fit in the QR Code
	$logo_qr_width = $QR_width/8;
	$scale = $logo_width/$logo_qr_width;
	$logo_qr_height = $logo_height/$scale;

	imagecopyresampled($QR, $logo, $QR_width/2.3, $QR_height/2.3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

	// Simpan kode QR lagi, dengan logo di atasnya
	imagepng($QR,$tempdir.$name.'.png');

	return $tempdir.$name.'.png';
}

function get_wildcard_function($link,$functionName = '',$option = null){
	$CI      	 = &get_instance();
	$onlineBaseUrl = get_app_settings("site_address");
	$currentBaseUrl = base_url();
	$nobaselink = str_replace("/wildCard/","",str_replace($currentBaseUrl , "", str_replace($onlineBaseUrl,"",$link)));
	$data['isMasterToken'] 	= false;
	$data['finalValue'] 	= $nobaselink;
	$data['status']			= staticValue("status_request","success");
	$data['msg']			= langText("berhasil_mendapatkan_data");
	$data['redirectLink'] 	= "";
	$data['data']		  	= array();
	// dd(array(strpos($nobaselink,'masterCard/'),$nobaselink));
	if(strpos($nobaselink,'masterCard/') !== false){
		$data['isMasterToken'] = true;
		$data['finalValue'] = str_replace('masterCard/',"",$nobaselink);
	}

	$needWildcardInfo = array('get_landingpage','landingpage_validation','set_cashier_session');
	if(in_array($functionName,$needWildcardInfo)){
		$get_wildcard_join 	= array(
			array('ec_wild_card',"ecwc_glbl_prodtok_code=glbl_prodtok_code")
		);
		if($data['isMasterToken']){
			$get_wildcard_where['glbl_prodtok_token_master_key'] = $data['finalValue'];
		}else{
			$get_wildcard_where['glbl_prodtok_token'] = $data['finalValue'];	
		}
		$get_wildcard 		= $CI->m_global->get_data_all('glbl_product_token',$get_wildcard_join,$get_wildcard_where,'ecwc_glbl_prodtok_code,ecwc_landing_page,glbl_prodtok_code,ecwc_name,glbl_prodtok_token')[0];
		$data['data']['wildcard'] = $get_wildcard;
		if(!empty($get_wildcard)){
			$CI->session->set_userdata('wildcardToken',$get_wildcard->glbl_prodtok_token);
		}
	}

	if($functionName == 'get_landingpage'){

		
		if($get_wildcard->ecwc_landing_page != "" && !$data['isMasterToken']){
			// get_wildcard_function("link");
			$relation				= explode('||',$get_wildcard->ecwc_landing_page);
			$get_app_role_join 		= array(
				array('ec_wild_card_app_connect', 'ecwcacsr_ecwcac_id=ecwcac_id'),
				array('ec_wild_card_app_connect_setting_name', 'ecwcacsr_ecwcacsn_code=ecwcacsn_code'),
			);
			$get_app_role_where['ecwcac_glbl_prodtok_code'] 	= $get_wildcard->ecwc_glbl_prodtok_code;
			$get_app_role_where['ecwcacsn_code'] 				= $relation[0];
			$get_app_role_where['ecwcacsr_value'] 				= $relation[1];
			$get_app_role_where['ecwcacsn_has_landing_page'] 	= (string)staticValue('global','true');

			$get_app_role_select 	= 'ecwcacsn_app_code,ecwcacsn_code,ecwcacsn_name,ecwcacsr_value,ecwcac_user_username,ecwcac_glbl_prodtok_code';
			$get_app_role 			= $CI->m_global->get_data_all('ec_wild_card_app_connect_setting_relations',$get_app_role_join,$get_app_role_where,$get_app_role_select);
			if($get_app_role){
				$get_app_role = $get_app_role[0];
				//------------------------------------------------- cashier application start -------------------------------------------------
				if($get_app_role->ecwcacsn_app_code == get_app_settings('app_cashier_catalog')){
					if($get_app_role->ecwcacsn_code == get_app_settings('ec_app_role_connect_cashier')){
						$getStoreBranchJoin 	= array(
							array('cc_store','ccbranch_store_code=ccstore_code')
						);

						$getStoreBranchWhere['ccbranch_code'] 	= $get_app_role->ecwcacsr_value;
						
						$getStoreBranchSelect 	= 'ccstore_code,ccstore_name,ccbranch_code,ccbranch_name';
						$getStoreBranch 		= $CI->m_global->get_data_all('cc_branch',$getStoreBranchJoin,$getStoreBranchWhere,$getStoreBranchSelect);

						if($getStoreBranch){
							$data['redirectLink'] = base_url('wildCard/cashier/'.$getStoreBranch[0]->ccstore_code.'/'.$getStoreBranch[0]->ccbranch_code.'/'.$data['finalValue']);
						}else{
							$data['status']			= staticValue("status_request","not_found");
							$data['msg']			= langText(array('cabang',' ','tidak_ditemukan'));
							$data['redirectLink'] 	= base_url();
						}
					}else{
						$data['status']			= staticValue("status_request","not_found");
						$data['msg']			= langText("segera_hadir");
						$data['redirectLink'] 	= base_url();
					}
					
				}else{
					$data['status']			= staticValue("status_request","not_found");
					$data['msg']			= langText("segera_hadir");
					$data['redirectLink'] 	= base_url();
				}
				//------------------------------------------------------ cashier application end -----------------------------------------------
			}else{
				$data['status']			= staticValue("status_request","not_found");
				$data['msg']			= langText("data_tidak_ditemukan");
				$data['redirectLink'] 	= base_url();
			}
			
		}else{
			$data['redirectLink'] = base_url('wildCard/'.($data['isMasterToken'] ? 'masterCard/' : '').$data['finalValue']);
		}
	}elseif($functionName == 'landingpage_validation' || $functionName == 'set_cashier_session'){
		$get_app_role_join 		= array(
				array('ec_wild_card_app_connect', 'ecwcacsr_ecwcac_id=ecwcac_id'),
				array('ec_wild_card_app_connect_setting_name', 'ecwcacsr_ecwcacsn_code=ecwcacsn_code'),
			);
		if($option['cashierRuleCode'] == get_app_settings("ec_app_role_connect_cashier")){
			$additionalJoin = array('cc_branch','ccbranch_code=ecwcacsr_value');
			$additionalJoin2 = array('cc_store','ccstore_code=ccbranch_store_code');
			array_push($get_app_role_join,$additionalJoin,$additionalJoin2);
		}
		// dd($get_app_role_join);
			

		$get_app_role_where['ecwcacsr_value'] 				= $option['branch'];
		$get_app_role_where['ecwcacsn_code'] 				= $option['cashierRuleCode'];
		$get_app_role_where['ecwcac_glbl_prodtok_code'] 	= $get_wildcard->ecwc_glbl_prodtok_code;
		// $get_app_role_select 								= 'ecwcacsr_value,ecwcacsn_code';
		$get_app_role 										= $CI->m_global->get_data_all('ec_wild_card_app_connect_setting_relations',$get_app_role_join,$get_app_role_where);
		$data['data']['relationRole'] = $get_app_role[0];
		if(count($get_app_role) < 1){
			$data['status']			= staticValue("status_request","unauthorized");
			$data['msg']			= langText("kartu_liar_tidak_diijinkan_untuk_mengakses_halaman_ini");
			$data['redirectLink'] 	= base_url();
		}
	}
	if($functionName == 'set_cashier_session'){
		$data['unique_id'] = uniqid();
		$CI->session->set_userdata('public_cashier',$data);
	}
	return $data;
			
}

function set_app_system_setting($username){
	$CI      	 			= &get_instance();
	$where 					= array();
	$settedRole 								= array('product_count_by_package');
	$settedRoleAdditionalNumericPlusFunction 	= array('product_count_by_package');

	$get_myRolePackageJoin 					= array(
		array('role','role_code=uar_role_code'),
		array('packages','role_package_code=package_code'),
	);
	$get_myRolePackageWhere['uar_username']	= $username;
	$get_myRolePackageSelect 				= 'role_code,package_code';
	$get_myRolePackage 						= $CI->m_global->get_data_all('user_app_roles',$get_myRolePackageJoin,$get_myRolePackageWhere,$get_myRolePackageSelect);
	
	$myRoleCode = array();
	foreach($get_myRolePackage as $key => $value){
		array_push($myRoleCode,$value->role_code);
	}

	$get_role_settingJoin 								= array(
		array('role','rolesetting_role_code=role_code'),
		array('packages','role_package_code=package_code'),
		array('user_app_system_settings' , 'uass_app_code=role_app_code and rolesetting_name=uass_name and uass_username="'.$username.'"' , 'left')
	);
	$get_role_settingWhere								= null;
	$get_role_settingSelect								= 'rolesetting_role_code,rolesetting_name,rolesetting_value,rolesetting_note,role_app_code,uass_value,uass_value_additional,package_order';
	$get_role_settingWheree['where_in'][0]['keys']		= 'rolesetting_role_code';
	$get_role_settingWheree['where_in'][0]['values']	= $myRoleCode;
	$get_role_settingWheree['where_in'][1]['keys']		= 'rolesetting_name';
	$get_role_settingWheree['where_in'][1]['values']	= $settedRole;
	$get_role_setting 						= $CI->m_global->get_data_all('role_settings',$get_role_settingJoin,$get_role_settingWhere,$get_role_settingSelect,$get_role_settingWheree);

	$dataRoleSettings = array();
	foreach($get_role_setting as $key => $value){
		$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['uass_username'] 			= $username;
		$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['uass_app_code'] 			= $value->role_app_code;
		$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['uass_name'] 				= $value->rolesetting_name;
		$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['uass_note'] 				= $value->rolesetting_note;
		$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['uass_value_additional'] 	= $value->uass_value_additional;
		$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['uass_value'] 				= ((@$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['package_order'] == '' || @$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['package_order'] > $value->package_order) ? $value->rolesetting_value : $dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['uass_value'] );
		$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['package_order'] 			= (int)((@$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['package_order'] == '' || @$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['package_order'] > $value->package_order) ? $value->package_order : $dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['package_order'] );

		// if(in_array($value->rolesetting_name,$settedRoleAdditionalNumericPlusFunction)){
		// 	$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['uass_value'] = (int)$dataRoleSettings[$value->role_app_code][$value->rolesetting_name]['uass_value'] + 
		// }
	}
	$dataRoleSettingsInsert = array();
	foreach($dataRoleSettings as $key => $value){
		foreach($value as $key2 => $value2){
			$insert['uass_username']			= $value2['uass_username']; 		
			$insert['uass_app_code']			= $value2['uass_app_code']; 		
			$insert['uass_name']				= $value2['uass_name']; 			
			$insert['uass_note']				= $value2['uass_note']; 			
			$insert['uass_value_additional']	= $value2['uass_value_additional']; 
			$insert['uass_value']				= $value2['uass_value']; 	

			if(in_array($insert['uass_name'],$settedRoleAdditionalNumericPlusFunction)){
				$insert['uass_value'] = (int)$insert['uass_value'] + (int)$insert['uass_value_additional'];
			}
			array_push($dataRoleSettingsInsert,$insert);	
		}	
	}

	$deleteCurrentSetting = $CI->m_global->delete('user_app_system_settings',array('uass_username' => $username));
	if(count($dataRoleSettingsInsert) > 0){
		$insertCurrentSetting = $CI->m_global->insert_batch('user_app_system_settings',$dataRoleSettingsInsert);
	}else{
		$insertCurrentSetting = true;
	}

	if($insertCurrentSetting){
		$data['status']			= staticValue("status_request","success");
		$data['msg']			= langText("berhasil");
	}else{
		$data['status']			= staticValue("status_request","error");
		$data['msg']			= langText("error");
	}
	
	return $data;
}
?>