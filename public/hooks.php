<?php
require_once('../core.php');

// Get Github CIDR
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/meta');
curl_setopt($ch, CURLOPT_USERAGENT, 'bchrobot/webhook-handler');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);

$githubMetaJSON = json_decode($data);

// Validate remote IP address
$remote_ip = $_SERVER['REMOTE_ADDR'];
$valid_remote_ips = array_merge($githubMetaJSON->hooks, $gitlab_ips);

$checker = new Whitelist\Check();
try {
    $checker->whitelist($valid_remote_ips);
}
catch (InvalidArgumentException $e) {
    // thrown when an invalid definition is encountered
}

$remote_is_valid = $checker->check($remote_ip);

if (!$remote_is_valid)
{
	http_response_code(403);
	$response["remote"] = "invalid";
	die(json_encode($response));
}

// Validate payload if using Secret Token
if($using_secret_token)
{
	// TODO
}

// Process webhook payload
$payload = processPayload($HTTP_RAW_POST_DATA);

// Process hook.yml for repository if it's configured
if( array_key_exists($payload['repo_full_name'], $repositories) && is_array($repositories[$payload['repo_full_name']]))
{
	$repository = $repositories[$payload['repo_full_name']];
	processRepository($repository, $payload);
}


// Set response code and return JSON
http_response_code(200);
echo json_encode($response, JSON_UNESCAPED_SLASHES);
?>