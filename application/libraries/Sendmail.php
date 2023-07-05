<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sendmail {
    
    private $username = "jktdsga";
    private $password = "jktdsga12345";

    private $sender_usr_gmail = 'cendanacoba@gmail.com';
    private $sender_pwd_gmail = 'ctunr2425';

    private $from = "<Niaga Online>";
    
    private $smtp = NULL;

    function cmd($str, $report=true ) {
        $ret = fwrite( $this->smtp, $str."\r\n" );
        if( $report === true )
            fread( $this->smtp, 587 );
    }

    function send($to, $cc, $subject, $msg) {
        $message = array();
        $message[] = "";
        $message[] = $msg;

        $this->smtp = fsockopen( "tcp://mail.garuda-indonesia.com", 587, $errno, $errstr );
        if( ! $this->smtp )
            die( "Email was not sent<br /> " );
        fread( $this->smtp, 512 );
        $this->cmd( "EHLO garuda-indonesia.com" );
        $this->cmd( "STARTTLS" );
        stream_socket_enable_crypto( $this->smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT );
        $this->cmd( "EHLO garuda-indonesia.com" );
        $this->cmd( "AUTH LOGIN" );
        //username
        $this->cmd( base64_encode( $this->username ) );
        //password
        $this->cmd( base64_encode( $this->password ) );
        $this->cmd( "MAIL FROM: <".strtoupper($this->username)."@garuda-indonesia.com>" );
        $this->cmd( "RCPT TO: <$to>" );
        $this->cmd( "RCPT TO: <$cc>" );
        $this->cmd( "DATA" );

        $this->cmd( "Date: ".date("r"), false );
        $this->cmd( "From: $this->from", false );
        $this->cmd( "Content-type: text/html ", false );
        $this->cmd( "Subject: $subject", false );
        $this->cmd( "To: $to", false );
        $this->cmd( "CC: $cc", false );
        //$this->cmd( $msg, false );
        for( $x=0; $x<sizeof($message); $x++ )
                $this->cmd( $message[$x], false );

        $this->cmd( "." );
        $this->cmd( "QUIT" );
        fclose( $this->smtp );
        return TRUE;
    }

    function gmail($to, $cc, $subject, $msg)
    {
        try{
            $conf = array();
            $conf['smtp_user']  = strtolower( $this->sender_usr_gmail );
            $conf['smtp_pass']  = $this->sender_pwd_gmail;  
            $conf['useremail']  = strtolower( $this->sender_usr_gmail );
            $conf['protocol']   = 'smtp';
            $conf['smtp_host']  = 'ssl://smtp.googlemail.com';
            $conf['smtp_port']  = '465';
            $conf['mailtype']   = 'html';
            $conf['charset']    = 'iso-8859-1';
            $conf['wordwrap']   = TRUE;
            $conf['charset']    ='iso-8859-1';
            
            $CI =& get_instance();
            $CI->load->library('email',$conf);
            
            $CI->email->set_newline("\r\n");
            $CI->email->from( $conf['useremail'], "Cendana Teknika Utama" );
            $CI->email->to( $to ); 
            $CI->email->subject( $subject );
            $CI->email->message( $msg );
            $CI->email->send();
            
            return TRUE;            
        }catch(Exception $e){
            return FALSE;
        }
    }
}