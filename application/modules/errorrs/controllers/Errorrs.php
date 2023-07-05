<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Errorrs extends MX_Controller {

    private $prefix         = 'error';

	function __construct() {
        parent::__construct();
    }

	public function error_404()
	{
        $data['pagetitle']  = 'Error';
        $data['subtitle']   = 'error 404';

        $data['prefix']     = $this->prefix; // uri/controller

        $data['breadcrumb'] = [ 'Error' => $this->prefix.'/error_404' ];

        $js['js']           = null;
        $css['css']         = [ 'error' ];

        // $data['url']        = $this->input->post('url');
        // $data['url1']        = $this->input->post('url1');

        $this->template->display( $this->prefix.'_404', $data, $js, $css );
	}

}