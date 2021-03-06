<?php
require_once('vendor/autoload.php');
require_once('config/config.php');

// Prepare response
header('Content-Type: application/json');
$response = array();

/**
 * processPayload
 */
function processPayload($raw_payload)
{
	$payload = array();

	// Get event type
	$headers = getallheaders();
	$payload["event"] = $headers['X-GitHub-Event'];

	// Get repo details
	$webhookJSON = json_decode($raw_payload);
	$payload["repo_full_name"] = $webhookJSON->repository->full_name;

	return $payload;
}


function processRepository($repository, $hook_payload)
{
	global $response;

	$repo_directory = $repository['dir'];
	$yml_location = array_key_exists('yml', $repository) ? $repo_directory.'/'.$repository['yml'] : $repo_directory . '/hook.yml';

	// Pull Git repo
	chdir($repo_directory);
	$response['shell-git'] = shell_exec('sudo -u webhook-handler /usr/bin/git pull 2>&1');

	// TODO handle yml emails

	// TODO handle yml commands
}
?>