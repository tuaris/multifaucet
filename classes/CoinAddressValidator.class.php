	<?php
	//hex input must be in uppercase, with no leading 0x
	 
	class CoinAddressValidator {
	 
		private $addressversion = "00"; //this is a hex byte

		public function __construct($addressversion = "00", $testnet = false)
		{
			$this->addressversion = ($testnet) ? "6F" : $addressversion; //TestNet vs ProductionNet
		}
		public function decodeHex($hex)
		{
			$hex=strtoupper($hex);
			$chars="0123456789ABCDEF";
			$return="0";
			for($i=0;$i<strlen($hex);$i++)
			{
				$current=(string)strpos($chars,$hex[$i]);
				$return=(string)bcmul($return,"16",0);
				$return=(string)bcadd($return,$current,0);
			}
			return $return;
		}
	 
		public function encodeHex($dec)
		{
			$chars="0123456789ABCDEF";
			$return="";
			while (bccomp($dec,0)==1)
			{
				$dv=(string)bcdiv($dec,"16",0);
				$rem=(integer)bcmod($dec,"16");
				$dec=$dv;
				$return=$return.$chars[$rem];
			}
			return strrev($return);
		}
	 
		public function decodeBase58($base58)
		{
			$origbase58=$base58;
		   
			$chars="123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
			$return="0";
			for($i=0;$i<strlen($base58);$i++)
			{
				$current=(string)strpos($chars,$base58[$i]);
				$return=(string)bcmul($return,"58",0);
				$return=(string)bcadd($return,$current,0);
			}
		   
			$return=$this->encodeHex($return);
		   
			//leading zeros
			for($i=0;$i<strlen($origbase58)&&$origbase58[$i]=="1";$i++)
			{
				$return="00".$return;
			}
		   
			if(strlen($return)%2!=0)
			{
				$return="0".$return;
			}
		   
			return $return;
		}
	 
		public function encodeBase58($hex)
		{
			if(strlen($hex)%2!=0)
			{
				die("encodeBase58: uneven number of hex characters");
			}
			$orighex=$hex;
		   
			$chars="123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
			$hex=$this->decodeHex($hex);
			$return="";
			while (bccomp($hex,0)==1)
			{
				$dv=(string)bcdiv($hex,"58",0);
				$rem=(integer)bcmod($hex,"58");
				$hex=$dv;
				$return=$return.$chars[$rem];
			}
			$return=strrev($return);
		   
			//leading zeros
			for($i=0;$i<strlen($orighex)&&substr($orighex,$i,2)=="00";$i+=2)
			{
				$return="1".$return;
			}
		   
			return $return;
		}
	 
		public function hash160ToAddress($hash160)
		{
			$hash160=$this->addressversion.$hash160;
			$check=pack("H*" , $hash160);
			$check=hash("sha256",hash("sha256",$check,true));
			$check=substr($check,0,8);
			$hash160=strtoupper($hash160.$check);
			return $this->encodeBase58($hash160);
		}
	 
		public function addressToHash160($addr)
		{
			$addr=$this->decodeBase58($addr);
			$addr=substr($addr,2,strlen($addr)-10);
			return $addr;
		}
	 
		public function _checkAddress($addr)
		{
			$addr=$this->decodeBase58($addr);
			if(strlen($addr)!=50)
			{
				return false;
			}
			$version=substr($addr,0,2);
			if(hexdec($version)>hexdec($this->addressversion))
			{
				return false;
			}
			$check=substr($addr,0,strlen($addr)-8);
			$check=pack("H*" , $check);
			$check=strtoupper(hash("sha256",hash("sha256",$check,true)));
			$check=substr($check,0,8);
			return $check==substr($addr,strlen($addr)-8);
		}
	 
		public function hash160($data)
		{
			$data=pack("H*" , $data);
			return strtoupper(hash("ripemd160",hash("sha256",$data,true)));
		}
	 
		public function pubKeyToAddress($pubkey)
		{
			return $this->hash160ToAddress($this->hash160($pubkey));
		}
		
		public static function base58_encode($num) {
			$alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
			$base_count = strlen($alphabet);
			$encoded = '';
		 
			while ($num >= $base_count) {
				$div = $num / $base_count;
				$mod = ($num - ($base_count * intval($div)));
				$encoded = $alphabet[$mod] . $encoded;
				$num = intval($div);
			}
		 
			if ($num) {
				$encoded = $alphabet[$num] . $encoded;
			}
		 
			return $encoded;
		}		

		public static function base58_decode($num) {
			$alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
			$len = strlen($num);
			$decoded = 0;
			$multi = 1;
		 
			for ($i = $len - 1; $i >= 0; $i--) {
				$decoded += $multi * strpos($alphabet, $num[$i]);
				$multi = $multi * strlen($alphabet);
			}
		 
			return $decoded;
		}		
	 
		public function remove0x($string)
		{
			if(substr($string,0,2)=="0x"||substr($string,0,2)=="0X")
			{
				$string=substr($string,2);
			}
			return $string;
		}
		public static function checkAddress($addr, $version, $testnet = false)
		{
			$obj = new self($version, $testnet);
			return $obj->_checkAddress($addr);
		}

		public function Check($addr)
		{
			return $this->_checkAddress($addr);
		}		
	}
