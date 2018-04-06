<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

$route['admin'] = 'admin/dashboard';

$controllers_methods = array(
  'de' => array(
      'willkommen/list' => 'welcome/list',
      'willkommen' => 'welcome'
    ),
  'fr' => array(
      'bienvenu/list' => 'welcome/list',
    '  bienvenu' => 'welcome'
    )
);

$route['^(\w{2})/(.*)'] = function($language, $link) use ($controllers_methods)
{
  if(array_key_exists($language,$controllers_methods))
  {
    foreach($controllers_methods[$language] as $key => $sym_link)
    {
      if(strrpos($link, $key,0) !== FALSE)
      {
        $new_link = ltrim($link,$key);
        $new_link = $sym_link.$new_link;
        break;
      }
    }
    return $new_link;
  }
  return $link;  
};
$route['^(\w{2})$'] = $route['default_controller'];