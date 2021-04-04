<?php


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
	private string $phpVersion = "8.0";   //7.0//7.4 //5
	private string $protocol = 'tcp';     //Could only be!
	private string $_webDirectory = "";
	private string $_address = '127.0.0.1'; //Feel free!
	private string $_mimeFile = 'mime.json';
	private string $_directoryLog = '__LOG__/';
	private string $_contentType = '';
	private array $_responceHeaders = [];
	private int $_contentLength = 2048;
	private string $_responce = "";
	private string $_responceNoGzip = "";
	private string $_headersResponce = "";
	private string $_statusResponce = "200";
	private string $_status = "200";
	private mixed $_socket;
	private mixed $_connection;
	private mixed $_timestampStart = 0;
	private mixed $_timestampEnd = 0;
	private bool $_isError = false;
	private array $_excludedFilesTerminal = [".css", ".ico", ".js"];
	private array $_excludedFilesWeb = [".ico"];
	private bool $_securityArrayStatuses = true;
	private array $_securityArray = ["'", '"', ";", "\\", "\\\\", "\\\\\\", "\\\\\\\\", "^", ")", "(", "+", "*", "$", "//", "!"];
	private bool $_securityFilesWebStataStuses = false;
	private array $_securityFilesWeb = ["", "index.php", "index.html", "index.htm"];
	private string $_strSecureMsg = '';
	
	/**
	*	Ccmmon function about web server it all.
	*	Basic and common calculation of a source are here. Connections and sockets. Many functions about parsing and functionality.
	*	@return bool 
	**/
	public function handle(int $port, string $webDirectoryOfUse) : bool
	{
		$numRequests = 0;
		$countRequests = 0;
		if (isset($webDirectoryOfUse) && ! empty($webDirectoryOfUse) && strlen($webDirectoryOfUse) > 1 && is_dir($webDirectoryOfUse) ) {
			$this->setDir($webDirectoryOfUse);
		} else {
			$this->_isError = true;
			$this -> _status = "404 404";
			print "[ Web Directory Does not Exists! ]\n";
			return false;
			}

		$this->_socket = stream_socket_server($this->protocol."://".$this -> _address.":".$port, $errno, $errstr);

		if ( ! isset($this->_socket) || empty($this->_socket) || ! is_resource($this->_socket) || ! $this->_socket ) {
			$this -> _status = "500 500";
			print "[ Socket Does not Exists! ::".$errstr.", ".$errno."::]\n";
			return false;
		} else {
			for(;;) {
				$this -> _connection = stream_socket_accept($this -> _socket, -1);
				if (isset($this -> _connection) && ! empty($this -> _connection)) {
					$gatheredRequest = stream_socket_recvfrom($this->_connection, 1000000, STREAM_PEEK);
					//set start time
					$this -> _timestampStart = microtime();
					//parse Request
					$requestArray = $this -> preParseRequestToArray($gatheredRequest);
					
					$request = $this -> preParseRequest($gatheredRequest);
					$regExCheck = $this -> createRegExCheck($request);
					$this -> checkRequestSecurity($requestArray, $regExCheck);
					$this -> setContentTypeString($requestArray);
					//Set Headers
					$this -> setResponceToGzipDeflate();
					$this -> setLengthOfResponce();
					$this -> setStatusResponce();

					$this -> setFullResponceHeaders();
					//Write _responce Headers
					$this -> setHeadersResponce();

					//set end time!
					$this -> _timestampEnd = microtime();
					//Write _responce Content
					fwrite($this -> _connection, $this -> _headersResponce.$this -> _responce);
					//Write to console
					if ($regExCheck === false) {
							//@TODO:later functionality
						} else {
							if ($this -> _isError === true) {
								//print "  ".$this -> _responce."\n";
							} else {
								if ($this -> _responce === false) {
									$this -> _strSecureMsg = "[ FAULT SECURITY check! ]";
									$this -> log("security.log", $numRequests, $this -> $requestArray[0]."[ FAULT SECURITY check! ]");
								}
								print "  |".(++$numRequests)."|".$this->timeDelta()."delta|====================>".$requestArray[0]."  ".$this->_strSecureMsg."\n";
							}
						}
					++$countRequests;
					$this -> log("request.log", $countRequests, trim($requestArray[0])."----".trim($requestArray[0]));
					$this -> log("user_agent.log", $countRequests, trim($requestArray[0])."----".trim($requestArray[5]));
					$this -> log("reffer.log", $countRequests, trim($requestArray[0])."----".trim($requestArray[11]));
					$this -> log("content_type.log", $countRequests, trim($requestArray[0])."----".trim($this -> _contentType));
					$this -> log("length_responce.log", $countRequests, trim($requestArray[0])."----".trim($this -> _contentLength));
					$this -> log("response.log", $countRequests, trim($requestArray[0])."----".trim($this -> _responceHeaders["HTTP/1.1"]));
					$this -> log("request_data.log", $countRequests, trim($requestArray[0])."----".trim($this -> _responceNoGzip));
					fclose($this -> _connection);
				}
			}
			fclose($this -> _socket);
		}
	}
	/**
	*	create regular espression about request and security at all
	*	@return string
	**/
	private function createRegExCheck(string $gathered) : string
	{ 
		$regEx = "";
		$lines = explode("\n", $gathered);
		$line = $lines[0];
		if ($this -> _securityFilesWeb === true) {
			if (isset($this -> _securityFilesWeb) && ! empty($this -> _securityFilesWeb) && is_array($this -> _securityFilesWeb) && count($this -> _securityFilesWeb)) {
				foreach ($this->_securityFilesWeb as $acceptable) {
					if (isset($acceptable) && ! empty($acceptable)) {
						$regEx .= str_replace(".", "\.", $acceptable)."|"; 
					} 
				}
			}
			if (isset($line) && ! empty($line)) {
				preg_match("/".substr($regEx,0,-1)."/", $line, $matches);
			}
			if (isset($matches) && ! empty($matches) && count($matches) > 0 ) {
				return true;
			}
		} else {
			return true;
		}
	}
	/**
	*	Middle level of security check.
	*	@return void
	**/
	private function checkRequestSecurity(array $requestArray, bool $regExStatus) : void
	{
		if ( $regExStatus === false ) {
			//@TODO:later functionality
			} else {
				$temporaryUri = $this -> parseRequest($requestArray);
				$this -> _responce = $this -> fileType($temporaryUri);	
				if($this -> _responce !== false){
					$this -> _status = "200 OK";
				} else {

				}		
			}
	}
	/**
	*	Set headders about responce of a request.
	*	@return void
	**/
	private function setHeadersResponce() : void
	{
		$temporaryHeaders="";
		if (isset($this -> _responceHeaders) && ! empty($this -> _responceHeaders) && is_array($this -> _responceHeaders)) {
			foreach ($this -> _responceHeaders as $firstPart=>$secondPart) {
				$temporaryHeaders = $temporaryHeaders.$firstPart." ".$secondPart;
			}
		}
		if (isset($temporaryHeaders) && ! empty($temporaryHeaders) && is_string($temporaryHeaders) && strlen($temporaryHeaders) > 1) {
			$this -> _headersResponce = $temporaryHeaders;
		}
	}
	/**
	*	Delta time for respond of a web server - time for rendering and answer.
	*	@return mixed
	**/
	private function timeDelta() : mixed
	{
		if (isset($this -> _timestampEnd) && ! empty($this -> _timestampEnd) && isset($this -> _timestampStart) && ! empty($this -> _timestampStart)) {
			return (string) abs(number_format(floatval(substr($this -> _timestampEnd, 0, 9))-floatval(substr($this -> _timestampStart, 0, 9)), 4, ".", ""));
		}
	}
	/**
	*	Parse request to array.
	*	@return array
	**/
	private function preParseRequestToArray(string $request) : array
	{
		if (isset($request) && ! empty($request) && is_string($request) && strlen($request) > 1) {
			$gatheredRequestArray = explode("\n", $request);
		}
		if (isset($gatheredRequestArray) && ! empty($gatheredRequestArray) && is_array($gatheredRequestArray) && count($gatheredRequestArray) > 1) {
			return $gatheredRequestArray;
		}
	}
	/**
	*	Parse request to string.
	*	@return string
	**/
	private function preParseRequest(string $request) : string
	{
		if (isset($request) && ! empty($request) && is_string($request) && strlen($request) > 1) {
			$gatheredRequestArray = explode("\n", $request);
			$firstLineByRequestFirst = $gatheredRequestArray[array_key_first($gatheredRequestArray)];
		}
		if (isset($firstLineByRequestFirst) && ! empty($firstLineByRequestFirst) && is_string($firstLineByRequestFirst) && strlen($firstLineByRequestFirst) > 1) {
			return $firstLineByRequestFirst;
		}
	}
	/**
	*	2 Gz about all content of _responce.
	*	@return void
	**/
	private function setResponceToGzipDeflate() : void
	{
		$this -> _responce = gzdeflate(gzencode($this -> _responce, 9), 9);
	}
	/**
	*	Length of a _responce inside a property.
	*	@return void
	**/
	private function setLengthOfResponce() : void
	{
		$this -> _contentLength = strlen($this -> _responce);
	}
	/**
	* 	Set the headers what they are equal to request about connectin.
	*	@return void
	**/
	private function setFullResponceHeaders() : void
	{
		$this -> setResponceHeaders("HTTP/1.1", $this -> _statusResponce."\r\n");
		$this -> setResponceHeaders("Host:", $this -> _address."\r\n");
		$this -> setResponceHeaders("Keep-Alive:", "1\r\n");
		$this -> setResponceHeaders("Date:", date(DATE_RFC2822)."\r\n");
		$this -> setResponceHeaders("Server:", "WwwwServer 1\r\n");
		$this -> setResponceHeaders("Content-Type:", $this -> _contentType."; charset=utf-8\r\n");
		$this -> setResponceHeaders("Content-Encoding:", "gzip, deflate\r\n");
		$this -> setResponceHeaders("Content-Language:", "en\r\n");
		$this -> setResponceHeaders("Content-Length:", $this -> _contentLength."\r\n");
		$this -> setResponceHeaders("Connection:", "close\r\n\r\n");
	}
	private function setStatusResponce() : void
	{
		$this -> _statusResponce = $this -> _status;
	}
	/**
	*	Algorithm about setting a content type into headers of a response.
	*	@return void
	**/
	private function setContentTypeString(array $arrayOfRequest) : void
	{	
		$arrayOfRequestFirstLine = $arrayOfRequest[0];
		$firstLineRquest = explode(" ", $arrayOfRequestFirstLine);
		$firstLineRquestSecondParam = $firstLineRquest[1];
		if( strpos($firstLineRquestSecondParam, "?") !== false) {
			$deltaLen = strpos($firstLineRquestSecondParam, "?") - strpos($firstLineRquestSecondParam, ".");
			$extension = substr($firstLineRquestSecondParam, strpos($firstLineRquestSecondParam, "."), $deltaLen);
		} else {
					$extension = substr($firstLineRquestSecondParam, strpos($firstLineRquestSecondParam, "."));
				}
		$object = json_decode($this -> fileRead($this -> _mimeFile));
		if (isset($object) && ! empty($object) && is_object($object)) {
			$this -> _contentType = $object -> $extension;
		}
	}
	/**
	*	Algorithm about setting a web dir.
	*	@return void
	**/
	private function setDir(string $webDirectory) : void
	{
		$this -> _webDirectory = $webDirectory;
	}
	/**
	*	Algorithm about setting a value and a name of a property array.
	*	@return void
	**/
	private function setResponceHeaders(string $nameRespondHeader, string $valueRespondHeader) : void
	{
		$this -> _responceHeaders[$nameRespondHeader] = $valueRespondHeader;
	}
	//Basic file read
	/**
	*	My opinion about Read a file and much of possibles upgrades.
	*	@return string|bool
	**/
	private function fileRead(string $file) : string|bool
	{
		if (isset($file) && ! empty($file) && is_file($file) && filesize($file) > 0) {
			$action = fopen($file, 'r');
			$read = fread($action, filesize($file));
			fclose($action);
			if (isset($read) && ! empty($read) && strlen($read) > 1) {
				return $read;
			}
		}
		return false;
	}
	//Basic file write
	/**
	*	My opinion about write a file and much of possibles upgrades.
	*	@return bool
	**/
	private function fileWrite(string $filename, int $id, string $message) : bool
	{
		if(isset($filename) && ! empty($filename) && is_string($filename) && strlen($filename) > 4 && isset($message) && ! empty($message) && is_string($message) && strlen($message) > 1) {
			touch($filename);
			$handling = fopen($filename, "a+");
			fwrite($handling,  "[".$id."]-[".date(DATE_RFC2822)."]-".$message."\n");
			fclose($handling);
			return true;
		}
		return false;
	}
	//
	/**
	*	Function, that parsing the request.
	*	@return array
	**/
	private function parseRequest(array $req) : array
	{
			$firstLineRquest = $req[0];
			$dataRequest = explode(" ", $firstLineRquest);
			$dataRequestFirstRecord = $dataRequest[0];
			$dataRequestSecondRecord = $dataRequest[1];
			if (isset($dataRequestFirstRecord ) && ! empty($dataRequestFirstRecord) && $dataRequestFirstRecord  == "GET") {
				if (strpos($dataRequestSecondRecord, "?") !== false) {
					$data = explode("?", $dataRequestSecondRecord);
					$dataUriFirst = $data[0];
					$dataUriSecond = $data[1];
					$requestedUrl = substr($dataUriFirst, 1);
					$temporaryGet = $dataUriSecond;
				} else {
					$requestedUrl = substr($dataRequestSecondRecord, 1);
				}
			}
			if (isset($dataRequestFirstRecord) && ! empty($dataRequestFirstRecord) && $dataRequestFirstRecord == "POST") {
				$requestedUrl = substr($dataRequestSecondRecord, 1);
				$temporaryGet = $req[array_key_last($req)];
			}
			if (strpos($requestedUrl, "./") === false && strpos($requestedUrl, "../") === false) {
				if (isset($temporaryGet) && ! empty($temporaryGet)) {
					return array("x_file"=>$requestedUrl, "x_data_REQUEST"=>$temporaryGet, "x_protocol"=>$dataRequestFirstRecord);
				} else {
					return array("x_file"=>$requestedUrl, "x_data_REQUEST"=>"", "x_protocol"=>$dataRequestFirstRecord);
				}
			}
	}
	/**
	*	Algorithm about security check of a URL.
	*	@return bool
	**/
	private function securityCheck(string $urlAboutCheck) : bool
	{
		$len = strlen($urlAboutCheck);
		$arrayCheck = array();
		for ($i=0;$i<$len;$i++) {
			$arrayCheck[$i] = substr($urlAboutCheck, $i, 1);
		}
		if (isset($arrayCheck) && ! empty($arrayCheck) && is_array($arrayCheck) && count($arrayCheck)) {
			foreach ($arrayCheck as $isSecureChars) {
				foreach ($this -> _securityArray as $notSecured) {
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
	private function securityCheckWebFiles(string $request) : bool
	{
		$filename = basename($request);
		if (isset($filename) && ! empty($filename) && is_string($filename) && strlen($filename) > 1 && $this -> _securityFilesWebStatues === true && in_array($filename, $this -> _securityFilesWeb)) {
			return true;
		}
		if ( $this -> _securityFilesWebStatues === true ) {
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
	private function fileType(array $temporaryUri) : string|bool
	{
		if(isset($temporaryUri["x_file"]) && ! empty($temporaryUri["x_file"]) && ! is_null($temporaryUri["x_file"])) {
			$xFile = $this -> _webDirectory.$temporaryUri["x_file"];
		}
		else{
			$this -> _isError = true;
			$this -> _status = "404 NOT FOUND";
			return "400 Bad Request!===========>Could not find file!";
			return false;
		}
		if ($this -> _securityArrayStatuses === true && isset($xFile) && !empty($xFile) && $this->securityCheck($xFile) === false) {
			$this -> _status = "406 406";
			return false;
		}
		if ($this -> _securityFilesWebStataStuses === true && isset($xFile) && !empty($xFile) && $this->securityCheckWebFiles($xFile) === false) {
			$this -> _status = "406 406";
			return false;
		}
		if (isset($xFile) && !empty($xFile) && !is_file($xFile)) {
			$this -> _isError = true;
			$this -> _status = "400 400";
			return "400 Bad Request!===========>Could not find file!";
		}
		if (isset($xFile) && ! empty($xFile) && ! is_readable($xFile)) {
			$this -> _isError = true;
			$this -> _status = "400 400";
			return "400 Bad Request!===========>Could not read from file!";
		}
		if(isset($temporaryUri["x_protocol"]) && ! empty($temporaryUri["x_protocol"]) && $temporaryUri["x_protocol"] != "HEAD" && $temporaryUri["x_protocol"] != "PING") {
			
			if (isset($xFile) && ! empty($xFile) && strlen($xFile) > 1) {
				if (strpos($xFile, "php") !== false && $this -> phpVersion === "8.0") {
					$this -> _isError = false;
					$this -> _status = "200 OK";
					return shell_exec("/usr/bin/php8.0  -r ' ".$this -> webVariables($xFile, $temporaryUri["x_protocol"], $temporaryUri["x_data_REQUEST"] )." include_once(\"".$xFile."\"); ' ");
				}
				elseif (strpos($xFile, "php") !== false && $this -> phpVersion === "7.4") {
					$this -> _isError = false;
					$this -> _status = "200 OK";
					return shell_exec("/usr/bin/php7.4 -r ' ".$this -> webVariables($xFile, $temporaryUri["x_protocol"], $temporaryUri["x_data_REQUEST"])." include_once(\"".$xFile."\"); ' ");
				}
				elseif (strpos($xFile, "php") !== false && $this -> phpVersion==="7.0") {
					$this -> _isError = false;
					$this -> _status = "200 OK";
					return shell_exec("/usr/bin/php7.0 -r ' ".$this -> webVariables($xFile, $temporaryUri["x_protocol"], $temporaryUri["x_data_REQUEST"])." include_once(\"".$xFile."\"); ' ");
				}
				elseif (strpos($xFile, "php") !== false && $this -> phpVersion === "5") {
					$this -> _isError = false;
					$this -> _status = "200 OK";
					return shell_exec("/usr/bin/php -r ' ".$this -> webVariables($xFile, $temporaryUri["x_protocol"], $temporaryUri["x_data_REQUEST"])." include_once(\"".$xFile."\"); ' ");
				} else {
					if( isset($xFile) && !empty($xFile) && strpos($xFile, ".")!==false && strlen($xFile) > 3 && is_file($xFile) && is_readable($xFile) ) {
						$this -> _status = "200 OK";
						return $this -> fileRead($xFile);
					}
					return false;
				}
			}
		}
		if(isset($temporaryUri["x_protocol"]) && ! empty($temporaryUri["x_protocol"]) && $temporaryUri["x_protocol"] == "HEAD") {
			//@TODO: later functionality of starting a function that calculate the HEAD protocul starting functions.
			$this -> _status = "200 OK";
			return "";
		}
		if(isset($temporaryUri["x_protocol"]) && ! empty($temporaryUri["x_protocol"]) && $temporaryUri["x_protocol"] == "PING") {
			$this -> _status = "200 OK";
			return "";
		}
		$this -> _isError = true;
		$this -> _status = "400";
		return "400 Bad Request!===========>Could not execute anithing!";
	}
	/**
	*	Parsing web vars like Get and POST to script about.
	*	@return array
	**/
	private function parseWebGetVars(string $protocol, string $webVars) : array
	{
		$newArr = array();
		$newArrNotReturned = array();
		if (isset($protocol) && ! empty($protocol) && ($protocol == "GET" || $protocol == "POST" || $protocol == "HEAD")) {
			if (isset($webVars) && ! empty($webVars) && is_string($webVars) && strlen($webVars) > 1) {
				$parsedVars = explode("&", $webVars);
				if (isset($parsedVars) && ! empty($parsedVars) && is_array($parsedVars) && count($parsedVars)) {
					foreach ($parsedVars as $vars) {
						$temporaryVar = explode("=", $vars);
						$temporaryVarFirst = $temporaryVar[0];
						$temporaryVarSecond = $temporaryVar[1];
						$newArr[$temporaryVarFirst] = $temporaryVarSecond;
					}
					$this -> _responceNoGzip = json_encode($newArr);
				}
			}
		}
		if (isset($protocol) && ! empty($protocol) && $protocol == "PUT") {
			//@TODO
		}
		if (isset($protocol) && ! empty($protocol) && $protocol == "DELETE") {
			//@TODO
		}
		if (isset($protocol) && ! empty($protocol) && $protocol == "CONNECT") {
			//@TODO
		}
		if (isset($protocol) && ! empty($protocol) && $protocol == "OPTIONS") {
			//@TODO
		}
		if (isset($protocol) && ! empty($protocol) && $protocol == "TRACE") {
			//@TODO
		}
		if (isset($protocol) && ! empty($protocol) && $protocol == "PATCH") {
			//@TODO
		}
		if (isset($protocol) && ! empty($protocol) && $protocol == "PING") {
			//ready!
		}
		if (isset($protocol) && ! empty($protocol) && $protocol == "PINGSERVICE") {
			//@TODO
		}
		if (isset($protocol) && ! empty($protocol) && $protocol == "LOOKUPSERVICE") {

		}
		if (isset($protocol) && ! empty($protocol) && $protocol == "ROUTESERVICE") {

		}
		return $newArr;
	}
	/**
	*	Dynamically web vars to script and return it for rending!
	*	@return string
	**/
	private function webVariables(string $file, string $protocol, string $webVars) : string
	{
		$arrayWebVars = array();
		$arrayWebVars = $this -> parseWebGetVars($protocol, $webVars);
		$strPhpCodeOne = " ";
		if (isset($arrayWebVars) && ! empty($arrayWebVars) && is_array($arrayWebVars) && count($arrayWebVars)) {
			foreach ($arrayWebVars as $keyVar => $webVar) {
				$strPhpCodeOne .= "\$_".strtoupper($protocol)."[\"".$keyVar."\"]=\"".$webVar."\"; \n";
				$strPhpCodeOne .= "\$_REQUEST[\"".$keyVar."\"]=\"".$webVar."\"; \n";
			}
		}
		return $strPhpCodeOne;
	}
	/**
	*	Dynamically log oposite data into file and create directory about logs!
	*	@return bool
	**/
	private function log(string $file, int $id, string $message) : bool
	{
		if(! is_dir($this -> _directoryLog)) {
			mkdir($this -> _directoryLog, 0777);
			chmod($this -> _directoryLog, 0777);
		}
		if($this->fileWrite(getcwd()."/".$this -> _directoryLog.$file, $id, $message)) {
			return true;
		}
		return false;
	}
}


?>