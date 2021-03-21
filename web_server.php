<?php

/*
 * This file is under license
 *
 * (c) Kaloyan Hristov <creativepoetryabout@gmail.com>
 * 
 * @title: WWWW Server - Web Server
 * @author: Kaloyan Hristov
 * Please view the LICENSE file that was with this source code.
 * 
 */

error_reporting( 0 );

class WWWWW_server{
	protected $web_dir="";
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
	private $timestamp_start=0;
	private $timestamp_end=0;
	

	public function __construct(){
	}
	public function __destruct(){
	}
	#Ccmmon function about web server it all.
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
		  			$gathered_request=stream_get_line($this->conn,300);
		  			$this->timestamp_start=microtime();
			  		$temp_URI=$this->parseRequest($gathered_request);
			  		$this->response=$this->fileType($temp_URI);
			  		$str_SECURE='';
			  		if($this->response === FALSE){
			  			$str_SECURE="[ FAULT SECURITY check! ]";
			  		}
			  		if(isset($this->responce_headers) && !empty($this->responce_headers) && is_array($this->responce_headers)){
			  			foreach($this->responce_headers as $first_part=>$second_part){
			  				fwrite($this->conn, $first_part." ".$second_part);
			  			}
			  		}
			  		$line_request=explode("\n",$gathered_request);
					$data_request=explode(" ", $line_request[0]);
					if(isset($line_request[0]) && !empty($line_request[0]) && strpos($line_request[0],".ico")===FALSE && strpos($line_request[0],".css")===FALSE && $this->response=="400 Bad Request!"){
						print "  " . $this->response . "\n" ;
					}
					elseif(isset($line_request[0]) && !empty($line_request[0]) && strpos($line_request[0],".ico")===FALSE && strpos($line_request[0],".css")===FALSE && strpos($line_request[0],$data_request[1])!==FALSE){
						++$num_requests;
						$num=(string) $num_requests;
						$time=(string) abs(floatval(substr($this->timestamp_end,0,9))-floatval(substr($this->timestamp_start,0,9))); // Becouse scientific notation!
						print "  |" . $num_requests . "|" . $time . "s|====================>" . $line_request[0]."  ".$str_SECURE."\n";
					}
			    	fwrite($this->conn, html_entity_decode(htmlspecialchars_decode($this->response)). "\r\n");
			    	$this->timestamp_end=microtime();
			    	fclose($this->conn);
		    	}
		  	}
		  	fclose($this->socket);
		}
	}
	#Set The date
	private function setDate(){
		$this->request_headers["Date:"]=date("Y-m-d H:i:s")."\r\n";
	}
	#Set The date
	private function setSERVERIP(){
		$this->request_headers["Host:"]=$this->address."\r\n";
	}
	private function setDir($WebDir){
		$this->web_dir=$WebDir;
	}
	#Basic file read
	private function FileRead($file){
		if(isset($file) && !empty($file) && is_file($file) && filesize($file) >0 ){
			$action=fopen($file,'r');
			$read=fread($action,filesize($file));
			fclose($action);
			if(isset($read) && !empty($read) && strlen($read) > 1){
				return $read;
			}
		}
		return 0;
	}
	#Function, that parsing the request.
	private function parseRequest($req){
			$line_request=explode("\n",$req);
			$data_request=explode(" ", $line_request[0]);
			if(strpos($data_request[1],"?")!==FALSE){
				$data=explode("?", $data_request[1]);
			}
			else{
				$data=$data_request[1];
			}
			$Xxxx=substr($data,1);
			if(is_file($this->web_dir.$Xxxx) && strpos($Xxxx,"./")===FALSE && strpos($Xxxx,"../")===FALSE){
				if(isset($data[1]) && !empty($data[1])){
					$temp_URI=array("x_file"=>$this->web_dir.$Xxxx, "x_GET"=>$data[1]);
				}
				else{
					$temp_URI=array("x_file"=>$this->web_dir.$Xxxx, "x_GET"=>"");
				}
			}
			if(isset($temp_URI) && !empty($temp_URI) && is_array($temp_URI) && count($temp_URI)){
				return $temp_URI;
			}
	}
	private function securityCheck($urlAboutCheck){

		$arr=array("'",'"',";","\\","\\\\","\\\\\\","\\\\\\\\","^",")","(","+","*","$","#","@","!");
		$len=strlen($urlAboutCheck);
		$array_check=array();
		for($i=0;$i<$len;$i++){
			$array_check[$i]=substr($urlAboutCheck,$i,1);
		}
		if(isset($array_check) && !empty($array_check) && is_array($array_check) && count($array_check)){
			foreach($array_check as $val1){
				foreach($arr as $val2){
					if( $val1 == $val2){
						return FALSE;
					}
				}
			}
		}
		return $var;
	}
	#FIle type of rendered files over the web.
	private function fileType($temp_URI){
		if($this->securityCheck($temp_URI["x_file"])===FALSE){
			return FALSE;
		}



		if(isset($temp_URI["x_file"]) && !empty($temp_URI["x_file"]) && strlen($temp_URI["x_file"])>1 && strpos($temp_URI["x_file"],"html")!==FALSE){
			return htmlspecialchars(htmlentities($this->FileRead($temp_URI["x_file"])));
		}
		elseif(isset($temp_URI["x_file"]) && !empty($temp_URI["x_file"]) && strlen($temp_URI["x_file"])>1 && strpos($temp_URI["x_file"],"xhtml")!==FALSE){
			return htmlspecialchars(htmlentities($this->FileRead($temp_URI["x_file"])));
		}
		elseif(isset($temp_URI["x_file"]) && !empty($temp_URI["x_file"]) && strlen($temp_URI["x_file"])>1 && strpos($temp_URI["x_file"],"xml")!==FALSE){
			return htmlspecialchars(htmlentities($this->FileRead($temp_URI["x_file"])));
		}
		elseif(isset($temp_URI["x_file"]) && !empty($temp_URI["x_file"]) && strlen($temp_URI["x_file"])>1 && strpos($temp_URI["x_file"],"php")!==FALSE && $this->php_version==="8.0"){
			return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php8.0 -f " . addslashes($temp_URI["x_file"]) . " '{x_GET: " . $temp_URI["x_GET"]. "}'")));
		}
		elseif(isset($temp_URI["x_file"]) && !empty($temp_URI["x_file"]) && strlen($temp_URI["x_file"])>1 && strpos($temp_URI["x_file"],"php")!==FALSE && $this->php_version==="7.4"){
			return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php7.4 -f " . addslashes($temp_URI["x_file"]) . " '{x_GET: " . $temp_URI["x_GET"]. "}'")));
		}
		elseif(isset($temp_URI["x_file"]) && !empty($temp_URI["x_file"]) && strlen($temp_URI["x_file"])>1 && strpos($temp_URI["x_file"],"php")!==FALSE && $this->php_version==="7.0"){
			return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php7.0 -f " . addslashes($temp_URI["x_file"]) . " '{x_GET: " . $temp_URI["x_GET"]. "}'")));
		}
		elseif(isset($temp_URI["x_file"]) && !empty($temp_URI["x_file"]) && strlen($temp_URI["x_file"])>1 && strpos($temp_URI["x_file"],"php")!==FALSE && $this->php_version==="5"){
			return htmlspecialchars(htmlentities(shell_exec("/usr/bin/php -f " . addslashes($temp_URI["x_file"]) . " '{x_GET: " . $temp_URI["x_GET"]. "}'")));
		}
		else{
			return "400 Bad Request!";
		}
	}
}




	$htpx_serverR = new WWWWW_server();
	
	#Could set the port if it is free about.
	$htpx_serverR->http_server(8282, "/home/XXXX/Desktop/www1/" );
?>
