<?php
function get_user_ip()
{
	if(isset($_SERVER['HTTP_CF_CONNECTING_IP']))
	{
		$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
		$_SERVER['HTTP_CLIENT_IP'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
	}

	$client = @$_SERVER['HTTP_CLIENT_IP'];
	$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	if(isset($_SERVER['REMOTE_ADDR']))
		$remote = $_SERVER['REMOTE_ADDR'];
	else
		$remote = '';

	if(filter_var($client, FILTER_VALIDATE_IP))
		return $client;
	elseif(filter_var($forward, FILTER_VALIDATE_IP))
		return $forward;
	else
		return $remote;
}

function sp_crypt($str)
{
	$remote_addr = get_user_ip();
	if(isset($_SERVER['HTTP_USER_AGENT']))
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
	else
		$user_agent = '';

	if(empty($remote_addr) && empty($user_agent))
		exit('Error : <b>Unable to get user information</b>');

	return sha1($remote_addr . md5($str) . $user_agent);
}

function gencode($nb)
{
	$cars = 'azertyuiopqsdfghjklmwxcvbn0123456789';
	$mt_max = strlen($cars) - 1;
	$return = '';
	for($i = 0; $i < $nb; $i++)
		$return .= $cars[mt_rand(0, $mt_max)];
	return $return;
}

function server_infos()
{
	$web_http = 'http';
	if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
		$web_http .= 's';
	elseif(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
		$web_http .= 's';
	elseif(isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https')
		$web_http .= 's';
	$web_http .= '://';

	if(isset($_SERVER['HTTP_HOST']))
		$web_root = $_SERVER['HTTP_HOST'];
	elseif(isset($_SERVER['SERVER_NAME']))
		$web_root = $_SERVER['SERVER_NAME'];
	else
		$web_root = 'domain-not-found';

	if(isset($_SERVER['SCRIPT_NAME']))
		$script_name = $_SERVER['SCRIPT_NAME'];
	elseif(isset($_SERVER['PHP_SELF']))
		$script_name = $_SERVER['PHP_SELF'];
	else
		$script_name = false;

	if(isset($_SERVER['DOCUMENT_ROOT']))
	{
		$server_root = $_SERVER['DOCUMENT_ROOT'];
		if($script_name === false)
		{
			if(isset($_SERVER['SCRIPT_FILENAME']))
			{
				$script_filename = $_SERVER['SCRIPT_FILENAME'];
				if(strpos($script_filename, $server_root) === 0)
					$script_name = substr($script_filename, strlen($server_root));
				else
					return false;
			}
			else
				return false;
		}
	}
	elseif(isset($_SERVER['SCRIPT_FILENAME']))
	{
		$script_filename = $_SERVER['SCRIPT_FILENAME'];
		if($script_name !== false)
		{
			$lng_script_filename = strlen($script_filename);
			$lng_script_name = strlen($script_name);
			if(strpos($script_filename, $script_name) === $lng_script_filename - $lng_script_name)
				$server_root = substr($script_filename, 0, $lng_script_filename - $lng_script_name);
			else
				return false;
		}
		else
			return false;
	}
	else
		return false;

	$return['web_root'] = $web_root;
	$return['web_http'] = $web_http;
	$return['server_root'] = $server_root;
	$return['script'] = $script_name;
	return $return;
}
