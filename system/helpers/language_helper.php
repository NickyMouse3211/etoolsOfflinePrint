<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Language Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/helpers/language_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists('lang'))
{

	function langcode($lang){
		$CI = &get_instance();
		$langcode = $CI->m_global->get_data_all('language',null,null,'language_code,language_name',null,null,0,null,null,array('array','language_name,language_code','a-b'));
		
		return $langcode[$lang];
	}
	/**
	 * Lang
	 *
	 * Fetches a language variable and optionally outputs a form label
	 *
	 * @param	string	$line		The language line
	 * @param	string	$for		The "for" value (id of the form element)
	 * @param	array	$attributes	Any additional HTML attributes
	 * @return	string
	 */
	function lang($line, $for = '', $attributes = array(), $BANoption = array(), $exit=false)
	{
		$CI = &get_instance();
		$parline  = $line;
		$statusUC = false;
		if ($attributes == '') {
			$attributes = array();
		}
		$linearr = $CI->lang->line($line,TRUE,TRUE,TRUE);
		$line 	 = is_array($linearr) ? $linearr[1]: $linearr;

		if ($CI->config->item( 'yandex_translate' ) == TRUE && $linearr[0] == '404') {
			//yandex need to be replaced with this
			// //https://global.xfyun.cn/doc/nlp/xftrans/API.html#requirements-for-the-interface
			// $body = '{"common":{"app_id":"g986baf6"},"business":{"from":"cn","to" :"en"},"data":{"text":"5LuK5aSp5aSp5rCU5oCO5LmI5qC377yf"}}';
			// $digiest = base64_encode(hash('sha256', $body));

			// $originalsign = "host: http://localhost\ndate: Wed, 20 Nov 2019 03:14:25 GMT\nPOST /v2/its HTTP/1.1\ndigest: SHA-256=".$digiest;
			
			// $signature_sha = hash_hmac('sha256', $originalsign, '4dd42c9ccf072a043b85e09fd302ea4c');

			// $signature = base64_encode($signature_sha);

			// echo $digiest.'<br/>';
			// echo $originalsign.'<br/>';
			// echo $signature_sha.'<br/>';
			// echo $signature.'<br/>';
			
			$host = 'https://translate.yandex.net';
			$text = $linearr[2];
			$langcode = @langcode($CI->session->userdata('site_lang')) != '' ? langcode($CI->session->userdata('site_lang')) : 'en';
			$key  = 'trnsl.1.1.20190305T065342Z.a76556ac63b87a7f.4245ab1d0d87a65831e5673e58fcf9db2fc8e669';
			$link = $host.'/api/v1.5/tr.json/translate?key='.$key.'&text='.urlencode(str_replace('_', ' ', $text)).'&lang='.$langcode;
			$ch = curl_init();

			$json = file_get_contents($link);
			$result = json_decode($json);

			$line = $result->text[0];
		}
		// echo $line;exit();
		// pre($CI);
		$ori  = $line;
		// pre($line,1);
		if (@$BANoption['UC'] != '') {
			$statusUC = 'None';
			if (@$BANoption['UC'] == 'all') {
				$line     = ucwords($line);
				$statusUC = 'all';
			}elseif(@$BANoption['UC'] == 'tittle' || @$BANoption['UC'] == 'title') {
				$line = ucwords($line);
				$line = str_replace(' To ', ' to ', $line);
				$line = str_replace(' Or ', ' or ', $line);
				$line = str_replace(' Of ', ' of ', $line);
				$line = str_replace(' For ', ' for ', $line);
				$line = str_replace(' And ', ' and ', $line);
				$line = str_replace(' An ', ' an ', $line);
				$line = str_replace(' Untuk ', ' untuk ', $line);
				$line = str_replace(' Dan ', ' dan ', $line);
				$line = str_replace(' Dari ', ' dari ', $line);
				$line = str_replace(' Ke ', ' ke ', $line);
				$line = str_replace(' Atau ', ' atau ', $line);
				$statusUC = 'tittle';
			}elseif(@$BANoption['UC'] == 'first') {
				$line     = ucfirst($line);
				$statusUC = 'first';
			}
		}

		if ($for !== '')
		{
			$line = '<label for="'.$for.'"'._stringify_attributes($attributes).'>'.$line.'</label>';
		}

		if ($exit == true) {
			pre(
				array(
					'param' => array(
								'line' => $parline,
								'for'  => $for,
								'attribute' => $attributes,
								'option' => $BANoption,
							),
					'status' => array(
								'UC' => $statusUC,
							),
					'original'   => $ori,
					'result'     => $line,
					'manualtest' => $CI->lang->line('anggota'),
				),1
			);
		}
		return $line;
	}

	function langText($string = null,$uc = 'first'){
		$return = '';
		if (!is_array($string)) {
			$text_append = lang($string,'','',array('UC' => $uc));
			if (!like_match('%Could not find the language line%',$text_append)) {
				$return = $text_append;
			}else{
				$return = $string;
			}
		}else{
			foreach ($string as $key => $value) {
				$text_append = lang($value,'','',array('UC' => $uc));
				if ($key > 0) {
					$return = $return.' '; 	
				}
				if (!like_match('%Could not find the language line%',$text_append)) {
					$return = $return.$text_append;
				}else{
					$return = $return.$value;
				}
			}
		}
		return $return;
	}
}
