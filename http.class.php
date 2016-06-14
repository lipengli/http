<?php 

interface Https{
	/**
	*	get����
	*/
	function get();
	/**
	*	post����
	*/
	function post();
	/**
	*	����
	*/
	function connect($url);
}

class HttpSocket implements Https
{
	/**
	*	$httpResource return fsockopen open resource ����fsockopen�򿪵���Դ
	*/
	protected $httpResource = null;
	
	/**
	*	$httpReponse return http reponse information ����http��Ӧ��Ϣ
	*/
	protected $httpReponse = null;
	
	/**
	*	$httpUrl return url array analysis ���ط�����url����
	*/
	protected $httpUrl = array();
	
	/**
	*	$httpLine return http request line ����http������
	*/
	protected $httpLine = '';
	
	/**
	*	$httpHead return http request head ����http����ͷ��Ϣ
	*/
	protected $httpHead = '';
	
	/**
	*	$httpBody return http request body ����http����������Ϣ
	*/
	protected $httpBody = array();
	
	/**
	*	$errno return fsockopen error code,defalute -1 ����fsockopen�Ĵ������
	*/
	protected $errno = -1;
	
	/**
	*	$errstr return fsockopen error message ,defalute empty ����fsockopen�Ĵ�����Ϣ
	*/
	protected $errstr = '';
	
	/**
	*	$timeout return fsockopen request timeout,defalute 10 ����fsockopen����ĳ�ʱʱ��
	*/
	protected $timeout = 10;
	
	/**
	*	$httpVersion return HTTP version ����ʹ�õ�http�汾
	*/
	protected $httpVersion = 'HTTP/1.1';
	
	/**
	*	$CRLF �س�����
	*/
	const CRLF = "\r\n";
	
	public function __construct($url){
		$this->connect($url);
		$this->setHttpHead();
	}
	
	/**
	*	set httpLine ����httpline
	*/
	protected function setHttpLine($method){
		$this->httpLine = $method." ".$this->httpUrl['path'].' '.$this->httpVersion.self::CRLF;
	}
	
	/**
	*	set httpHead ����httpHead
	*/
	protected function setHttpHead(){
		$this->httpHead = 'Host:'.$this->httpUrl['host'].self::CRLF;
	}
	
	/**
	*	set httpBody ����httpBody
	*/
	public function setHttpBody($options){
		if(array_key_exists('Content-type',$options)){
			$this->httpBody[] = "Content-type:".$options['Content-type'].self::CRLF;
		}
		if(array_key_exists('Content-length',$options)){
			$this->httpBody[] = "Content-length:".$options['Content-length'].self::CRLF;
		}
	}
		
	/**
	*	http get request  get����ʵ��
	*/
	public function get(){
		$this->setHttpLine("GET");
		$this->request();
		return $this->httpReponse;
	}
	
	/**
	*	http post request post����ʵ��
	*/
	public function post(){
		$this->setHttpLine("POST");
		$this->request();
		return $this->httpReponse;
	}
	
	/**
	*	fsockopen request connect ����ʵ��
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
	*	ʵ�ʵ����󷽷�
	*/
	protected function request(){
		$fsockopenOut = $this->httpLine . $this->httpHead .'Connection: Close'. self::CRLF .implode(self::CRLF,$this->httpBody) . self::CRLF ;
		fwrite($this->httpResource,$fsockopenOut);
		while(!feof($this->httpResource)){
			$this->httpReponse .= fgets($this->httpResource, 1024);
		}
		fclose($this->httpResource);
	}
}
$http = new HttpSocket('http://www.baidu.com/');
echo $http->get();