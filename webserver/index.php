<?php
	require_once('config.php');
	set_time_limit(0);
	header('Content-Type: application/json; charset=' . (defined('CHARSET_OUTPUT') ? CHARSET_OUTPUT : 'UTF-8'));
	
	if(defined('DEBUG') && DEBUG) {
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
	}
	
	$response		= (object) null;
	$response->time		= time();
	$response->error	= false;
	
	/* Check if the request is only HTTPS */
	if(defined('HTTPS_ONLY') && HTTPS_ONLY && $_SERVER['HTTPS'] !== 'on') {
		$response->error	= true;
		$response->message	= 'Only HTTP-Connections allowed!';
		
		if(defined('DEBUG') && DEBUG) {
			$response->debug = 'HTTP-Reuest is: ' . $_SERVER['HTTPS'];
		}
	}
	
	/* Check if the UserAgent is from AppServer */
	if(!$response->error && defined('USERAGENT_APPSERVER') && USERAGENT_APPSERVER && !preg_match('/^KnuddelsAppServer\/(?<version>[0-9\.]+) \(AppServer:(?<appserver>[\d]+); ChatServer:(?<chatserver>[\d]+)\)$/Uis', $_SERVER['HTTP_USER_AGENT'], $client)) {
		$response->error	= true;
		$response->message	= 'Forbidden Client!';
		
		if(defined('DEBUG') && DEBUG) {
			$response->debug = [
				'UserAgent'	=> $_SERVER['HTTP_USER_AGENT'],
				'Version'	=> [
					'Client'	=> isset($client['version']) ? $client['version'] : null,
					'AppServer'	=> isset($client['appserver']) ? $client['appserver'] : null,
					'ChatServer'	=> isset($client['chatserver']) ? $client['chatserver'] : null
				]
			];
		}
	}
	
	if(!$response->error && defined('USERAGENT_APPSERVER') && USERAGENT_APPSERVER) {
		/* Check explicit AppServer Version */
		if(!$response->error && defined('APPSERVER_VERSION') && APPSERVER_VERSION) {
			if(!isset($_SERVER['HTTP_K_APPSERVERREVISION']) || !isset($client['appserver']) || ($client['appserver'] !== $_SERVER['HTTP_K_APPSERVERREVISION'])) {
				$response->error	= true;
				$response->message	= 'Forbidden AppServer!';
				
				if(defined('DEBUG') && DEBUG) {
					$response->debug = 'AppServer is: ' . (isset($_SERVER['HTTP_K_APPSERVERREVISION']) ? $_SERVER['HTTP_K_APPSERVERREVISION'] : null);
				}
			}
		}
		
		/* Check explicit ChatServer Version */
		if(!$response->error && defined('CHATSERVER_VERSION') && CHATSERVER_VERSION) {
			if(!isset($_SERVER['HTTP_K_CHATSERVERREVISION']) || !isset($client['chatserver']) || ($client['chatserver'] !== $_SERVER['HTTP_K_CHATSERVERREVISION'])) {
				$response->error	= true;
				$response->message	= 'Forbidden ChatServer!';
				
				if(defined('DEBUG') && DEBUG) {
					$response->debug = 'ChatServer is: ' . (isset($_SERVER['HTTP_K_CHATSERVERREVISION']) ? $_SERVER['HTTP_K_CHATSERVERREVISION'] : null);
				}
			}
		}
	}
	
	/* Check if Server is allowed */
	if(!$response->error && defined('ALLOWED_CHATSERVERS') && ALLOWED_CHATSERVERS !== '*' && ALLOWED_CHATSERVERS !== false) {
		$allowed = explode(',', ALLOWED_CHATSERVERS);
		
		if(!isset($_SERVER['HTTP_K_CHATSERVER']) || preg_match('/^knuddels(?<server>DEV|DE|CH|COM|AT|TEST)$/Uis', $_SERVER['HTTP_K_CHATSERVER'], $chatserver) && !in_array($chatserver['server'], $allowed) && !in_array('*', $allowed)) {
			$response->error	= true;
			$response->message	= 'ChatServer is not allowed!';
			
			if(defined('DEBUG') && DEBUG) {
				$response->debug = [
					'Given'		=> (isset($_SERVER['HTTP_K_CHATSERVER']) ? $_SERVER['HTTP_K_CHATSERVER'] : null),
					'Allowed'	=> $allowed
				];
			}
		}
	}
	
	/* Check if developer is allowed */
	if(!$response->error && defined('ALLOWED_DEVELOPERS') && ALLOWED_DEVELOPERS) {
		$developers = (file_exists('knuddelsAccess.txt') ? array_filter(explode(PHP_EOL, file_get_contents('knuddelsAccess.txt'))) : null);

		if(!isset($_SERVER['HTTP_K_DEVELOPERID']) || $developers === null || !in_array($_SERVER['HTTP_K_DEVELOPERID'], $developers) && !in_array('*', $developers)) {
			$response->error	= true;
			$response->message	= 'Developer is not allowed!';
			
			if(defined('DEBUG') && DEBUG) {
				$response->debug = [
					'Given'		=> (isset($_SERVER['HTTP_K_DEVELOPERID']) ? $_SERVER['HTTP_K_DEVELOPERID'] : null),
					'Allowed'	=> array_filter($developers, function($value) {
						return is_numeric($value);
					})
				];
			}
		}
	}
	
	/* Check if channel is allowed */
	if(!$response->error && defined('ALLOWED_CHANNELS') && ALLOWED_CHANNELS) {
		$channels = (file_exists('knuddelsAccess.txt') ? array_filter(explode(PHP_EOL, file_get_contents('knuddelsAccess.txt'))) : null);

		if(!isset($_SERVER['HTTP_K_CHANNEL']) || $channels === null || !in_array('# Channel: ' . $_SERVER['HTTP_K_CHANNEL'], $channels) && !in_array('# Channel: *', $channels)) {
			$response->error	= true;
			$response->message	= 'Channel is not allowed!';
			
			if(defined('DEBUG') && DEBUG) {
				$response->debug = [
					'Given'		=> (isset($_SERVER['HTTP_K_CHANNEL']) ? $_SERVER['HTTP_K_CHANNEL'] : null),
					'Allowed'	=> array_filter($channels, function($value) {
						return (strpos($value, '# Channel: ') === 0);
					})
				];
			}
		}
	}
	
	/* Check if app is allowed */
	if(!$response->error && defined('ALLOWED_APPS') && ALLOWED_APPS) {
		$apps = (file_exists('knuddelsAccess.txt') ? array_filter(explode(PHP_EOL, file_get_contents('knuddelsAccess.txt'))) : null);

		if(!isset($_SERVER['HTTP_K_APPKEY']) || $apps === null || !in_array('# App: ' . $_SERVER['HTTP_K_APPKEY'], $apps) && !in_array('# App: *', $apps)) {
			$response->error	= true;
			$response->message	= 'UserApp is not allowed!';
			
			if(defined('DEBUG') && DEBUG) {
				$response->debug = [
					'Given'		=> (isset($_SERVER['HTTP_K_APPKEY']) ? $_SERVER['HTTP_K_APPKEY'] : null),
					'Allowed'	=> array_filter($apps, function($value) {
						return (strpos($value, '# App: ') === 0);
					})
				];
			}
		}
	}
	
	/* Connect to Database */
	try {
		$database = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s', DATABASE_HOSTNAME, DATABASE_PORT, DATABASE_NAME), DATABASE_USERNAME, DATABASE_PASSWORD, array(
			PDO::MYSQL_ATTR_INIT_COMMAND	=> 'SET NAMES ' . (defined('CHARSET_DATABASE') ? CHARSET_DATABASE : 'utf8'),
			PDO::ATTR_ERRMODE		=> PDO::ERRMODE_EXCEPTION
		));
	} catch(PDOException $e) {
		$response->error	= true;
		$response->message	= 'Database Error!';
		
		if(defined('DEBUG') && DEBUG) {
			$response->debug = [
				'Code'		=> $e->getCode(),
				'Message'	=> $e->getMessage()
			];
		}
	}
	
	/* Checking API Action */
	if(!$response->error && !isset($_POST['action'])) {
		$response->error	= true;
		$response->message	= 'No action provided!';
	}
	
	/* Handle Actions */
	if(!$response->error) {
		switch($_POST['action']) {
			case 'fetch':
				if(!$response->error && !isset($_POST['query'])) {
					$response->error	= true;
					$response->message	= 'No query provided!';
				}
				
				if(!$response->error && !isset($_POST['param'])) {
					$response->error	= true;
					$response->message	= 'No param provided!';
				}
				
				if(!$response->error) {
					try {
						$statement = $database->prepare($_POST['query']);
						$statement->execute(json_decode($_POST['param'], true));
						$response->data = $statement->fetchAll(PDO::FETCH_OBJ);
					} catch(PDOException $e) {
						if(defined('DEBUG') && DEBUG) {
							$response->debug = [
								'Code'		=> $e->getCode(),
								'Message'	=> $e->getMessage(),
								'Query'		=> $_POST['query']
							];
						}
					}
				}
			break;
			case 'single':
				if(!$response->error && !isset($_POST['query'])) {
					$response->error	= true;
					$response->message	= 'No query provided!';
				}
				
				if(!$response->error && !isset($_POST['param'])) {
					$response->error	= true;
					$response->message	= 'No param provided!';
				}
				
				if(!$response->error) {
					try {
						$statement = $database->prepare($_POST['query']);
						$statement->execute(json_decode($_POST['param'], true));
						$response->data = $statement->fetch(PDO::FETCH_OBJ);
					} catch(PDOException $e) {
						if(defined('DEBUG') && DEBUG) {
							$response->debug = [
								'Code'		=> $e->getCode(),
								'Message'	=> $e->getMessage(),
								'Query'		=> $_POST['query']
							];
						}
					}
				}
			break;
			case 'count':
				if(!$response->error && !isset($_POST['query'])) {
					$response->error	= true;
					$response->message	= 'No query provided!';
				}
				
				if(!$response->error && !isset($_POST['param'])) {
					$response->error	= true;
					$response->message	= 'No param provided!';
				}
				
				if(!$response->error) {
					try {
						$statement = $database->prepare($_POST['query']);
						$statement->execute(json_decode($_POST['param'], true));
						$response->data = $statement->rowCount();
					} catch(PDOException $e) {
						if(defined('DEBUG') && DEBUG) {
							$response->debug = [
								'Code'		=> $e->getCode(),
								'Message'	=> $e->getMessage(),
								'Query'		=> $_POST['query']
							];
						}
					}
				}
			break;
			case 'update':
				if(!$response->error && !isset($_POST['table'])) {
					$response->error	= true;
					$response->message	= 'No table provided!';
				}
				
				if(!$response->error && !isset($_POST['where'])) {
					$response->error	= true;
					$response->message	= 'No where provided!';
				}
				
				if(!$response->error && !isset($_POST['param'])) {
					$response->error	= true;
					$response->message	= 'No param provided!';
				}
				
				if(!$response->error) {
					$fields = [];
					
					// Create bindings
					foreach(json_decode($_POST['param']) AS $name => $value) {
						if($_POST['where'] === $name) {
							continue;
						}
						
						$fields[] = sprintf('`%s`=:%s', $name, $name);
					}
					
					$query = sprintf('UPDATE `%1$s` SET %2$s WHERE `%3$s`=:%3$s', $_POST['table'], implode(', ', $fields), $_POST['where']);
					
					try {
						$statement = $database->prepare($query);
						$statement->execute(json_decode($_POST['param'], true));
						$response->data = $statement->rowCount();
					} catch(PDOException $e) {
						if(defined('DEBUG') && DEBUG) {
							$response->debug = [
								'Code'		=> $e->getCode(),
								'Message'	=> $e->getMessage(),
								'Query'		=> $query
							];
						}
					}
				}
			break;
			case 'remove':
				if(!$response->error && !isset($_POST['table'])) {
					$response->error	= true;
					$response->message	= 'No table provided!';
				}
				
				if(!$response->error && !isset($_POST['param'])) {
					$response->error	= true;
					$response->message	= 'No param provided!';
				}
				
				if(!$response->error) {
					$where = [];
					
					// Create bindings
					foreach(json_decode($_POST['param']) AS $name => $value) {
						$where[] = sprintf('`%s`=:%s', $name, $name);
					}
					
					$query = sprintf('DELETE FROM `%s` WHERE %s', $_POST['table'], implode(', ', $where));
					
					try {
						$statement = $database->prepare($query);
						$statement->execute(json_decode($_POST['param'], true));
						$response->data = $statement->rowCount();
					} catch(PDOException $e) {
						if(defined('DEBUG') && DEBUG) {
							$response->debug = [
								'Code'		=> $e->getCode(),
								'Message'	=> $e->getMessage(),
								'Query'		=> $query
							];
						}
					}
				}
			break;
			case 'insert':
				if(!$response->error && !isset($_POST['table'])) {
					$response->error	= true;
					$response->message	= 'No table provided!';
				}
				
				if(!$response->error && !isset($_POST['param'])) {
					$response->error	= true;
					$response->message	= 'No param provided!';
				}
				
				if(!$response->error) {
					$names		= [];
					$values		= [];
					$param		= json_decode($_POST['param'], true);
					
					foreach($param AS $name => $value) {
						$names[]	= sprintf('`%s`', $name);
						
						if($value === 'NOW()') {
							$values[]	= 'NOW()';
							unset($param[$name]);
						} else {
							$values[]	= sprintf(':%s', $name);
						}
					}
					
					$query = sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $_POST['table'], implode(', ', $names), implode(', ', $values));
					
					try {
						$statement	= $database->prepare($query);
						$statement->execute($param);
						$response->data = intval($database->lastInsertId(), 10);
					} catch(PDOException $e) {
						if(defined('DEBUG') && DEBUG) {
							$response->debug = [
								'Code'		=> $e->getCode(),
								'Message'	=> $e->getMessage(),
								'Query'		=> $query
							];
						}
					}
				}
			break;
			default:
				$response->error	= true;
				$response->message	= 'Unsupported action provided!';
				
				if(defined('DEBUG') && DEBUG) {
					$response->debug = 'Action is: ' . (isset($_POST['action']) ? $_POST['action'] : null);
				}
			break;
		}
	}
	
	print call_user_func_array('json_encode', (defined('PRETTY_OUTPUT') && PRETTY_OUTPUT ? [ $response, JSON_PRETTY_PRINT ] : [ $response ]));
?>
