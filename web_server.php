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
	protected $webDirectory = "";
	protected $phpVersion = "8.0"; //7.0//7.4 //5
	protected $address = '127.0.0.1'; //Feel free!
	protected $protocol = 'tcp'; //Could only be!
	protected $responceHeaders=array();
	private $dynamicallyVars = TRUE;
	private $response = "";
	private $socket;
	private $connection;
	private $timestampStart = 0;
	private $timestampEnd = 0;
	private $isError = FALSE;
	private $excludedFilesTerminal = array(".css",".ico",".js");
	private $excludedFilesWeb = array(".ico");
	private $securityArray = array("'",'"',";","\\","\\\\","\\\\\\","\\\\\\\\","^",")","(","+","*","$","#","!");
	private $securityFilesWeb = array("","index.php","index.html", "index.htm");
	private $strSecureMsg = '';


	/**
	*	Empty these, because there are something security.
	*	@TODO: later functionality!
	**/
	public function __construct()
	{

	}
	public function __destruct()
	{

	}
	/**
	*	Ccmmon function about web server it all.
	*	Basic and common calculation of a source are here. Connections and sockets. Many functions about parsing and functionality.
	**/
	public function  httpServer($Port,$webDirectory)
	{
		$numRequests = 0;
		$this->setresponceHeaders("HTTP/1.1","200 OK\r\n");
		$this->setresponceHeaders("Host:",$this->address."\r\n");
		$this->setresponceHeaders("Accept:","text/html\r\n");
		$this->setresponceHeaders("Keep-Alive:","1\r\n");
		$this->setresponceHeaders("Date:",date("Y-m-d H:i:s")."\r\n");
		$this->setresponceHeaders("Server:","WwwwServer 1");
		$this->setresponceHeaders("Connection:","close\r\n\r\n");
		$this->setDir($webDirectory);
		$this->socket = stream_socket_server($this->protocol."://".$this->address.":".$Port, $errno, $errstr);
		if (!isset($this->socket) || empty($this->socket) || !is_resource($this->socket) || !$this->socket) {
		  	echo "$errstr ($errno)<br />\n";
		} else {
				$this->timestampStart = microtime();
		  	while (true === true) {
		  		$this->connection = stream_socket_accept($this->socket, -1);
		  		if(isset($this->connection) && !empty($this->connection)) {
		  			$gatheredRequest = stream_get_line($this->connection,1000000,"\n");

						$lineRequest = explode("\n",$gatheredRequest);
						$dataRequest = explode(" ", $lineRequest[0]);
						$temporaryExtension = substr($dataRequest[1],strpos($dataRequest[1],"."));
			  		if(in_array($temporaryExtension,$this->excludedFilesTerminal)) {
								#@TODO:later functionality
						} else {
			  			$temporaryUri = $this->parseRequest($gatheredRequest);
			  			$this->response = $this->fileType($temporaryUri);
			  		}

			  		if($this->response === FALSE) {
			  			$this->strSecureMsg = "[ FAULT SECURITY check! ]";
			  		}

			  		if(isset($this->responceHeaders) && !empty($this->responceHeaders) && is_array($this->responceHeaders)) {
			  			foreach($this->responceHeaders as $firstPart => $secondPart) {
			  				fwrite($this->connection, $firstPart." ".$secondPart);
			  			}
			  		}

			  	$lineRequest = explode("\n",$gatheredRequest);
					$dataRequest = explode(" ", $lineRequest[0]);
					$temporaryExtension = substr($dataRequest[1],strpos($dataRequest[1],"."));
					if(in_array($temporaryExtension,$this->excludedFilesTerminal)){
							#@TODO:later functionality
					} else {
						if($this->isError === TRUE) {
							print "  " . $this->response . "\n" ;
						} else {
							++$numRequests;
							$time = (string) abs(number_format(floatval(substr($this->timestampEnd,0,9))-floatval(substr($this->timestampStart,0,9)), 4, ".",""));
							print "  |".$numRequests."|".$time."|====================>".$lineRequest[0]."  ".$this->strSecureMsg."\n";
						}
					}
			    fwrite($this->connection, html_entity_decode(htmlspecialchars_decode($this->response))."\r\n");
			    $this->timestampEnd = microtime();
			    fclose($this->connection);
		    	}
		  	}
		  	fclose($this->socket);
		}
	}
	/**
	*	Algorithm about setting a web dir.
	**/
	private function setDir($webDirectory)
	{
		$this->webDirectory = $webDirectory;
	}
	/**
	*	Algorithm about setting a value and a name of a property array.
	**/
	private function setresponceHeaders($nameRespondHeader,$valueRespondHeader)
	{
		$this->responceHeaders[$nameRespondHeader] = $valueRespondHeader;
	}
	#Basic file read
	/**
	*	My opinion about Read a file and much of possibles upgrades.
	**/
	private function fileRead($file)
	{
		if(isset($file) && !empty($file) && is_file($file) && filesize($file) > 0) {
			$action = fopen($file,'r');
			$read = fread($action,filesize($file));
			fclose($action);
			if(isset($read) && !empty($read) && strlen($read) > 1) {
				return $read;
			}
		}
		return FALSE;
	}
	#
	/**
	*	Function, that parsing the request.
	*
	**/
	private function parseRequest($req)
	{
			$lineRequest = explode("\n",$req);
			$dataRequest = explode(" ", $lineRequest[0]);

			if(isset($dataRequest[0]) && !empty($dataRequest[0]) && $dataRequest[0] == "GET") {
				if(strpos($dataRequest[1],"?") !== FALSE) {
					$data = explode("?", $dataRequest[1]);
					$requestedUrl = substr($data[0],1);
					$temporaryGet = $data[1];
				} else {
					$requestedUrl=substr($dataRequest[1],1);
				}
			}
			if(isset($dataRequest[0]) && !empty($dataRequest[0]) && $dataRequest[0] == "POST") {
				$requestedUrl = substr($dataRequest[1],1);
				foreach($lineRequest as $keyRequest => $lineOfRequest) {
					if($keyRequest>3) {
						$stringRequest = $stringRequest.$lineOfRequest;
					}
				}
				$temporaryGet = json_decode($stringRequest);
			}
			if(is_file($this->webDirectory.$requestedUrl) && strpos($requestedUrl,"./") === FALSE && strpos($requestedUrl,"../") === FALSE) {
				if(isset($temporaryGet) && !empty($temporaryGet) && $this->dynamicallyVars === TRUE) {
					return $temporaryUri = array("x_file" => $this->webDirectory.$requestedUrl, "x_data_REQUEST" => $temporaryGet, "x_protocol" => $dataRequest[0]);
				} else {
					return $temporaryUri = array("x_file" => $this->webDirectory.$requestedUrl, "data_REQUEST" => "", "x_protocol" => $dataRequest[0]);
				}
			}
	}
	/**
	*	Algorithm about security check of a URL.
	*
	**/
	private function securityCheck($urlAboutCheck)
	{
		$len = strlen($urlAboutCheck);
		$arrayCheck = array();
		for($i=0;$i<$len;$i++) {
			$arrayCheck[$i] = substr($urlAboutCheck,$i,1);
		}
		if(isset($arrayCheck) && !empty($arrayCheck) && is_array($arrayCheck) && count($arrayCheck)) {
			foreach($arrayCheck as $isSecureChars) {
				foreach($this->securityArray as $notSecured) {
					if($isSecureChars == $notSecured) {
						return FALSE;
					}
				}
			}
		}
		return TRUE;
	}
	/**
	*	Algorithm about security check of a URL.
	*
	**/
	private function securityCheckWebFiles($request)
	{
		$filename = basename($request);
		if(isset($filename) && !empty($filename) && is_string($filename) && strlen($filename) > 1 && in_array($filename,$this->securityFilesWeb)) {
			return TRUE;
		}
		return FALSE;
	}
	#FIle type of rendered files over the web.
	/**
	*	The render is here about xml, html, txt, PHPs and more
	*	@TODO:Include more renders.
	**/
	private function fileType($temporaryUri)
	{
		if($this->securityCheck($temporaryUri["x_file"]) === FALSE || $this->securityCheckWebFiles($temporaryUri["x_file"]) === FALSE) {
			return FALSE;
		}
		if( isset($temporaryUri["x_file"]) && !empty($temporaryUri["x_file"]) && !is_file($temporaryUri["x_file"])) {
			$this->isError = TRUE;
			return "400 Bad Request!===========>Could not find file!";
		}
		if( isset($temporaryUri["x_file"]) && !empty($temporaryUri["x_file"]) && !is_readable($temporaryUri["x_file"])) {
			$this->isError = TRUE;
			return "400 Bad Request!===========>Could not read from file!";
		}
		if(isset($temporaryUri["x_file"]) && !empty($temporaryUri["x_file"]) && strlen($temporaryUri["x_file"]) > 1) {
			if(strpos($temporaryUri["x_file"],"html") !== FALSE){
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities($this->fileRead($temporaryUri["x_file"])));
			}
			if(strpos($temporaryUri["x_file"],"htm") !== FALSE) {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities($this->fileRead($temporaryUri["x_file"])));
			}
			if(strpos($temporaryUri["x_file"],"txt") !== FALSE) {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities($this->fileRead($temporaryUri["x_file"])));
			}
			if(strpos($temporaryUri["x_file"],"xhtml") !== FALSE) {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities($this->fileRead($temporaryUri["x_file"])));
			}
			if(strpos($temporaryUri["x_file"],"xml") !== FALSE) {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities($this->fileRead($temporaryUri["x_file"])));
			}
			if(strpos($temporaryUri["x_file"],"php") !== FALSE && $this->phpVersion === "8.0" & $this->dynamicallyVars === FALSE) {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php8.0 -f " . addslashes($temporaryUri["x_file"])." '{x_GET: ".addslashes($temporaryUri["x_GET"])."}'")));
			}
			if(strpos($temporaryUri["x_file"],"php") !== FALSE && $this->phpVersion === "8.0" && $this->dynamicallyVars === TRUE) {
				$this->isError = FALSE;
				$code=$this->dynamicallyWebVariablesOnFly($temporaryUri["x_file"],$temporaryUri["x_protocol"],$temporaryUri["x_data_REQUEST"]);
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php8.0 -r ' ".$code." '")));
			}
			if(strpos($temporaryUri["x_file"],"php") !== FALSE && $this->phpVersion === "7.4") {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php7.4 -f " . addslashes($temporaryUri["x_file"])." '{x_GET: ".addslashes($temporaryUri["x_GET"])."}'")));
			}
			if(strpos($temporaryUri["x_file"],"php") !== FALSE && $this->phpVersion==="7.0") {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php7.0 -f " . addslashes($temporaryUri["x_file"])." '{x_GET: ".addslashes($temporaryUri["x_GET"])."}'")));
			}
			if(strpos($temporaryUri["x_file"],"php") !== FALSE && $this->phpVersion === "5") {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php -f " . addslashes($temporaryUri["x_file"])." '{x_GET: ".addslashes($temporaryUri["x_GET"]). "}'")));
			}
		}
		$this->isError = TRUE;
		return "400 Bad Request!===========>Could not execute anithing!";
	}
	/**
	*	Parsing web vars like Get and POST to script about.
	*
	**/
	private function parseWebVars($protocol,$webVars)
	{
		$new_arr = array();
		if(isset($protocol) && !empty($protocol) && $protocol=="GET") {
			if(isset($webVars) && !empty($webVars) && is_string($webVars) && strlen($webVars) > 1) {
				$parsedVars = explode("&",$webVars);
				if(isset($parsedVars) && !empty($parsedVars) && is_array($parsedVars) && count($parsedVars)) {
					foreach($parsedVars as $vars) {
						$temporaryVar = explode("=",$vars);
						$new_arr[$temporaryVar[0]] = $temporaryVar[1];
					}
				}
			}
		}
		elseif(isset($protocol) && !empty($protocol) && $protocol == "POST") {
			#@TODO
		}
		elseif(isset($protocol) && !empty($protocol) && $protocol  == "HEAD") {
			#@TODO
		}
		return $new_arr;
	}
	/**
	*	Dynamically web vars to script and return it for rending!
	*
	**/
	private function dynamicallyWebVariablesOnFly($file,$protocol,$webVars)
	{
		$arrayWebVars = array();
		$arrayWebVars = $this->parseWebVars($protocol,$webVars);
		$strPhpCodeOne = "\n\n\n";
		$strPhpCodeTwo = $this->fileRead($file);
		if(isset($arrayWebVars) && !empty($arrayWebVars) && is_array($arrayWebVars) && count($arrayWebVars)) {
			foreach($arrayWebVars as $keyVar => $webVar) {
				$strPhpCodeOne = $strPhpCodeOne."$".strtoupper($protocol).strtolower($keyVar)."=\"".$webVar."\"; \n";
			}
		}
		$strPhpCodeTwo = substr($strPhpCodeTwo,6,-3);
		$strOriginalPhp = $strPhpCodeOne.$strPhpCodeTwo ." ?>";
		return $strOriginalPhp;
	}
}




	$server = new WwwwServer();
	#Could set the port if it is free about.
	$server->httpServer(8283, "/home/xxxxx/Desktop/Documents/");
?>
