<?php
error_reporting( 0 );
/**
 * web_server.php
 *
 * WwwwServer
 *
 * @category  Web
 * @package    WwwwServer
 * @author     Kaloyan Hristov
 * @copyright  2021 Kaloyan Hristov
 * @license    https://github.com/X0xx-1110/WWWW-Server/blob/main/LICENSE  MIT License
 * @version    [1.1.4]
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
	protected string $webDirectory = "";
	protected string $phpVersion = "8.0"; //7.0//7.4 //5
	protected string $address = '127.0.0.1'; //Feel free!
	protected string $protocol = 'tcp'; //Could only be!
	protected array $_responceHeaders = [ ];
	protected int $contentLength = 1024;
	private bool $_dynamicallyVars = false;
	private string $_responce = "";
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

	protected static WwwwServer $history;
	protected static array $result = [ ];
	protected static mixed $timestamp;

	public function __destruct(){

	}
	/**
	*	Possible variant for return a Instance of a criteria. Different matter of Design pattern.
	*	Any kind of Prototype, but not. It's a History Design Pattern. 
	* 	@return void
	**/
	public static function getInstance(WwwwServer $o) : void
	{
		if (isset($o) && !empty($o) && is_object($o)) {
			 WwwwServer::$timestamp = [ date( DATE_RFC2822 ) ];
			 WwwwServer::$result[] = [ clone $o ];
		}
	}
	/**
	*	Push the array insde the property of objects.
	*	@return void
	**/
	public static function push(WwwwServer $o) : void
	{
		WwwwServer::getInstance($o);
	}
	/**
	*	Ccmmon function about web server it all.
	*	Basic and common calculation of a source are here. Connections and sockets. Many functions about parsing and functionality.
	*	@return int $port, string $webDirectoryOfUse
	**/
	public function httpServer($port, $webDirectoryOfUse)
	{
		$numRequests = 0;
		$this->setFull_responceHeaders();

		if (isset($webDirectoryOfUse) && !empty($webDirectoryOfUse) && strlen($webDirectoryOfUse) > 1 && is_dir($webDirectoryOfUse)){
			$this->setDir( $webDirectoryOfUse );
		}
		else{
			$this->_isError = true;
			print "[ Web Directory Does not Exists! ]\n";
			return false;
		}

		$this->_socket = stream_socket_server($this->protocol."://".$this->address.":".$port, $errno, $errstr);

		if (!isset($this->_socket) || empty($this->_socket) || !is_resource($this->_socket) || !$this->_socket) {
			echo "$errstr ($errno)<br />\n";
		} else {
				$this->_timestampStart = microtime();
			for(;;) {
				$this->_connection = stream_socket_accept($this->_socket, -1);
				if (isset($this->_connection) && !empty($this->_connection)) {
					$gatheredRequest = stream_get_line($this->_connection, 1000000, "\n");
					$regExCheck = $this->createRegExCheck($gatheredRequest);
					$lineRequest = explode("\n", $gatheredRequest);

					if ( $regExCheck === false ) {
								//@TODO:later functionality
						} else {
							$temporaryUri = $this->parseRequest($gatheredRequest);
							$this->_responce = $this->fileType($temporaryUri);
						}

					if ( $regExCheck === false ) {
							//@TODO:later functionality
						} else {
							if ($this->_isError === true) {
								print "  " . $this->_responce . "\n";
							} else {
								if ($this->_responce === false) {
									$this->_strSecureMsg = "[ FAULT SECURITY check! ]";
								}
								++$numRequests;
								$time = (string) abs(number_format(floatval(substr($this->_timestampEnd, 0, 9))-floatval(substr($this->_timestampStart, 0, 9)), 4, ".", ""));
								print "  |".$numRequests."|".$time."delta|====================>".$lineRequest[0]."  ".$this->_strSecureMsg."\n";
							}

						//History of a challenge!!!!!!!
						WwwwServer::push($this);
						//History of a challenge!!!!!!!
						//var_dump(WwwwServer::$result);
					}

					//set Gzip encoding a make a length of a _responce
					//$this->set_responceToGz($this->_responce);
					$this->setLengthOfResponce($this->_responce);
					
					 //Into Bits
					//Write _responce Headers
					$temporaryHeaders="";
					if (isset($this->_responceHeaders) && !empty($this->_responceHeaders) && is_array($this->_responceHeaders)) {
						foreach($this->_responceHeaders as $firstPart => $secondPart) {
							$temporaryHeaders = $temporaryHeaders . $firstPart." ".$secondPart;
						}
					}
					//Write _responce Content
					fwrite($this->_connection, $temporaryHeaders . $this->_responce);
				
				
					$this->_timestampEnd = microtime();
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
		if(isset($this->_securityFilesWeb) && !empty($this->_securityFilesWeb) && is_array($this->_securityFilesWeb) && count($this->_securityFilesWeb)) {
			foreach($this->_securityFilesWeb as $acceptable) {
				if(isset($acceptable) && !empty($acceptable)) {
					$regEx .= str_replace(".", "\.", $acceptable) . "|"; 
				} 
			}
		}
		if(isset($lines[0]) && !empty($lines[0])) {
			preg_match("/".substr($regEx,0,-1)."/", $lines[0], $matches);
		}
		if(isset($matches) && !empty($matches) && count($matches) > 0 ) {
			return true;
		}
		return false;
	}
	/**
	*	2 Gz about all content of _responce.
	*	@return void
	**/
	private function set_responceToGz($_responce)
	{
		$this->_responce = gzencode($_responce, 9);
	}
	/**
	*	Length of a _responce inside a property.
	*	@return void
	**/
	private function setLengthOfResponce($_responce)
	{
		$this->contentLength = strlen($_responce);
	}
	/**
	* 	Set the headers what they are equal to request about connectin.
	*	@return void
	**/
	private function setFull_responceHeaders()
	{
		$this->set_responceHeaders("HTTP/1.1", "200 OK\r\n");
		$this->set_responceHeaders("Host:", $this->address."\r\n");
		$this->set_responceHeaders("Accept:", "text/html\r\n");
		$this->set_responceHeaders("Keep-Alive:", "30\r\n");
		$this->set_responceHeaders("Date:", date("Y-m-d H:i:s")."\r\n");
		$this->set_responceHeaders("Server:", "WwwwServer 1\r\n");
		$this->set_responceHeaders("Content-Type:", "text/html; charset=utf-8\r\n");
		//$this->set_responceHeaders("Content-Encoding:", "gzip\r\n");
		$this->set_responceHeaders("Content-Language:", "en\r\n");
		$this->set_responceHeaders("Allow:", "GET\r\n");
		//$this->set_responceHeaders("Content-Length:", "1024\r\n");
		$this->set_responceHeaders("Connection:", "close\r\n\r\n");
	}
	/**
	*	Algorithm about setting a web dir.
	*	@return void
	**/
	private function setDir($webDirectory)
	{
		$this->webDirectory = $webDirectory;
	}
	/**
	*	Algorithm about setting a value and a name of a property array.
	*	@return void
	**/
	private function set_responceHeaders($nameRespondHeader, $valueRespondHeader)
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
			$lineRequest = explode("\n", $req);
			$dataRequest = explode(" ", $lineRequest[0]);

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
				foreach($lineRequest as $keyRequest => $lineOfRequest) {
					if ($keyRequest>3) {
						$stringRequest = $stringRequest.$lineOfRequest;
					}
				}
				$temporaryGet = json_decode($stringRequest);
			}
			if (is_file($this->webDirectory.$requestedUrl) && strpos($requestedUrl, "./") === false && strpos($requestedUrl, "../") === false) {
				if (isset($temporaryGet) && !empty($temporaryGet)) {
					return $temporaryUri = array("x_file" => $this->webDirectory.$requestedUrl, "x_data_REQUEST" => $temporaryGet, "x_protocol" => $dataRequest[0]);
				} else {
					return $temporaryUri = array("x_file" => $this->webDirectory.$requestedUrl, "x_data_REQUEST" => "", "x_protocol" => $dataRequest[0]);
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
			if (strpos($temporaryUri["x_file"], "html") !== false) {
				$this->_isError = false;
				return $this->fileRead($temporaryUri["x_file"]);
			}
			if (strpos($temporaryUri["x_file"], "htm") !== false) {
				$this->_isError = false;
				return htmlspecialchars(htmlentities($this->fileRead($temporaryUri["x_file"])));
			}
			if (strpos($temporaryUri["x_file"], "txt") !== false) {
				$this->_isError = false;
				return htmlspecialchars(htmlentities($this->fileRead($temporaryUri["x_file"])));
			}
			if (strpos($temporaryUri["x_file"], "xhtml") !== false) {
				$this->_isError = false;
				return htmlspecialchars(htmlentities($this->fileRead($temporaryUri["x_file"])));
			}
			if (strpos($temporaryUri["x_file"], "xml") !== false) {
				$this->_isError = false;
				return htmlspecialchars(htmlentities($this->fileRead($temporaryUri["x_file"])));
			}
			if (strpos($temporaryUri["x_file"], "php") !== false && $this->phpVersion === "8.0" && $temporaryUri["x_data_REQUEST"] != "") {
				$this->_isError = false;
				$code=$this->dynamicallyWebVariablesOnFly($temporaryUri["x_file"], $temporaryUri["x_protocol"], $temporaryUri["x_data_REQUEST"]);
				return shell_exec("/usr/bin/php8.0 -r ' ".$code." '");
			}
			if (strpos($temporaryUri["x_file"], "php") !== false && $this->phpVersion === "8.0" ) {
				$this->_isError = false; 
				return shell_exec("/usr/bin/php8.0 -f " . $temporaryUri["x_file"]);
			}
			if (strpos($temporaryUri["x_file"], "php") !== false && $this->phpVersion === "7.4") {
				$this->_isError = false;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php7.4 -f " . addslashes($temporaryUri["x_file"])." '{x_GET: ".addslashes($temporaryUri["x_GET"])."}'")));
			}
			if (strpos($temporaryUri["x_file"], "php") !== false && $this->phpVersion==="7.0") {
				$this->_isError = false;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php7.0 -f " . addslashes($temporaryUri["x_file"])." '{x_GET: ".addslashes($temporaryUri["x_GET"])."}'")));
			}
			if (strpos($temporaryUri["x_file"], "php") !== false && $this->phpVersion === "5") {
				$this->_isError = false;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php -f " . addslashes($temporaryUri["x_file"])." '{x_GET: ".addslashes($temporaryUri["x_GET"]). "}'")));
			}
		}
		$this->_isError = true;
		return "400 Bad Request!===========>Could not execute anithing!";
	}
	/**
	*	Parsing web vars like Get and POST to script about.
	*	@return array
	**/
	private function parseWebVars($protocol, $webVars)
	{
		$new_arr = array();
		if (isset($protocol) && !empty($protocol) && $protocol=="GET") {
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
		if (isset($protocol) && !empty($protocol) && $protocol == "POST") {
			//@TODO
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
	private function dynamicallyWebVariablesOnFly($file, $protocol, $webVars)
	{
		$arrayWebVars = array();
		$arrayWebVars = $this->parseWebVars($protocol, $webVars);
		$strPhpCodeOne = "\n\n\n   ";
		$strPhpCodeTwo = $this->fileRead($file);
		if (isset($arrayWebVars) && !empty($arrayWebVars) && is_array($arrayWebVars) && count($arrayWebVars)) {
			foreach($arrayWebVars as $keyVar => $webVar) {
				if ($this -> _dynamicallyVars == true ){
					$strPhpCodeOne = $strPhpCodeOne."$".strtoupper($protocol).strtolower($keyVar)."=\"".$webVar."\"; \n";
				}
			}
		}
		$strPhpCodeTwo = substr($strPhpCodeTwo, 6, -3);
		$strOriginalPhp = $strPhpCodeOne.$strPhpCodeTwo ." ?>";
		return $strOriginalPhp;
	}
}




	$server1 = new WwwwServer();
	//Could set the port if it is free about.

	$server1->httpServer(8283, "/home/xxxxxxxxxxxxxx/Desktop/Documents/");



?>