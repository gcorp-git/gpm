<?
define( 'HOME', realpath( __DIR__ ) . '/' );
define( 'SETTINGS', include( HOME . 'settings.php' ) );

require_once __DIR__ . '/gpm-client.php';

GPMClient::process( $argv );