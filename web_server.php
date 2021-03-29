<?php
error_reporting( 0 );
/**
 * web_server.php
 *
 * WwwwServer
 *
 * @category   Web
 * @package    WwwwServer
 * @author     Kaloyan Hristov
 * @copyright  2021 Kaloyan Hristov
 * @license    https://github.com/X0xx-1110/WWWW-Server/blob/main/LICENSE  MIT License
 * @version    [1.8.1]
 * @link       https://github.com/X0xx-1110/WWWW-Server
 * @see        https://github.com/X0xx-1110
 * @since      File available since Release 1.1.1
 * @deprecated N/A
 */


/**
*	Main goal of a class is to describe option about the web.
*	All the methods are inside one class, that would create possible these functionality.
**/
class WwwwServer
{
	
	protected string $phpVersion = "8.0";   //7.0//7.4 //5
	protected string $protocol = 'tcp';     //Could only be!
	private string $_webDirectory = "";
	private string $_address = '127.0.0.1'; //Feel free!
	private string $_mime_file = 'mime.json';
	private string $_file_extension = '';
	private string $_content_type = '';
	private array $_responceHeaders = [ ];
	private int $_contentLength = 2048;
	private string $_responce = "";
	private string $_headers_responce = "";
	private mixed $_socket;
	private mixed $_connection;
	private mixed $_timestampStart = 0;
	private mixed $_timestampEnd = 0;
	private bool $_isError = false;
	private array $_excludedFilesTerminal = [ ".css", ".ico", ".js" ];
	private array $_excludedFilesWeb = [ ".ico" ];
	private array $_securityArray = [ "'", '"', ";", "\\", "\\\\", "\\\\\\", "\\\\\\\\", "^", ")", "(", "+", "*", "$", "//", "!" ];
	private array $_securityFilesWeb = [ "", "index.php", "index.html", "index.htm" ];
	private string $_strSecureMsg = '';

	protected static array $result = [ ];
	protected static mixed $timestamp;

	public function __destruct(){

	}
	/**
	*	Possible variant for return a Instance of a criteria. Different matter of Design pattern.
	*	Any kind of Prototype, but not.
	* 	@return void
	**/
	private static function setObjectToArray(WwwwServer $o) : void
	{
		if (isset($o) && !empty($o) && is_object($o)) {
			 WwwwServer::$timestamp = [ date( DATE_RFC2822 ) ];
			 $o -> result = [ ];
			 WwwwServer::$result[ ] = clone $o;
		}
	}
	/**
	*	Push the array insde the property of objects.
	*	@return void
	**/
	private static function push(WwwwServer $o) : void
	{
		WwwwServer::setObjectToArray($o);
	}
	/**
	*	Push the array insde the property of objects.
	*	@return WwwwServer
	**/
	private static function getFirst() : WwwwServer
	{
		return WwwwServer::$result[array_key_first(WwwwServer::$result)];
	}
	/**
	*	Push the array insde the property of objects.
	*	@return WwwwServer
	**/
	private static function getLast() : WwwwServer
	{
		return WwwwServer::$result[array_key_last(WwwwServer::$result)];
	}
	/**
	*	Ccmmon function about web server it all.
	*	Basic and common calculation of a source are here. Connections and sockets. Many functions about parsing and functionality.
	*	@return bool 
	**/
	public function httpServer($port, $webDirectoryOfUse)
	{
		$numRequests = 0;
		if (isset($webDirectoryOfUse) && !empty($webDirectoryOfUse) && strlen($webDirectoryOfUse) > 1 && is_dir($webDirectoryOfUse)) {
			$this->setDir( $webDirectoryOfUse );
		}
		else{
			$this->_isError = true;
			print "[ Web Directory Does not Exists! ]\n";
			return false;
		}

		$this->_socket = stream_socket_server($this->protocol."://".$this -> _address.":".$port, $errno, $errstr);

		if (!isset($this->_socket) || empty($this->_socket) || !is_resource($this->_socket) || !$this->_socket) {
			print "[ Socket Does not Exists! ::".$errstr.", ".$errno."::]\n";
			return false;
		} else {
				$this->_timestampStart = microtime();
			for(;;) {
				$this->_connection = stream_socket_accept($this->_socket, -1);
				if (isset($this->_connection) && !empty($this->_connection)) {
					$gatheredRequest = stream_socket_recvfrom($this->_connection, 1000000, STREAM_PEEK);
					//parse Request
					$requestArray = $this -> preParseRequestToArray($gatheredRequest);
					$request = $this -> preParseRequest($gatheredRequest);
					$regExCheck = $this -> createRegExCheck($request);
					$this -> checkRequestSecurity($requestArray, $regExCheck);
					$this -> setContentTypeString( $requestArray );
					//Set Headers
					$this -> setResponceToGzip();
					$this -> setLengthOfResponce();
					$this -> setFullResponceHeaders();
					//Write _responce Headers
					$this -> setHeadersResponce();
					//Write _responce Content
					fwrite($this->_connection, $this -> _headers_responce . $this -> _responce);
					$this->_timestampEnd = microtime();
					//Write to console
					if ( $regExCheck === false ) {
							//@TODO:later functionality
						} else {
							if ($this->_isError === true) {
								print "  " . $this -> _responce . "\n";
							} else {
								if ($this->_responce === false) {
									$this->_strSecureMsg = "[ FAULT SECURITY check! ]";
								}
								print "  |".(++$numRequests)."|".$this->timeDelta()."delta|====================>".$requestArray[0]."  ".$this->_strSecureMsg."\n";
							}
							//History of a challenge!!!!!!!
							WwwwServer::push($this);
						}

					fclose($this->_connection);
				}
			}
			fclose($this->_socket);
		}
	}
	/**
	*	create regular espression about request and security 
	*	@return string
	**/
	private function createRegExCheck($gathered)
	{
		$regEx = "";
		$lines = explode("\n",$gathered);
		$line = $lines[0];
		if (isset($this->_securityFilesWeb) && !empty($this->_securityFilesWeb) && is_array($this->_securityFilesWeb) && count($this->_securityFilesWeb)) {
			foreach($this->_securityFilesWeb as $acceptable) {
				if (isset($acceptable) && !empty($acceptable)) {
					$regEx .= str_replace(".", "\.", $acceptable) . "|"; 
				} 
			}
		}
		if (isset($line) && !empty($line)) {
			preg_match("/".substr($regEx,0,-1)."/", $line, $matches);
		}
		if (isset($matches) && !empty($matches) && count($matches) > 0 ) {
			return true;
		}
		return false;
	}
	/**
	*	Middle level of security check.
	*	@return void
	**/
	private function checkRequestSecurity($requestArray,$regExStatus)
	{
		if ( $regExStatus === false ) {
			//@TODO:later functionality
			} else {
				$temporaryUri = $this->parseRequest($requestArray);
				$this -> _responce = $this->fileType($temporaryUri);			
			}
	}
	/**
	*	Set headders about responce of a request.
	*	@return void
	**/
	private function setHeadersResponce()
	{
		$temporaryHeaders="";
		if (isset($this->_responceHeaders) && !empty($this->_responceHeaders) && is_array($this->_responceHeaders)) {
			foreach($this->_responceHeaders as $firstPart => $secondPart) {
				$temporaryHeaders = $temporaryHeaders . $firstPart." ".$secondPart;
			}
		}
		if (isset($temporaryHeaders) && !empty($temporaryHeaders) && is_string($temporaryHeaders) && strlen($temporaryHeaders) > 1) {
			$this -> _headers_responce = $temporaryHeaders;
		}
	}
	/**
	*	Delta time for respond of a web server - time for rendering and answer.
	*	@return mixed
	**/
	private function timeDelta()
	{
		if (isset($this->_timestampEnd) && !empty($this->_timestampEnd) && isset($this->_timestampStart) && !empty($this->_timestampStart)) {
			return (string) abs(number_format(floatval(substr($this->_timestampEnd, 0, 9))-floatval(substr($this->_timestampStart, 0, 9)), 4, ".", ""));
		}
	}
	/**
	*	Parse request to array.
	*	@return array
	**/
	private function preParseRequestToArray($request)
	{
		if (isset($request) && !empty($request) && is_string($request) && strlen($request) > 1 ) {
			$gatheredRequestArray = explode("\n", $request);
		}
		if (isset($gatheredRequestArray) && !empty($gatheredRequestArray) && is_array($gatheredRequestArray) && count($gatheredRequestArray) > 1) {
			return $gatheredRequestArray;
		}
	}
	/**
	*	Parse request to string.
	*	@return string
	**/
	private function preParseRequest($request)
	{
		if (isset($request) && !empty($request) && is_string($request) && strlen($request) > 1 ) {
			$gatheredRequestArray = explode("\n", $request);
			$firstLineByRequestFirst = $gatheredRequestArray[array_key_first($gatheredRequestArray)];
		}
		if (isset($firstLineByRequestFirst) && !empty($firstLineByRequestFirst) && is_string($firstLineByRequestFirst) && strlen($firstLineByRequestFirst) > 1) {
			return $firstLineByRequestFirst;
		}
	}
	/**
	*	2 Gz about all content of _responce.
	*	@return void
	**/
	private function setResponceToGzip()
	{
		$this->_responce = gzencode( $this -> _responce, 9);
	}
	/**
	*	Length of a _responce inside a property.
	*	@return void
	**/
	private function setLengthOfResponce()
	{
		$this -> _contentLength = strlen( $this -> _responce );
	}
	/**
	* 	Set the headers what they are equal to request about connectin.
	*	@return void
	**/
	private function setFullResponceHeaders()
	{
		$this->setResponceHeaders("HTTP/1.1", "200 OK\r\n");
		$this->setResponceHeaders("Host:", $this -> _address."\r\n");
		$this->setResponceHeaders("Keep-Alive:", "1\r\n");
		$this->setResponceHeaders("Date:", date( DATE_RFC2822 )."\r\n");
		$this->setResponceHeaders("Server:", "WwwwServer 1\r\n");
		$this->setResponceHeaders("Content-Type:", $this -> _content_type . "; charset=utf-8\r\n");
		$this->setResponceHeaders("Content-Encoding:", "gzip\r\n");
		$this->setResponceHeaders("Content-Language:", "en\r\n");
		$this->setResponceHeaders("Content-Length:", $this -> _contentLength . "\r\n");
		$this->setResponceHeaders("Connection:", "close\r\n\r\n");
	}
	/**
	*	Algorithm about setting a content type into headers of a response.
	*	@return void
	**/
	private function setContentTypeString($arrayOfRequest)
	{
		$firstLineRquest = explode(" ",$arrayOfRequest[0]);
		$extension = substr($firstLineRquest[1], strpos($firstLineRquest[1],"."));
		$object=json_decode($this->fileRead($this->_mime_file));
		if(isset($object) && !empty($object) && is_object($object)) {
			$this -> _content_type = $array -> $extension;
		}
	}
	/**
	*	Algorithm about setting a web dir.
	*	@return void
	**/
	private function setDir($webDirectory)
	{
		$this -> _webDirectory = $webDirectory;
	}
	/**
	*	Algorithm about setting a value and a name of a property array.
	*	@return void
	**/
	private function setResponceHeaders($nameRespondHeader, $valueRespondHeader)
	{
		$this->_responceHeaders[$nameRespondHeader] = $valueRespondHeader;
	}
	//Basic file read
	/**
	*	My opinion about Read a file and much of possibles upgrades.
	*	@return string|bool
	**/
	private function fileRead($file)
	{
		if (isset($file) && !empty($file) && is_file($file) && filesize($file) > 0) {
			$action = fopen($file, 'r');
			$read = fread($action, filesize($file));
			fclose($action);
			if (isset($read) && !empty($read) && strlen($read) > 1) {
				return $read;
			}
		}
		return false;
	}
	//
	/**
	*	Function, that parsing the request.
	*	@return array
	**/
	private function parseRequest($req)
	{
			$dataRequest = explode(" ", $req[0]);

			if (isset($dataRequest[0]) && !empty($dataRequest[0]) && $dataRequest[0] == "GET") {
				if (strpos($dataRequest[1], "?") !== false) {
					$data = explode("?", $dataRequest[1]);
					$requestedUrl = substr($data[0], 1);
					$temporaryGet = $data[1];
				} else {
					$requestedUrl=substr($dataRequest[1], 1);
				}
			}
			if (isset($dataRequest[0]) && !empty($dataRequest[0]) && $dataRequest[0] == "POST") {
				$requestedUrl = substr($dataRequest[1], 1);
				$temporaryGet = $req[array_key_last($req)];
			}
			if (is_file($this -> _webDirectory.$requestedUrl) && strpos($requestedUrl, "./") === false && strpos($requestedUrl, "../") === false) {
				if (isset($temporaryGet) && !empty($temporaryGet)) {
					return $temporaryUri = array("x_file" => $this -> _webDirectory.$requestedUrl, "x_data_REQUEST" => $temporaryGet, "x_protocol" => $dataRequest[0]);
				} else {
					return $temporaryUri = array("x_file" => $this -> _webDirectory.$requestedUrl, "x_data_REQUEST" => "", "x_protocol" => $dataRequest[0]);
				}
			}
	}
	/**
	*	Algorithm about security check of a URL.
	*	@return bool
	**/
	private function securityCheck($urlAboutCheck)
	{
		$len = strlen($urlAboutCheck);
		$arrayCheck = array();
		for($i=0;$i<$len;$i++) {
			$arrayCheck[$i] = substr($urlAboutCheck, $i, 1);
		}
		if (isset($arrayCheck) && !empty($arrayCheck) && is_array($arrayCheck) && count($arrayCheck)) {
			foreach($arrayCheck as $isSecureChars) {
				foreach($this->_securityArray as $notSecured) {
					if ($isSecureChars == $notSecured) {
						return false;
					}
				}
			}
		}
		return true;
	}
	/**
	*	Algorithm about security check of a URL.
	*	@return bool
	**/
	private function securityCheckWebFiles($request)
	{
		$filename = basename($request);
		if (isset($filename) && !empty($filename) && is_string($filename) && strlen($filename) > 1 && in_array($filename, $this->_securityFilesWeb)) {
			return true;
		}
		return false;
	}
	//FIle type of rendered files over the web.
	/**
	*	The render is here about xml, html, txt, PHPs and more
	*	@TODO:Include more renders.
	*	@return string|bool
	**/
	private function fileType($temporaryUri)
	{
		if ($this->securityCheck($temporaryUri["x_file"]) === false || $this->securityCheckWebFiles($temporaryUri["x_file"]) === false) {
			return false;
		}
		if ( isset($temporaryUri["x_file"]) && !empty($temporaryUri["x_file"]) && !is_file($temporaryUri["x_file"])) {
			$this->_isError = true;
			return "400 Bad Request!===========>Could not find file!";
		}
		if ( isset($temporaryUri["x_file"]) && !empty($temporaryUri["x_file"]) && !is_readable($temporaryUri["x_file"])) {
			$this->_isError = true;
			return "400 Bad Request!===========>Could not read from file!";
		}
		if (isset($temporaryUri["x_file"]) && !empty($temporaryUri["x_file"]) && strlen($temporaryUri["x_file"]) > 1) {
			if (strpos($temporaryUri["x_file"], "php") !== false && $this->phpVersion === "8.0" ) {
				$this->_isError = false;
				return shell_exec("/usr/bin/php8.0  -r ' ".$this -> webVariables( $temporaryUri["x_file"], $temporaryUri["x_protocol"], $temporaryUri["x_data_REQUEST"] )." include_once(\"". $this -> webDir.$temporaryUri["x_file"]."\"); ' ");
			}
			elseif (strpos($temporaryUri["x_file"], "php") !== false && $this->phpVersion === "7.4") {
				$this->_isError = false;
				return shell_exec("/usr/bin/php7.4 -r ' ".$this -> webVariables( $temporaryUri["x_file"], $temporaryUri["x_protocol"], $temporaryUri["x_data_REQUEST"] )." include_once(\"". $this -> webDir.$temporaryUri["x_file"]."\"); ' ");
			}
			elseif (strpos($temporaryUri["x_file"], "php") !== false && $this->phpVersion==="7.0") {
				$this->_isError = false;
				return shell_exec("/usr/bin/php7.0 -r ' ".$this -> webVariables( $temporaryUri["x_file"], $temporaryUri["x_protocol"], $temporaryUri["x_data_REQUEST"] )." include_once(\"". $this -> webDir.$temporaryUri["x_file"]."\"); ' ");
			}
			elseif (strpos($temporaryUri["x_file"], "php") !== false && $this->phpVersion === "5") {
				$this->_isError = false;
				return shell_exec("/usr/bin/php -r ' ".$this -> webVariables( $temporaryUri["x_file"], $temporaryUri["x_protocol"], $temporaryUri["x_data_REQUEST"] )." include_once(\"". $this -> webDir.$temporaryUri["x_file"]."\"); ' ");
			} else {
				if( isset($temporaryUri["x_file"]) && !empty($temporaryUri["x_file"]) && strpos($temporaryUri["x_file"],".")!==false && strlen($temporaryUri["x_file"]) > 3 && is_file($this -> webDir.$temporaryUri["x_file"]) && is_readable($this -> webDir.$temporaryUri["x_file"]) ) {
					return $this->readFile($this -> webDir.$temporaryUri["x_file"]);
				}
				return false;
			}
		}
		$this->_isError = true;
		return "400 Bad Request!===========>Could not execute anithing!";
	}
	/**
	*	Parsing web vars like Get and POST to script about.
	*	@return array
	**/
	private function parseWebGetVars($protocol, $webVars)
	{
		$new_arr = array();
		if (isset($protocol) && !empty($protocol) && ( $protocol=="GET" || $protocol=="POST" )) {
			if (isset($webVars) && !empty($webVars) && is_string($webVars) && strlen($webVars) > 1) {
				$parsedVars = explode("&", $webVars);
				if (isset($parsedVars) && !empty($parsedVars) && is_array($parsedVars) && count($parsedVars)) {
					foreach($parsedVars as $vars) {
						$temporaryVar = explode("=", $vars);
						$new_arr[$temporaryVar[0]] = $temporaryVar[1];
					}
				}
			}
		}
		if (isset($protocol) && !empty($protocol) && $protocol == "HEAD") {
			//@TODO
		}
		if (isset($protocol) && !empty($protocol) && $protocol == "PUT") {
			//@TODO
		}
		if (isset($protocol) && !empty($protocol) && $protocol == "DELETE") {
			//@TODO
		}
		if (isset($protocol) && !empty($protocol) && $protocol == "CONNECT") {
			//@TODO
		}
		if (isset($protocol) && !empty($protocol) && $protocol == "OPTIONS") {
			//@TODO
		}
		if (isset($protocol) && !empty($protocol) && $protocol == "TRACE") {
			//@TODO
		}
		if (isset($protocol) && !empty($protocol) && $protocol == "PATCH") {
			//@TODO
		}
		return $new_arr;
	}
	/**
	*	Dynamically web vars to script and return it for rending!
	*	@return string
	**/
	private function webVariables($file, $protocol, $webVars)
	{
		$arrayWebVars = array();
		$arrayWebVars = $this->parseWebGetVars($protocol, $webVars);
		$strPhpCodeOne = " ";
		if (isset($arrayWebVars) && !empty($arrayWebVars) && is_array($arrayWebVars) && count($arrayWebVars)) {
			foreach($arrayWebVars as $keyVar => $webVar) {
				$strPhpCodeOne .= "\$_" . strtoupper($protocol) . "[\"" . $keyVar . "\"]=\"" . $webVar . "\"; \n";
				$strPhpCodeOne .= "\$_REQUEST[\"" . $keyVar . "\"]=\"" . $webVar . "\"; \n";
			}
		}
		return $strPhpCodeOne;
	}
}




	$server1 = new WwwwServer();
	//Could set the port if it is free about.
	$server1->httpServer(8283, "/home/xxxxxxxxxxxx/Desktop/Documents/");



?>