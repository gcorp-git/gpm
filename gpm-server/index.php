<?
header( 'Content-Type: text/html; charset=utf-8' );

define( 'HOME', realpath( getenv( 'DOCUMENT_ROOT' ) ) . '/' );
define( 'INPUT', file_get_contents( 'php://input' ) );
define( 'SETTINGS', include( HOME . 'settings.php' ) );

require_once HOME . 'api-exception.php';
require_once HOME . 'api.php';
require_once HOME . 'gpm-server.php';

API::process( $_SERVER['REQUEST_URI'] );