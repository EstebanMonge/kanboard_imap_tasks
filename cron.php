#!/usr/bin/env php
<?php
require('vendor/autoload.php');
use JsonRPC\Client;
use Fetch\Server;
use Fetch\Message;

$projects = array();
$users = array();
$tasks = array();
$jsonrpc_auth_name = "jsonrpc";
$toaddress="";
$ccaddress="";

if ( isset($argv[1]) ) {
	$database=$argv[1];
	$db = new SQLite3($database);
	$results = $db->query('SELECT option,value from settings where option IN ("imap_body_message","imap_default_priority","imap_server","imap_username","imap_password","imap_server_port","imap_server_requires_ssl","imap_mail_prefix","api_token","imap_application_url","imap_guest_user_id")');
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
			case "imap_guest_user_id":
				$imap_guest_user_id= $row['value'];
				break;
			case "imap_default_priority":
				$default_priority= $row['value'];
				break;
			case "imap_body_message":
				$body_message= $row['value'];
				break;
			
		}
	}
	$client = new JsonRPC\Client($jsonrpc_url);
	$client->authentication($jsonrpc_auth_name, $jsonrpc_auth_token);
	$projects_tmp = $client->execute('getAllProjects');
	$users_tmp= $client->execute('getAllUsers');
	foreach ($projects_tmp as $proj) {
		if ($proj['identifier']){
			$projects[$proj['identifier']]['id'] = $proj['id'];
		}
	}
	$server = new Server($imap_server,993);
	$server->setAuthentication($imap_username, $imap_password);
	$messages = $server->search('UNSEEN');
		foreach ($messages as $message) {
			$body_text=$message->getMessageBody();
                        preg_match('~TaskID#\[(.*?)\]~', $message->getSubject(), $output);
			$task_id = $output[1];
			$to = $message->getAddresses("to"); 
			$cc = $message->getAddresses("cc");
			$from = $message->getAddresses("from");
			$header=$message->getHeaders();
			if (!isset($cc)) {
				foreach ($cc as $ccfor) {
					if ( "$imap_username" != $ccfor['address'] ) {
						$ccaddress .= $ccfor['address'].",";
					}
				}
			}
			$ccaddress=substr_replace($ccaddress, "", -1);
			foreach ($to as $tofor) {
				if ( "$imap_username" != $tofor['address'] ) {
					$toaddress .= $tofor['address'].",";
				}
			}
			$toaddress=substr_replace($toaddress, "", -1);
			if ( $client->getTask($task_id) ) {
				$comment['content']="";
				$comment['task_id']=$task_id;
				$comment['user_id'] = "none"; 
        			foreach ($users_tmp as $user) {
                			if ( strtolower($user['email']) == $to ){
                        			$comment['user_id'] = $user['id'];
                			}
        			}
				if ( $comment['user_id']=="none" && $imap_guest_user_id != "" ) {
					$comment['user_id'] = $imap_guest_user_id;
					$comment['content'].="From: ".$from['address']."\r\n";
				}
				$comment['content'].=$body_text;
				$response=$client->createComment($comment);	
			}
			else {
				if (strpos($to[0]['address'],$mail_prefix) !== false){
					$project_identifier = str_replace($mail_prefix,'',$to[0]['address']);
					$project_identifier = strstr($project_identifier, '@', true);
					if (isset($projects[strtoupper($project_identifier)])) {
					        $task['project_id'] = $projects[strtoupper($project_identifier)]['id'];
					}
				}
				else {
					        preg_match('~<(.*?)>~', $message->getSubject(), $output);
                                               	$project_identifier = $output[1];
	                                        if (isset($projects[strtoupper($project_identifier)])) {
	                                                $task['project_id'] = $projects[strtoupper($project_identifier)]['id'];
					}
				}
                                $task['title'] = str_replace("<$project_identifier>",'',$message->getSubject());
                                $task['description'] = $body_text;
                                $task['priority'] = $default_priority;
                                $task_id = $client->createTask($task);

                                                       $subject = "Re: ".$message->getSubject()." TaskID#[".$task_id."]";
#                                                       $body = 'You can track on Kanboard your request with the Task ID# '.$task_id;
                                                       $headers = 'MIME-Version: 1.0' . "\r\n";
                                                       $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                                                       $headers .= 'From: '.$imap_username. "\r\n" .
                                                       'Reply-To:  <'.$imap_username.'>'. "\r\n" .
                                                       'Subject: '.$subject."\r\n".
                                                       'To: '.$from['address']."\r\n".
                                                       'CC: '.$toaddress.','.$ccaddress."\r\n".
                                                       'In-Reply-To: <'.$header->message_id.'>'. "\r\n" .
                                                       'References: <'.$header->message_id.'>'. "\r\n" .
                                                       'X-Mailer: PHP/' . phpversion();
                                                       mail($toaddress,$subject,$body_message,$headers);			}
		}
	}
else
{
	echo "You must specify the database path\n";
}
?>
