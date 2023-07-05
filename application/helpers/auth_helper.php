<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	$CI = &get_instance();
	$CI->load->library( 'session' );

	$ex = array('login');

	$user_data 		= @$CI->session->userdata('userdata');

    $status_link    = @$CI->input->post('status_link');

    $uri_link 		= '/apl/' . $CI->uri->segment(1) . '/' . $CI->uri->segment(2);
    $list_bypass 	= [
						'fetch',
    				  ];

    if(!in_array($CI->uri->segment(1), $list_bypass)) {
		redirect("https://e-toolsapps.com");
	}