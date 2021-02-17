<?
class API {

	static function process( string $uri ) {
		try {
			$data = self::_process( $uri );
		} catch ( Error | Exception $e ) {
			$class = get_class( $e );

			if ( $class === 'APIException' ) {
				self::error( $e->getCode(), $e->getMessage(), $e->getData() );
			} else {
				self::error( 500, 'Server error' );
			}
		} finally {
			self::response( $data );
		}
	}

	static function response( $data=null ) {
		header( "Content-Type: application/json" );

		$response = [
			'ok' => true,
		];

		if ( !empty( $data ) ) $response['data'] = $data;

		echo json_encode( $response );

		http_response_code( 200 );

		exit;
	}

	static function error( int $code=500, string $message='', $data=null ) {
		header( "Content-Type: application/json" );
		
		if ( empty( $message ) ) $message = 'Unknown error';

		$response = [
			'ok' => false,
			'code' => $code,
			'message' => $message,
		];

		if ( !empty( $data ) ) $response['data'] = $data;

		echo json_encode( $response );

		http_response_code( 200 );

		exit;
	}

	/* private */

	private static function _process( string $uri ) {
		$exploded = explode( '?', $uri );
		$method = trim( $exploded[0], '/' );

		if ( !method_exists( 'GPMServer', $method ) ) {
			throw new APIException( 405, 'Method is not allowed' );
		}

		return call_user_func( [ 'GPMServer', $method ], $_REQUEST );
	}

}