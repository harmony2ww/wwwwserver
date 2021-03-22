<?php
error_reporting( 0 );
/**
 * web_server.php
 *
 * WWWW-Server
 *
 * @category   Web
 * @package    WWWW-Server
 * @author     Kalata
 * @copyright  2021 Kalata
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
class WWWWW_server{
	protected $webDir="";
	protected $php_version="8.0"; //7.0//7.4 //5
	protected $address='127.0.0.1'; //Feel free!
	protected $protocol='tcp'; //Could only be!
	protected $responce_headers=array("HTTP/1.1"=>"200 OK\r\n",
								   "Host:"=> "127.0.0.1\r\n",
								   "Accept:"=>"text/html\r\n",
								   "Keep-Alive:"=> "1\r\n",
								   "Date:"=>"", 
								   "Connection:"=>"close\r\n\r\n");
	private $response="";
	private $socket;
	private $conn;
	private $timestampStart=0;
	private $timestampEnd=0;
	
	private $IS_ERROR=FALSE;
	private $securityArray=array("'",'"',";","\\","\\\\","\\\\\\","\\\\\\\\","^",")","(","+","*","$","#","@","!");
	private $excludedFilesTerminal=array(".css",".ico",".js");
	private $strSecureMsg='';


	/**
	*	Empty these, because there are something security.
	*	@TODO: later functionality!
	**/
	public function __construct(){
	}
	public function __destruct(){
	}
	/**
	*	Ccmmon function about web server it all.
	*	Basic and common calculation of a source are here. Connections and sockets. Many functions about parsing and functionality at all.
	**/
	public function  http_server($Port,$WebDir){
		$num_requests=0;
		$this->setDate();
		$this->setSERVERIP();
		$this->setDir($WebDir);
		$this->socket = stream_socket_server($this->protocol."://".$this->address.":".$Port, $errno, $errstr);
		if (!isset($this->socket) || empty($this->socket) || !is_resource($this->socket) || !$this->socket){
		  	echo "$errstr ($errno)<br />\n";
		}  
		else {
		  	while (true===true) {
		  		$this->conn = stream_socket_accept($this->socket, -1);
		  		if(isset($this->conn) && !empty($this->conn) ){
		  			$gatheredRequest=stream_get_line($this->conn,300);

		  			$this->timestampStart=microtime();
			  		
			  		$tempURI=$this->parseRequest($gatheredRequest);
			  		$this->response=$this->fileType($tempURI);
			  		

			  		if($this->response === FALSE){
			  			$this->strSecureMsg="[ FAULT SECURITY check! ]";
			  		}

			  		if(isset($this->responce_headers) && !empty($this->responce_headers) && is_array($this->responce_headers)){
			  			foreach($this->responce_headers as $first_part=>$second_part){
			  				fwrite($this->conn, $first_part." ".$second_part);
			  			}
			  		}

			  		$lineRequest=explode("\n",$gatheredRequest);
					$dataRequest=explode(" ", $lineRequest[0]);

					$temporaryExtension=substr($dataRequest[1],strpos($dataRequest[1],"."));

					if(in_array($temporaryExtension,$this->excludedFilesTerminal)){
						#@nothing
					}
					else{
						if($this->IS_ERROR===TRUE){
							print "  " . $this->response . "\n" ;
						}
						else{
							++$numRequests;
							$time=(string) abs(floatval(substr($this->timestampEnd,0,9))-floatval(substr($this->timestampStart,0,9)));
							print "  |" . $numRequests . "|" . $time . "s|====================>" . $lineRequest[0]."  ".$this->strSecureMsg."\n";
						}
					}
			    	fwrite($this->conn, html_entity_decode(htmlspecialchars_decode($this->response)). "\r\n");
			    	$this->timestampEnd=microtime();
			    	fclose($this->conn);
		    	}
		  	}
		  	fclose($this->socket);
		}
	}
	#Set The date
	/**
	*	Algorithum about setting a date.
	**/
	private function setDate(){
		$this->request_headers["Date:"]=date("Y-m-d H:i:s")."\r\n";
	}
	#Set The date
	/**
	*	Algorithum about setting a ip address.
	**/
	private function setSERVERIP(){
		$this->request_headers["Host:"]=$this->address."\r\n";
	}
	/**
	*	Algorithum about setting a web dir.
	**/
	private function setDir($WebDir){
		$this->webDir=$WebDir;
	}
	#Basic file read
	/**
	*	My opinion about Read a file and much of possibles upgrades.
	**/
	private function FileRead($file){
		if(isset($file) && !empty($file) && is_file($file) && filesize($file) >0 ){
			$action=fopen($file,'r');
			$read=fread($action,filesize($file));
			fclose($action);
			if(isset($read) && !empty($read) && strlen($read) > 1){
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
	private function parseRequest($req){
			$lineRequest=explode("\n",$req);
			$dataRequest=explode(" ", $lineRequest[0]);
			if(strpos($dataRequest[1],"?")!==FALSE){
				$data=explode("?", $dataRequest[1]);
			}
			else{
				$data=$dataRequest[1];
			}
			$Xxxx=substr($data,1);
			if(is_file($this->webDir.$Xxxx) && strpos($Xxxx,"./")===FALSE && strpos($Xxxx,"../")===FALSE){
				if(isset($data[1]) && !empty($data[1])){
					return $tempURI=array("x_file"=>$this->webDir.$Xxxx, "x_GET"=>$data[1]);
				}
				else{
					return $tempURI=array("x_file"=>$this->webDir.$Xxxx, "x_GET"=>"");
				}
			}
	}
	/**
	*	Algorithm about security check of a URL.
	*
	**/
	private function securityCheck($urlAboutCheck){
		$len=strlen($urlAboutCheck);
		$arrayCheck=array();
		for($i=0;$i<$len;$i++){
			$array_check[$i]=substr($urlAboutCheck,$i,1);
		}
		if(isset($arrayCheck) && !empty($arrayCheck) && is_array($arrayCheck) && count($arrayCheck)){
			foreach($arrayCheck as $val1){
				foreach($this->securityArray as $val2){
					if( $val1 == $val2){
						return FALSE;
					}
				}
			}
		}
		return $var;
	}
	#FIle type of rendered files over the web.
	/**
	*	The render is here about xml, html, txt, PHPs and more
	*	@TODO:Include more renders.
	**/
	private function fileType($tempURI){
		if($this->securityCheck($tempURI["x_file"])===FALSE){
			return FALSE;
		}
		if( isset($tempURI["x_file"]) && !empty($tempURI["x_file"]) && !is_file($tempURI["x_file"])){
			$this->IS_ERROR=TRUE;
			return "400 Bad Request!===========>Could not find file!";
		}
		if( isset($tempURI["x_file"]) && !empty($tempURI["x_file"]) && !is_readable($tempURI["x_file"])){
			$this->IS_ERROR=TRUE;
			return "400 Bad Request!===========>Could not read from file!";
		}
		if(isset($tempURI["x_file"]) && !empty($tempURI["x_file"]) && strlen($tempURI["x_file"])>1){
			if(strpos($tempURI["x_file"],"html")!==FALSE){
				$this->IS_ERROR=FALSE;
				return htmlspecialchars(htmlentities($this->FileRead($tempURI["x_file"])));
			}
			if(strpos($tempURI["x_file"],"htm")!==FALSE){
				$this->IS_ERROR=FALSE;
				return htmlspecialchars(htmlentities($this->FileRead($tempURI["x_file"])));
			}
			if(strpos($tempURI["x_file"],"txt")!==FALSE){
				$this->IS_ERROR=FALSE;
				return htmlspecialchars(htmlentities($this->FileRead($tempURI["x_file"])));
			}
			if(strpos($tempURI["x_file"],"xhtml")!==FALSE){
				$this->IS_ERROR=FALSE;
				return htmlspecialchars(htmlentities($this->FileRead($tempURI["x_file"])));
			}
			if(strpos($tempURI["x_file"],"xml")!==FALSE){
				$this->IS_ERROR=FALSE;
				return htmlspecialchars(htmlentities($this->FileRead($tempURI["x_file"])));
			}
			if(strpos($tempURI["x_file"],"php")!==FALSE && $this->php_version==="8.0"){
				$this->IS_ERROR=FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php8.0 -f " . addslashes($tempURI["x_file"])." '{x_GET: ".addslashes($tempURI["x_GET"])."}'")));
			}
			if(strpos($tempURI["x_file"],"php")!==FALSE && $this->php_version==="7.4"){
				$this->IS_ERROR=FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php7.4 -f " . addslashes($tempURI["x_file"])." '{x_GET: ".addslashes($tempURI["x_GET"])."}'")));
			}
			if(strpos($tempURI["x_file"],"php")!==FALSE && $this->php_version==="7.0"){
				$this->IS_ERROR=FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php7.0 -f " . addslashes($tempURI["x_file"])." '{x_GET: ".addslashes($tempURI["x_GET"])."}'")));
			}
			if(strpos($tempURI["x_file"],"php")!==FALSE && $this->php_version==="5"){
				$this->IS_ERROR=FALSE;
				return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php -f " . addslashes($tempURI["x_file"])." '{x_GET: ".addslashes($tempURI["x_GET"]). "}'")));
			}
		}
		$this->IS_ERROR=TRUE;
		return "400 Bad Request!===========>Could not execute anithing!";
	}
}




	$htpx_serverR = new WWWWW_server();
	#Could set the port if it is free about.
	$htpx_serverR->http_server(8282, "/home/xxxxx/Desktop/Documents/" );
?>
