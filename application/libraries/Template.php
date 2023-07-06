<?php
class Template {

    protected $_ci;

    function __construct() 
    {
        $this->_ci = &get_instance();
    }


    function excel( $template, $data )
    {
        $this->_ci->load->view( $template, $data );
    }

    function f_print( $template, $data = NULL, $js = NULL, $css = NULL )
    {
        $data['_content']       = $this->_ci->load->view($template, $data, TRUE);
        $data['_css']           = $this->_ci->load->view('templates/css', $css, TRUE);
        $data['_base']          = $this->_ci->load->view('templates/template.php', $data);
    }

}

?>
