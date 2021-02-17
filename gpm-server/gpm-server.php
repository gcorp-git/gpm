<?
class GPMServer {

	static function info( array $args ) {
		self::_check_args( $args );
		self::_check_name( $args['package'] );

		$info = self::_get_info( $args['package'] );

		API::response( $info );
	}

	static function download( array $args ) {
		self::_check_args( $args );
		self::_check_name( $args['package'] );

		$file_zip = HOME . "packages/{$args['package']}/{$args['package']}.zip";

		self::_check_file( $file_zip );

		$size = filesize( $file_zip );

		header( "Content-Type: application/zip" );
		header( "Content-disposition: attachment; filename={$args['package']}.zip" );
		header( "Content-Length: {$size}" );

		readfile( $file_zip );
		
		exit;
	}

	/* private */

	private static function _get_info( string $name ) {
		$file_info = HOME . "packages/{$name}/info.php";
		$file_zip = HOME . "packages/{$name}/{$name}.zip";

		self::_check_file( $file_info );
		self::_check_file( $file_zip );
		
		$info = include $file_info;

		if ( !is_array( $info ) || !isset( $info['hash'] ) ) {
			throw new APIException( 500, 'Incorrect package info' );
		}

		if ( empty( $info['hash'] ) ) {
			$content = file_get_contents( $file_zip );
			$info['hash'] = hash( 'sha3-512', $content );
			$dump = '<? return ' . var_export( $info, true ) . ';';

			file_put_contents( $file_info, $dump );
		}

		return $info;
	}

	private static function _check_args( array $args ) {
		if ( empty( $args['package'] ) ) {
			throw new APIException( 400, 'Required arguments missed: package' );
		}
	}

	private static function _check_name( string $name ) {
		if ( empty( $name ) || !preg_match( '/^[A-Za-z0-9_-]+$/', $name ) ) {
			throw new APIException( 400, 'Incorrect package name' );
		}
	}

	private static function _check_file( string $file ) {
		if ( !file_exists( $file ) ) {
			throw new APIException( 404, 'File not found' );
		}
	}

}