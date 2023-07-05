<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

 // create hook for multi langunage
  $hook['post_controller_constructor'] = array(
      'class'    => 'MultiLanguageLoader',
      'function' => 'initialize',
      'filename' => 'MultiLanguageLoader.php',
      'filepath' => 'hooks'
  );

?>