<?
if ( empty( $argv[1] ) ) exit;
if ( !file_exists( $argv[1] ) ) exit;

$file_zip = $argv[1];
$content = file_get_contents( $file_zip );

$pi = pathinfo( $file_zip );
$file_info = "{$pi['dirname']}/{$pi['filename']}.php";

$info = [
	'hash' => hash( 'sha3-512', $content ),
	'dependencies' => [],
];

$dump = '<? return ' . var_export( $info, true ) . ';';

file_put_contents( $file_info, $dump );