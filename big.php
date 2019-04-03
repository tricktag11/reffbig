<?php  

class curl {
	public $ch;
	function curl() {
		$this->ch = curl_init();
		curl_setopt ($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/530.1 (KHTML, like Gecko) Chrome/2.0.164.0 Safari/530.1');
		curl_setopt ($this->ch, CURLOPT_HEADER, 1);
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($this->ch, CURLOPT_FOLLOWLOCATION,true);
		curl_setopt ($this->ch, CURLOPT_TIMEOUT, 30);
		curl_setopt ($this->ch, CURLOPT_CONNECTTIMEOUT,30);
	}
	function header($header) {
		curl_setopt ($this->ch, CURLOPT_HTTPHEADER, $header);
	}
	function ssl($veryfyPeer, $verifyHost){
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $veryfyPeer);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $verifyHost);
	}
	function post($url, $data) {
		curl_setopt($this->ch, CURLOPT_POST, 1);	
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
		return $this->getpage($url);
	}
	function data($url, $data, $hasHeader=true, $hasBody=true) {
		curl_setopt ($this->ch, CURLOPT_POST, 1);
		curl_setopt ($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return $this->getpage($url, $hasHeader, $hasBody);
	}
	function get($url, $hasHeader=true, $hasBody=true) {
		curl_setopt ($this->ch, CURLOPT_POST, 0);
		return $this->getpage($url, $hasHeader, $hasBody);
	}	
	function getpage($url, $hasHeader=false, $hasBody=true) {
		curl_setopt($this->ch, CURLOPT_HEADER, $hasHeader ? 1 : 0);
		curl_setopt($this->ch, CURLOPT_NOBODY, $hasBody ? 0 : 1);
		curl_setopt ($this->ch, CURLOPT_URL, $url);
		$data = curl_exec ($this->ch);
		$this->error = curl_error ($this->ch);
		$this->info = curl_getinfo ($this->ch);
		return $data;
	}
}

function fetch_value($str,$find_start,$find_end) {
	$start = @strpos($str,$find_start);
	if ($start === false) {
		return "";
	}
	$length = strlen($find_start);
	$end    = strpos(substr($str,$start +$length),$find_end);
	return trim(substr($str,$start +$length,$end));
}

function string($length) {
	$characters = 'abcdefghijklmnopqrstuvwxyz';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function ref($refcode) {
	$curl = new curl();
	$curl->ssl(0, 2);

	$domain = "1secmail.net|1secmail.com|1secmail.org";
	$domain = explode("|", $domain);
	$maildo = $domain[rand(0, count($domain)-1)];
	$username = string(12).rand(000,111);
	$email = $username.'@'.$maildo;
	$password = '@Misaka123';

	$headers = array();
	$headers[] = 'Accept: application/json';
	$headers[] = 'User-Agent: ASUS_X00TD_8.1.0_0.1.6.1';
	$headers[] = 'Content-Type: application/x-www-form-urlencoded';
	$headers[] = 'Host: api.bigtoken.com';
	$headers[] = 'Connection: Keep-Alive';
	$curl->header($headers);

	$signup = $curl->post('https://api.bigtoken.com/signup', 'password='.$password.'&monetize=1&referral_id='.$refcode.'&email='.$email);

	if (stripos($signup, 'user_id')) {
		echo "\nSuccess register";
		sleep(3);

		$headers = array();
		$headers[] = 'Origin: https://www.1secmail.com';
		$headers[] = 'Accept-Language: en-US,en;q=0.9';
		$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36 OPR/58.0.3135.118';
		$headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
		$headers[] = 'Accept: */*';
		$headers[] = 'Authority: www.1secmail.com';
		$headers[] = 'X-Requested-With: XMLHttpRequest';
		$curl->header($headers);

		$getMessages = $curl->post('https://www.1secmail.com/mailbox', 'action=getMessages&login='.$username.'&domain='.$maildo);

		if (stripos($getMessages, 'bigtoken.com')) {
			$url = fetch_value($getMessages, '<a href="','">Confirmation needed: Your BIGtoken email address</a>');
			$replace = str_replace('readMessage', 'mailBody', $url);
			$mailBody = $curl->get('https://www.1secmail.com/mailbox'.$replace);
			
			if (stripos($mailBody, 'Thank you for signing up to BIGtoken!')) {

				$confirm = fetch_value($mailBody, '<a class="button" style="width: 110px; height: 22px; background: #528FF5; padding: 12px; text-align: center; border-radius: 6px;color: #fff; font-weight:700;text-decoration:none;" href="','">Confirm now</a>');

				$bigtoken = $curl->get($confirm);

				$link = fetch_value($bigtoken, 'Location: ','
Content-Security-Policy');
				$token = fetch_value($link, 'code=','&type');

				$headers = array();
				$headers[] = 'Content-Type: application/json';
				$headers[] = 'Accept: application/json';
				$headers[] = 'Authority: api.bigtoken.com';
				$curl->header($headers);

				$a = $curl->post('https://api.bigtoken.com/signup/email-verification', '{"email":"'.$email.'","verification_code":"'.$token.'"}');
				echo "\n[".date("h:i:s")."] | Referral Code: ".$refcode." | Ok\n";

			} else {
				echo "\n[".date("h:i:s")."] | Referral Code: ".$refcode." | Cannot get verification link\n";
			}

		} else {
			echo "\n[".date("h:i:s")."] | Referral Code: ".$refcode." | Cannot find verification link\n";
		}

	} else {
		echo "\n[".date("h:i:s")."] | Referral Code: ".$refcode." | Failed\n";
	}

}

echo "Created by yudha tira pamungkas\n";
echo "Referral (Ex: I2IG2HD0M): ";
$refcode = trim(fgets(STDIN));

while (true) {
	ref($refcode);
}



?>
