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
	protected $webDir = "";
	protected $phpVersion = "8.0"; //7.0//7.4 //5
	protected $address = '127.0.0.1'; //Feel free!
	protected $protocol = 'tcp'; //Could only be!
	protected $responceHeaders=array("HTTP/1.1" => "200 OK\r\n",
		"Host:" => "127.0.0.1\r\n",
		"Accept:" => "text/html\r\n",
		"Keep-Alive:" => "1\r\n",
		"Date:" => "",
		"Connection:" => "close\r\n\r\n");
	private $dynamicallyVars = TRUE;
	private $response = "";
	private $socket;
	private $connection;
	private $timestampStart = 0;
	private $timestampEnd = 0;
	private $isError = FALSE;
	private $securityArray = array("'",'"',";","\\","\\\\","\\\\\\","\\\\\\\\","^",")","(","+","*","$","#","@","!");
	private $excludedFilesTerminal = array(".css",".ico",".js");
	private $excludedFilesWeb = array(".ico");
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
	*	Basic and common calculation of a source are here. Connections and sockets. Many functions about parsing and functionality at all.
	**/
	public function  httpServer($Port,$WebDir)
	{
		$num_requests = 0;
		$this->setDate();
		$this->setSERVERIP();
		$this->setDir($WebDir);
		$this->socket = stream_socket_server($this->protocol."://".$this->address.":".$Port, $errno, $errstr);
		if (!isset($this->socket) || empty($this->socket) || !is_resource($this->socket) || !$this->socket) {
		  	echo "$errstr ($errno)<br />\n";
		} else {
		  	while (true === true) {
		  		$this->connection = stream_socket_accept($this->socket, -1);
		  		if(isset($this->connection) && !empty($this->connection)) {
		  			$gatheredRequest = stream_get_line($this->connection,1000000,"\n");
		  			$this->timestampStart = microtime();

						$lineRequest = explode("\n",$gatheredRequest);
						$dataRequest = explode(" ", $lineRequest[0]);
						$temporaryExtension = substr($dataRequest[1],strpos($dataRequest[1],"."));
			  		if(in_array($temporaryExtension,$this->excludedFilesTerminal)) {
								#@TODO:later functionality
						} else {
			  			$tempURI = $this->parseRequest($gatheredRequest);
			  			$this->response = $this->fileType($tempURI);
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
							$time = (string) abs(floatval(substr($this->timestampEnd,0,9))-floatval(substr($this->timestampStart,0,9)));
							print "  |".$numRequests."|".$time."s|====================>".$lineRequest[0]."  ".$this->strSecureMsg."\n";
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
	#Set The date
	/**
	*	Algorithum about setting a date.
	**/
	private function setDate()
	{
		$this->request_headers["Date:"] = date("Y-m-d H:i:s")."\r\n";
	}
	#Set The date
	/**
	*	Algorithum about setting a ip address.
	**/
	private function setSERVERIP()
	{
		$this->request_headers["Host:"] = $this->address."\r\n";
	}
	/**
	*	Algorithum about setting a web dir.
	**/
	private function setDir($WebDir)
	{
		$this->webDir = $WebDir;
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
			if(is_file($this->webDir.$requestedUrl) && strpos($requestedUrl,"./") === FALSE && strpos($requestedUrl,"../") === FALSE) {
				if(isset($temporaryGet) && !empty($temporaryGet) && $this->dynamicallyVars === TRUE) {
					return $tempURI = array("x_file" => $this->webDir.$requestedUrl, "x_data_REQUEST" => $temporaryGet, "x_protocol" => $dataRequest[0]);
				} else {
					return $tempURI = array("x_file" => $this->webDir.$requestedUrl, "data_REQUEST" => "", "x_protocol" => $dataRequest[0]);
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
			foreach($arrayCheck as $isSecureCars) {
				foreach($this->securityArray as $notSecured) {
					if($isSecureCars == $notSecured) {
						return FALSE;
					}
				}
			}
		}
		return TRUE;
	}
	#FIle type of rendered files over the web.
	/**
	*	The render is here about xml, html, txt, PHPs and more
	*	@TODO:Include more renders.
	**/
	private function fileType($tempURI)
	{
		if($this->securityCheck($tempURI["x_file"]) === FALSE) {
			return FALSE;
		}
		if( isset($tempURI["x_file"]) && !empty($tempURI["x_file"]) && !is_file($tempURI["x_file"])) {
			$this->isError = TRUE;
			return "400 Bad Request!===========>Could not find file!";
		}
		if( isset($tempURI["x_file"]) && !empty($tempURI["x_file"]) && !is_readable($tempURI["x_file"])) {
			$this->isError = TRUE;
			return "400 Bad Request!===========>Could not read from file!";
		}
		if(isset($tempURI["x_file"]) && !empty($tempURI["x_file"]) && strlen($tempURI["x_file"]) > 1) {
			if(strpos($tempURI["x_file"],"html") !== FALSE){
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities($this->fileRead($tempURI["x_file"])));
			}
			if(strpos($tempURI["x_file"],"htm") !== FALSE) {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities($this->fileRead($tempURI["x_file"])));
			}
			if(strpos($tempURI["x_file"],"txt") !== FALSE) {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities($this->fileRead($tempURI["x_file"])));
			}
			if(strpos($tempURI["x_file"],"xhtml") !== FALSE) {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities($this->fileRead($tempURI["x_file"])));
			}
			if(strpos($tempURI["x_file"],"xml") !== FALSE) {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities($this->fileRead($tempURI["x_file"])));
			}
			if(strpos($tempURI["x_file"],"php") !== FALSE && $this->phpVersion === "8.0" & $this->dynamicallyVars === FALSE) {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php8.0 -f " . addslashes($tempURI["x_file"])." '{x_GET: ".addslashes($tempURI["x_GET"])."}'")));
			}
			if(strpos($tempURI["x_file"],"php") !== FALSE && $this->phpVersion === "8.0" && $this->dynamicallyVars === TRUE) {
				$this->isError = FALSE;
				$code=$this->dynamicallyWebVariablesOnFly($tempURI["x_file"],$tempURI["x_protocol"],$tempURI["x_data_REQUEST"]);
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php8.0 -r ' ".$code." '")));
			}
			if(strpos($tempURI["x_file"],"php") !== FALSE && $this->phpVersion === "7.4") {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php7.4 -f " . addslashes($tempURI["x_file"])." '{x_GET: ".addslashes($tempURI["x_GET"])."}'")));
			}
			if(strpos($tempURI["x_file"],"php") !== FALSE && $this->phpVersion==="7.0") {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php7.0 -f " . addslashes($tempURI["x_file"])." '{x_GET: ".addslashes($tempURI["x_GET"])."}'")));
			}
			if(strpos($tempURI["x_file"],"php") !== FALSE && $this->phpVersion === "5") {
				$this->isError = FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php -f " . addslashes($tempURI["x_file"])." '{x_GET: ".addslashes($tempURI["x_GET"]). "}'")));
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
		$strOriginalPhp = "".$strPhpCodeOne.$strPhpCodeTwo ." ?>";
		return $strOriginalPhp;
	}
}




	$server = new WwwwServer();
	#Could set the port if it is free about.
	$server->httpServer(8282, "/home/xxxx/Desktop/Documents/");
?>
