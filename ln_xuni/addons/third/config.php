<?php

return array (
  0 => 
  array (
    'name' => 'wechat',
    'title' => '微信',
    'type' => 'array',
    'content' => 
    array (
      'app_id' => '',
      'app_secret' => '',
      'callback' => '',
      'scope' => 'snsapi_base',
    ),
    'value' => 
    array (
      'app_id' => 'wx6d468797a8c8082a',
      'app_secret' => '0a8938d870f4bbe01a2878d913c9eccd',
      'scope' => 'snsapi_base',
    ),
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  1 => 
  array (
    'name' => 'rewrite',
    'title' => '伪静态',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => 
    array (
      'index/index' => '/third$',
      'index/connect' => '/third/connect/[:platform]',
      'index/callback' => '/third/callback/[:platform]',
      'index/bind' => '/third/bind/[:platform]',
      'index/unbind' => '/third/unbind/[:platform]',
      'index/share' => '/third/share',
    ),
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
);
