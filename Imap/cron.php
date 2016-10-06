#!/usr/bin/env php
<?php
require('helpers.php');
require('vendor/autoload.php');
use JsonRPC\Client;
$projects = array();
$tasks = array();
$jsonrpc_auth_name = "jsonrpc";


if ( isset($argv[1]) ) {
	$database=$argv[1];
	$db = new SQLite3($database);
	$results = $db->query('SELECT option,value from settings where option IN ("imap_server","imap_username","imap_password","imap_server_port","imap_server_requires_ssl","imap_mail_prefix","api_token","imap_application_url")');
	while ($row = $results->fetchArray()) {
		switch ($row['option']) {
			case "imap_server":
				$imap_server = $row['value'];
				break;
			case "imap_username":
				$imap_username = $row['value'];
				break;
			case "imap_password":
				$imap_password = $row['value'];
				break;
			case "imap_server_port":
				$imap_server_port = $row['value'];
				break;
			case "imap_server_requires_ssl":
				$imap_server_requires_ssl = $row['value'];
				break;
			case "imap_mail_prefix":
				$mail_prefix = $row['value'];
				break;
			case "api_token":
				$jsonrpc_auth_token = $row['value'];
				break;
			case "imap_application_url":
				$jsonrpc_url= $row['value'];
				break;
			
		}
	}
	$client = new JsonRPC\Client($jsonrpc_url);
	$client->authentication($jsonrpc_auth_name, $jsonrpc_auth_token);
	$projects_tmp = $client->execute('getAllProjects');
	foreach ($projects_tmp as $proj) {
		if ($proj['identifier']){
			$projects[$proj['identifier']]['id'] = $proj['id'];
		}
	}
	$hostname='{'.$imap_server.':'.$imap_server_port.'/imap/ssl}INBOX';
	$mbox = imap_open("$hostname", "$imap_username", "$imap_password");
	if (!$mbox) {
		echo imap_last_error();
		exit;
	}
	$emails = imap_search($mbox,'UNSEEN');
	if ( $emails) {
		rsort($emails);
		foreach($emails as $email_number) {
			$header = imap_header($mbox, $email_number);
			$body_text = imap_fetchbody($mbox, $email_number,0);
			$body_html = getBody($email_number, $mbox);
			if (strpos($header->to[0]->mailbox,$mail_prefix) !== false){
				$project_identifier = str_replace($mail_prefix,'',$header->to[0]->mailbox);
				if (isset($projects[$project_identifier])){
				        $task['project_id'] = $projects[$project_identifier]['id'];
					$task['title'] = imap_utf8($header->subject);
					$task['description'] = $body_text;
					$response = $client->createTask($task);
				}
			}
		}
	}
}
else
{
	echo "You must specify the database path\n";
}
?>
