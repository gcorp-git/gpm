<?
class GPMClient {
	private static $_curl = null;

	static function process( array $argv ) {
		if ( empty( $argv[1] ) ) {
			self::error( 'Required arguments missed: method name' );
		}

		if ( empty( $argv[2] ) ) {
			self::error( 'Required arguments missed: package name' );
		}

		switch ( $argv[1] ) {
			case 'install':
				self::install( $argv[2] );
				break;
			case 'remove':
				self::remove( $argv[2] );
				break;
			default:
				self::error( 'Unknown method: ' . $argv[1] );
				break;
		}
	}

	static function install( string $package ) {
		// todo: зависимость между пакетами (dependencies)
		// gpm должен вывести список пакетов,
		// от которых зависит запрошенный,
		// и спросить нужно ли их скачивать
		// 
		// todo: coffee tools
		// coffee-tool-{name-of-tool}
		// скачиваясь, он должен приобретать упрощённое имя
		// этого можно добиться просто запаковыванием папки с другим именем,
		// но тогда скачивание такого пакета может привести
		// к перезаписи файлов другого инструмента с таким же именем
		// ... по всей видимости папки инструментов должны называться иначе,
		// ... нежели файлы инструментов
		// ... и указывать это нужно в settings.php coffee-cms

		$package = trim( $package );

		$info = self::api( 'info', [
			'package' => $package,
		]);

		$dir = HOME . "cache";

		if ( !is_dir( $dir ) ) mkdir( $dir, 0777, true );

		$zip_file = "{$dir}/{$package}.zip";

		$needs_downloading = true;

		if ( file_exists( $zip_file ) ) {
			$zip_content = file_get_contents( $zip_file );
			$hash = hash( 'sha3-512', $zip_content );

			if ( $hash === $info->hash ) {
				$needs_downloading = false;

				// todo: echo "extracting from cache"
			}
		}

		if ( $needs_downloading ) {
			$zip_content = self::api( 'download', [
				'package' => $package,
			]);

			$hash = hash( 'sha3-512', $zip_content );

			if ( $hash !== $info->hash ) {
				self::error( 'Incorrect hash sum' );
			}

			file_put_contents( $zip_file, $zip_content );

			if ( !file_exists( $zip_file ) ) {
				self::error( 'Failed to save package file' );
			}
		}

		$zip = new ZipArchive();

		$is_ok = $zip->open( $zip_file );

		if ( $is_ok !== true ) {
			unlink( $zip_file );

			self::error( 'Failed to extract package file' );
		}

		$cwd = realpath( getcwd() );

		$zip->extractTo( $cwd );
		$zip->close();
	}

	static function remove( string $package ) {
		//
	}

	static function api( string $method, array $args=[] ) {
		$api_url = trim( SETTINGS['api-url'], '/' );
		$url = "{$api_url}/{$method}";
		$curl = self::_get_curl();

		curl_setopt_array( $curl, [
			CURLOPT_URL => $url,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => http_build_query( $args ),
		]);

		$response = curl_exec( $curl );
		$info = curl_getinfo( $curl );

		switch ( $info['content_type'] ) {
			case 'application/json':
				return self::_handle_json_response( $response );
				break;
			case 'application/zip':
				return $response;
				break;
			default:
				self::error( 'Incorrect server response' );
				break;
		}
	}

	static function error( string $message='Unknown error' ) {
		echo "ERROR: {$message}\n";
		exit;
	}

	/* private */

	private static function _get_curl() {
		if ( empty( self::$_curl ) ) {
			self::$_curl = curl_init();
		}

		return self::$_curl;
	}

	private static function _handle_json_response( string $response ) {
		$response = json_decode( $response );

		if ( empty( $response->ok ) ) {
			if ( isset( $response->code ) ) {
				echo "ERROR: {$response->code} {$response->message}\n";

				if ( !empty( $response->data ) ) {
					echo json_encode( $response->data ) . "\n";
				}

				exit;
			} else {
				self::error( 'Incorrect JSON response' );
			}
		}

		return $response->data;
	}

}