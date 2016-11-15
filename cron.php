#!/usr/bin/env php
<?php
require('vendor/autoload.php');
use JsonRPC\Client;
$projects = array();
$users = array();
$tasks = array();
$jsonrpc_auth_name = "jsonrpc";


if ( isset($argv[1]) ) {
	$database=$argv[1];
	$db = new SQLite3($database);
	$results = $db->query('SELECT option,value from settings where option IN ("imap_server","imap_username","imap_password","imap_server_port","imap_server_requires_ssl","imap_mail_prefix","api_token","imap_application_url","imap_guest_user_id")');
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
			$body_text = strip_tags(quoted_printable_decode(imap_fetchbody($mbox, $email_number,'1')));
			$task_id=preg_replace('/.*We created Task ID#/','',imap_utf8($header->subject));
			$to=$header->from[0]->mailbox.'@'.$header->from[0]->host;
			if ( $client->getTask($task_id) ) {
				$comment['content']="";
				$comment['task_id']=$task_id;
				$comment['user_id'] = "none"; 
        			foreach ($users_tmp as $user) {
                			if ( strtolower($user['email']) == strtolower($to) ){
                        			$comment['user_id'] = $user['id'];
                			}
        			}
				if ( $comment['user_id']=="none" && $imap_guest_user_id != "" ) {
					$comment['user_id'] = $imap_guest_user_id;
					$comment['content'].="From: ".$to."\r\n";
				} 
				$comment['content'].=$body_text;
				$response=$client->createComment($comment);	
			}
			else {
				if (strpos($header->to[0]->mailbox,$mail_prefix) !== false){
					$project_identifier = str_replace($mail_prefix,'',$header->to[0]->mailbox);
					if (isset($projects[$project_identifier])) {
					        $task['project_id'] = $projects[$project_identifier]['id'];
						$task['title'] = imap_utf8($header->subject);
						$task['description'] = $body_text;
						$task_id = $client->createTask($task);
						$subject = 'We created Task ID#'.$task_id;
						$body = 'You can track on Kanboard your request with the Task ID# '.$task_id;
						$headers = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						$headers .= 'From: '.$imap_username. "\r\n" .
		        	                'Reply-To:  <'.$header->to[0]->mailbox.'@'.$header->to[0]->host.'>'. "\r\n" .
		                	        'Subject: '.$subject."\r\n".
		                        	'To: '.$to."\r\n".
			                        'In-Reply-To: <'.$header->message_id.'>'. "\r\n" .
			                        'References: <'.$header->message_id.'>'. "\r\n" .
			                        'X-Mailer: PHP/' . phpversion();
						mail($to,$subject,$body,$headers);
					}
				}
				else {
					        preg_match('~<(.*?)>~', $header->subject, $output);
                                               	$project_identifier = $output[1];
	                                        if (isset($projects[$project_identifier])) {
	                                                $task['project_id'] = $projects[$project_identifier]['id'];
        	                                        $task['title'] = str_replace("<".$project_identifier.">","",imap_utf8($header->subject));
                	                                $task['description'] = $body_text;
                        	                        $task_id = $client->createTask($task);
                                	                $subject = 'We created Task ID#'.$task_id;
                                        	        $body = 'You can track on Kanboard your request with the Task ID# '.$task_id;
                                                	$headers = 'MIME-Version: 1.0' . "\r\n";
	                                                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        	                                        $headers .= 'From: '.$imap_username. "\r\n" .
                	                                'Reply-To:  <'.$header->to[0]->mailbox.'@'.$header->to[0]->host.'>'. "\r\n" .
                        	                        'Subject: '.$subject."\r\n".
                                	                'To: '.$to."\r\n".
                                        	        'In-Reply-To: <'.$header->message_id.'>'. "\r\n" .
                                                	'References: <'.$header->message_id.'>'. "\r\n" .
	                                                'X-Mailer: PHP/' . phpversion();
        	                                        mail($to,$subject,$body,$headers);
					}
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
