<?php

return array (
  'autoload' => false,
  'hooks' => 
  array (
    'app_init' => 
    array (
      0 => 'alioss',
    ),
    'upload_config_init' => 
    array (
      0 => 'alioss',
    ),
    'upload_delete' => 
    array (
      0 => 'alioss',
    ),
    'config_init' => 
    array (
      0 => 'third',
    ),
  ),
  'route' => 
  array (
    '/third$' => 'third/index/index',
    '/third/connect/[:platform]' => 'third/index/connect',
    '/third/callback/[:platform]' => 'third/index/callback',
    '/third/bind/[:platform]' => 'third/index/bind',
    '/third/unbind/[:platform]' => 'third/index/unbind',
    '/third/share' => 'third/index/share',
  ),
);