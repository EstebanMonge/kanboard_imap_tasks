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
$toaddress = "";
$ccaddress = "";
$comment['content'] = "";
$task['description'] = "";
$argv_db = $argv[1];
$debug = ($argv_db == '--debug' || $argv_db == '-d');

if ($debug) {
    echo "Debug mode enabled.\n";
    $argv_db = $argv[2];
}

if (isset($argv_db) && file_exists($argv_db)) {
    // Database to connect to and default settings.
	$database = $argv_db;
	$imap_server_port = 0;
	$imap_server_port_default = 993;
	$mail_prefix = null;
	$imap_automatic_replies = 0;
	$imap_enabled = 0;
	$default_priority = 0;

	// Load settings.
    if ($debug) {
        echo "Opening ".$database.".\n";
    }
	$db = new SQLite3($database, SQLITE3_OPEN_READONLY);
	$results = $db->query(
	        'SELECT option, value 
                    FROM settings 
                    WHERE option IN ("imap_body_message", "imap_default_priority", "imap_server", "imap_username",
                                     "imap_password", "imap_server_port", "imap_server_requires_ssl",
                                     "imap_mail_prefix", "api_token", "imap_application_url", "imap_guest_user_id",
                                     "imap_task_description_message", "imap_enabled", "imap_automatic_replies")');

	while ($row = $results->fetchArray()) {
		switch ($row['option']) {
            case "imap_enabled":
                $imap_enabled = intval($row['value']);
                break;
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
				$imap_server_port = intval($row['value']);
				break;
			case "imap_server_requires_ssl":
				$imap_server_requires_ssl = $row['value'];
				$imap_server_port_default = $row['value'] ? 993 : 143;
				break;
			case "imap_mail_prefix":
			    if (!empty($row['value'])) {
                    $mail_prefix = $row['value'];
                }
				break;
			case "api_token":
			    // Detect the JSON RPC token from the database.
				$jsonrpc_auth_token = $row['value'];
				break;
			case "imap_application_url":
				$jsonrpc_url = $row['value'];
				break;
			case "imap_guest_user_id":
				$imap_guest_user_id = $row['value'];
				break;
			case "imap_default_priority":
			    if ($row['value']) {
                    $default_priority = $row['value'];
                }
				break;
            case "imap_automatic_replies":
                $imap_automatic_replies = intval($row['value']);
                break;
			case "imap_body_message":
				$body_message_configured = $row['value'];
				break;
			case "imap_task_description_message":
				$task_description_message = $row['value'];
				break;

		}
	}

	// There is no reason to hold the database connection open.
	$db->close();
	if ($debug) {
	    echo "Database closed.\n";
    }

	// Turn off the rest of our processing without removing the plugin.
	if (!$imap_enabled) {
	    if ($debug) {
	        echo "Plugin disabled. Exiting.";
        }
	    exit;
    }

	// Default to the RFC 5233 Subaddress Extension format.
	if (!$mail_prefix) {
	    $mail_prefix = preg_replace('/@.*$/', '', $imap_username).'+';
    }

	// Connect to Kanboard via XmlRPC.
    if ($debug) {
        echo "Using JSONRPC endpoint of: ".$jsonrpc_url."\n";
    }
	$client = new JsonRPC\Client($jsonrpc_url);
	$client->authentication($jsonrpc_auth_name, $jsonrpc_auth_token);
	$projects_tmp = $client->execute('getAllProjects');
	$users_tmp = $client->execute('getAllUsers');
	foreach ($projects_tmp as $proj) {
		if ($proj['identifier']) {
			$projects[$proj['identifier']]['id'] = $proj['id'];
		}
	}

	// Connect to the IMAP server.
    if ($debug) {
        echo 'Connecting to IMAP server '.$imap_server.':'.$imap_server_port.' (SSL'.($imap_server_requires_ssl ? '' : ' not ')." required).\n";
    }
	$imap_server_port = $imap_server_port ? $imap_server_port : $imap_server_port_default;
	$server = new Server($imap_server, $imap_server_port);
	$server->setAuthentication($imap_username, $imap_password);

	// Override Server initialization based on the user's configuration.
	if ($imap_server_requires_ssl) {
		$server->setFlag('ssl');
	} else {
		$server->setFlag('novalidate-cert');
	}

	try {
        $messages = $server->search('UNSEEN');
    } catch (Exception $e) {
	    throw new Exception("Error connecting to IMAP server $imap_server:$imap_server_port with".($imap_server_requires_ssl ? '' : 'out').
            " SSL as $imap_username: ". $e->getMessage());
    }

	if ($debug) {
	    echo 'Found '.count($messages)." new message(s).\n";
    }

	// Loop through the unread email messages.
    foreach ($messages as $message) {
        $body_text = $message->getMessageBody();
        $body_html = $message->getMessageBody($html = true);
        $to = $message->getAddresses("to");
        $cc = $message->getAddresses("cc");
        $from = $message->getAddresses("from");
        $header = $message->getHeaders();
        $task_id = null;

        if ($debug) {
            echo 'New Message from '.$from['address'].': '.$message->getSubject();
        }

        // Parse the important email addresses.
        if (!empty($cc)) {
            foreach ($cc as $ccfor) {
                if ($imap_username != $ccfor['address']) {
                    $ccaddress .= $ccfor['address'] . ",";
                }
            }
            // Remove the last comma.
            $ccaddress = substr_replace($ccaddress, "", -1);
        }
        foreach ($to as $tofor) {
            if ($imap_username != $tofor['address']) {
                $toaddress .= $tofor['address'].",";
            }
        }
        $toaddress = substr_replace($toaddress, "", -1);

        // Prepare to email the user back.
        $body_message = "<br><br>----- Original Message -----<br><br>";
        $body_message .= "From: ".$from['address']."\n";
        $body_message .= "To: ".$toaddress."\n";
        if ($ccaddress) {
            $body_message .= "Cc: " . $ccaddress . "\n";
        }
        $body_message .= $body_html;
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= 'From: '.$imap_username. "\r\n" .
                'Reply-To: <'.$imap_username.'>'. "\r\n" .
                'To: '.$from['address']."\r\n".
                'CC: '.$toaddress.','.$ccaddress."\r\n".
                'In-Reply-To: <'.$header->message_id.'>'. "\r\n" .
                'References: <'.$header->message_id.'>'. "\r\n" .
                'X-Mailer: PHP/' . phpversion(). "\r\n";

        // Try to detect the task ID from the subject line.
        if (preg_match('~TaskID#\[(.*?)\]~', $message->getSubject(), $output)) {
            $task_id = $output[1];
        }

        $comment['user_id'] = $imap_guest_user_id;
        $task['creator_id'] = $imap_guest_user_id;
        foreach ($users_tmp as $user) {
            if (strtolower($user['email']) == strtolower($from['address'])) {
                $comment['user_id'] = $user['id'];
                $task['creator_id'] = $user['id'];
            }
        }
        if ($comment['user_id'] == $imap_guest_user_id && $imap_guest_user_id != "") {
            $comment['content'] .= "From: ".strtolower($from['address'])."\r\n";
            $task['description'] .= "From: ".strtolower($from['address'])."\r\n";
        }

        // If a valid Task ID was specified, add a comment to it.
        if ($task_id && $client->getTask($task_id)) {
            if ($debug) {
                echo 'Found Task ID: '.$task_id.". Adding comment.\n";
            }
            $comment['task_id'] = $task_id;
            $comment['content'] .= $body_text;
            $response = $client->createComment($comment);

            if ($imap_automatic_replies) {
                if ($debug) {
                    echo "Replying with confirmation.\n";
                }
                $subject = "Re: Added your comment on task " . $task_id;
                $body_message = 'I added your comment on ' . $task_id . '.' . $body_message;
                $headers .= 'Subject: ' . $subject;
                if (!mail($toaddress, $subject, $body_message, $headers)) {
                    throw new Exception('Failed to send comment confirmation.');
                }
            }
        } else {
            // Try to detect the project that we want.
            if (strpos($to[0]['address'], $mail_prefix) !== false) {
                $project_identifier = str_replace($mail_prefix, '', $to[0]['address']);
                $project_identifier = strstr($project_identifier, '@', true);
                if ($debug) {
                    echo 'Found project identifier in subject line: '.$project_identifier.".\n";
                }
                if (isset($projects[strtoupper($project_identifier)])) {
                    $task['project_id'] = $projects[strtoupper($project_identifier)]['id'];
                } else if ($debug) {
                    echo "Project identifier does not seem to exist in the database.\n";
                }
            } else {
                if (preg_match('~<(.*?)>~', $message->getSubject(), $output)) {
                    $project_identifier = $output[1];
                    if ($debug) {
                        echo 'Found project identifier in subject line: '.$project_identifier.".\n";
                    }
                    if (isset($projects[strtoupper($project_identifier)])) {
                        $task['project_id'] = $projects[strtoupper($project_identifier)]['id'];
                    } else if ($debug) {
                        echo "Project identifier does not seem to exist in the database.\n";
                    }
                } else if ($imap_automatic_replies) {
                    // We failed to detect which project the user wanted. Send a rejection notice.
                    if ($debug) {
                        echo "Unable to determine project identifier. Replying with failure message.\n";
                    }
                    $subject = "Re: ERROR: ".$message->getSubject();
                    $body_message = 'Sorry, I was not able to determine which project this was for.'.$body_message;
                    $headers .= 'Subject: '.$subject;
                    if (!mail($toaddress, $subject, $body_message, $headers)) {
                        throw new Exception('Failed to send failure message.');
                    }
                    continue;
                }
            }

            // Create a new task.
            $task['title'] = str_replace("<$project_identifier>", '', $message->getSubject());
            if (!empty($task_description_message)) {
                $task['description'] .= "\r\n" . $task_description_message;
            }
            $task['description'] .= "\r\n".$body_text;
            $task['priority'] = $default_priority;
            $task_id = $client->createTask($task);

            // Confirm to the sender that we created a new task.
            if ($imap_automatic_replies) {
                if ($debug) {
                    echo "Replying with confirmation.\n";
                }

                $subject = "Re: " . $message->getSubject() . " TaskID#[" . $task_id . "]";
                $body_message = str_replace('$task_id', $task_id, $body_message_configured) . $body_message;
                $headers .= 'Subject: ' . $subject;
                if (!mail($toaddress, $subject, $body_message, $headers)) {
                    throw new Exception('Failed to send task creation confirmation.');
                }
            }
        }
    }
} else {
	throw new Exception("You must specify the database path.");
}
