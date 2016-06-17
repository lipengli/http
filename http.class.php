<?php 
set_time_limit(0);
interface Https{
	/**
	*	get方法
	*/
	function get();
	/**
	*	post方法
	*/
	function post();
	/**
	*	连接
	*/
	function connect($url);
}

class HttpSocket implements Https
{
	/**
	*	$httpResource return fsockopen open resource 返回fsockopen打开的资源
	*/
	protected $httpResource = null;
	
	/**
	*	$httpReponse return http reponse information 返回http响应信息
	*/
	protected $httpReponse = null;
	
	/**
	*	$httpUrl return url array analysis 返回分析的url数组
	*/
	protected $httpUrl = array();
	
	/**
	*	$httpLine return http request line 返回http请求行
	*/
	protected $httpLine = '';
	
	/**
	*	$httpHead return http request head 返回http请求头信息
	*/
	protected $httpHead = array();
	
	/**
	*	$httpBody return http request body 返回http请求主体信息
	*/
	protected $httpBody = '';
	
	/**
	*	$errno return fsockopen error code,defalute -1 返回fsockopen的错误代码
	*/
	protected $errno = -1;
	
	/**
	*	$errstr return fsockopen error message ,defalute empty 返回fsockopen的错误信息
	*/
	protected $errstr = '';
	
	/**
	*	$timeout return fsockopen request timeout,defalute 10 返回fsockopen请求的超时时间
	*/
	protected $timeout = 10;
	
	/**
	*	$httpVersion return HTTP version 返回使用的http版本
	*/
	protected $httpVersion = 'HTTP/1.1';
	
	/**
	*	$CRLF 回车换行
	*/
	const CRLF = "\r\n";
	
	public function __construct($url){
		$this->connect($url);
	}
	
	/**
	*	set httpLine 设置httpline
	*/
	public function setHttpLine($method){
		$this->httpLine = $method." ".$this->httpUrl['path'].' '.$this->httpVersion.self::CRLF;
	}
	
	/**
	*	set httpBody 设置httpHead
	*/
	public function setHttpBody($str){
		$this->httpBody = http_build_query($str);
	}
	
	/**
	*	set httpHead 设置httpBody
	*/
	public function setHttpHead($options = []){
		$this->httpHead[] = 'Host:'.$this->httpUrl['host'].self::CRLF;
		if(array_key_exists('Content-length',$options)){
			$this->httpHead[] = "Content-length:".strlen($this->httpBody).self::CRLF;
		}
		if(array_key_exists('Content-type',$options)){
			$this->httpHead[] = "Content-type:".$options['Content-type'].self::CRLF;
		}
		
		if(array_key_exists('Cookie',$options)){
			$this->httpHead[] = "Cookie:".$options['Cookie'].self::CRLF;
		}
		$this->httpHead[] = "Connection: Close";
	}
		
	/**
	*	http get request  get方法实现
	*/
	public function get(){
		$this->setHttpLine("GET");
		$this->request();
		return $this->httpReponse;
	}
	
	/**
	*	http post request post方法实现
	*/
	public function post(){
		$this->setHttpLine("POST");
		$this->request();
		return $this->httpReponse;
	}
	
	/**
	*	fsockopen request connect 连接实现
	*/
	public function connect($url){
		$this->httpUrl = parse_url($url);
		if(!isset($this->httpUrl['port'])){
			$this->httpUrl['port'] = 80;
		}
		if(!isset($this->httpUrl['path'])){
			$this->httpUrl['path'] = '/';
		}
		$this->httpResource = fsockopen($this->httpUrl['host'],$this->httpUrl['port'],$this->errno,$this->errstr,$this->timeout);
	}
	
	/**
	*	实际的请求方法
	*/
	protected function request(){
		$fsockopenOut = $this->httpLine . implode('',$this->httpHead). self::CRLF.self::CRLF  . $this->httpBody . self::CRLF ;
		//echo $fsockopenOut;
		//die;
		fwrite($this->httpResource,$fsockopenOut);
		while(!feof($this->httpResource)){
			$this->httpReponse .= fgets($this->httpResource, 1024);
		}
		fclose($this->httpResource);
	}
}