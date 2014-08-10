<?php
//`cd /var/www/foo.lake-sunapee.org; git pull`;
//die();


// Require dependencies
if(!file_exists("../config/config.php"))
{
	http_response_code(400);
	die(json_encode(array(
		"bad?" => "yeah"
	)));
}
require_once("../config/config.php");
require_once("../lib/CIDR.php");

// Prepare response
header('Content-Type: application/json');
$response = array('success' => false);

// Get Github CIDR
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/meta');
curl_setopt($ch, CURLOPT_USERAGENT, 'bchrobot/webhook-handler');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);

$githubMetaJSON = json_decode($data);

// Validate where request is coming from
$remote = $_SERVER['REMOTE_ADDR'];
$response["remote"] = array("ip" => $remote, "provider" => "unknown");
$match = false;
// Check Github
foreach ($githubMetaJSON->hooks as $cidr)
{
	if (CIDR::IPisWithinCIDR($remote, $cidr))
	{
		$match = true;
		$response["remote"]["provider"] = "Github";
		break;
	}
}
// Check user-defined Gitlab IPs
foreach ($gitlab_ips as $gitlab_ip)
{
	if ($remote === $gitlab_ip)
	{
		$match = true;
		$response["remote"]["provider"] = "Gitlab";
		break;
	}
}
if (!$match)
{
	http_response_code(403);
	die(json_encode($response));
}

// Validate payload if using Secret Token
if($using_secret_token)
{
	// TODO
}

// Process webhook payload
$headers = getallheaders();
$response["event"] = $headers['X-GitHub-Event'];
$webhookJSON = json_decode($HTTP_RAW_POST_DATA);
$repoName = $webhookJSON->repository->full_name;

if( array_key_exists($repoName, $repositories) && is_array($repositories[$repoName]))
{
	$repoDir = $repositories[$repoName]['dir'];
	$response["repo"] = array("name" => $repoName, "repoDir" => $repoDir);

	chdir($repoDir);
	$response["shell-git"] = shell_exec("sudo -u webhook-handler /usr/bin/git pull 2>&1");
}


// blah; remove this section
// $response["blah"] = $webhookJSON->repository->full_name;

// Set response code and return JSON
http_response_code(200);
echo json_encode($response, JSON_UNESCAPED_SLASHES);
?>