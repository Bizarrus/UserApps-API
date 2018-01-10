<?php
	#########################################
	#		Database		#
	#########################################
	define('DATABASE_HOSTNAME',		'localhost');
	define('DATABASE_PORT',			3306);
	define('DATABASE_NAME',			'');
	define('DATABASE_USERNAME',		'');
	define('DATABASE_PASSWORD',		'');
	
	#########################################
	#		Improvements		#
	#########################################
	/*
	 * IF you're using an another charset encoding, you can
	 * set it here... For sample "UTF-8", "ISO-8859-2" or other..
	*/
	define('CHARSET_OUTPUT', 'UTF-8');
	define('CHARSET_DATABASE', 'utf8');
	
	/*
	 * Enable debug mode for problems
	*/
	define('DEBUG', true);
	
	/*
	 * Print pretty format
	 * NOTE:	This option is only for debugging a nice-to-have!
	 *		If you're using the API on productive systems,
	 *		disable it to save lot's of memory...
	*/
	define('PRETTY_OUTPUT', true);
	
	#########################################
	#		Security		#
	#########################################
	/*
	 * Check if the API-Request is only via https://
	 * @default	true
	 * @possible true, false
	*/
	define('HTTPS_ONLY', true);
	
	/*
	 * Check if the Request contains a valid UserAgent
	 * @default	true
	 * @possible true, false
	*/
	define('USERAGENT_APPSERVER', true);
	
	/*
	 * Check if the Request contains a valid AppServer version.
	 * NOTE: This option has no affects if USERAGENT_APPSERVER is disabled!
	 * @default	true
	 * @possible true, false
	*/
	define('APPSERVER_VERSION', true);
	
	/*
	 * Check if the Request contains a valid ChatServer version.
	 * NOTE: This option has no affects if USERAGENT_APPSERVER is disabled!
	 * @default	true
	 * @possible true, false
	*/
	define('CHATSERVER_VERSION', true);
	
	/*
	 * Check if the Request comes from allowed ChatServers
	 * @default	true
	 * @possible
	 *		false	= disable
	 *		*		= All
	 *		DE		= Knuddels.de
	 *		CH		= Knuddels.ch
	 *		AT		= Knuddels.at
	 *		COM		= Knuddels.com
	 *		DEV		= Developer.Knuddels.de
	 *		TEST	= TestServer.Knuddels.de
	*/
	define('ALLOWED_CHATSERVERS', 'DE,DEV,TEST');
	
	/*
	 * Check allowed developers.
	 * NOTE: All possible/whitelisted user-id's must be defined in your knuddelsAccess.txt
	 * @default	true
	 * @possible true, false
	*/
	define('ALLOWED_DEVELOPERS', true);
	
	/*
	 * Check allowed channels.
	 * NOTE:	All possible/whitelisted channels's must be defined in your knuddelsAccess.txt.
	 * 		For each Line, you must define "# Channel: <CHANNEL>" or
	 *		you can whitelist all Channels with "# Channel: *"
	 * @default	true
	 * @possible true, false
	*/
	#define('ALLOWED_CHANNELS', true);
	
	/*
	 * Check allowed Apps.
	 * NOTE:	All possible/whitelisted UserApps must be defined in your knuddelsAccess.txt.
	 *		The name of your app is the name of the FTP-Directory!
	 * 		For each Line, you must define "# App: <NAME>" or
	 *		you can whitelist all Apps with "# App: *"
	 * @default	true
	 * @possible true, false
	*/
	#define('ALLOWED_APPS', true);
?>
