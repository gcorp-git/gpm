<?
class APIException extends Exception {
	private $_code;
	private $_message;
	private $_data;

	function __construct( int $code=500, string $message='Unknown', mixed $data=null ) {
		$this->_code = $code;
		$this->_message = $message;
		$this->_data = $data;

		parent::__construct( $message, $code );
	}

	function getData() {
		return $this->_data;
	}

}