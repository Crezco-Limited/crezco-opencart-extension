<?php
class Crezco {
	private $server = array(
		'sandbox' => 'https://api.sandbox.crezco.com',
		'production' => 'https://api.crezco.com'
	);
	private $environment = 'sandbox';
	private $api_key = '';
	private $partner_id = '';
	private $access_token = '';
	private $errors = array();
	private $last_response = array();
		
	//IN:  crezco info
	public function __construct($crezco_info) {
		if (!empty($crezco_info['api_key'])) {
			$this->api_key = $crezco_info['api_key'];
		}
		
		if (!empty($crezco_info['partner_id'])) {
			$this->partner_id = $crezco_info['partner_id'];
		}
				
		if (!empty($crezco_info['environment']) && (($crezco_info['environment'] == 'production') || ($crezco_info['environment'] == 'sandbox'))) {
			$this->environment = $crezco_info['environment'];
		}
	}
	
	//IN:  user info
	public function createUser($user_info) {
		$command = '/v1/users';
		
		$params = array(
			'request' => $user_info,
			'idempotencyId' => $this->token()
		);
		
		$result = $this->execute('POST', $command, $params, true);
		
		if (empty($result['detail']) && empty($result['Detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  user id
	//OUT: user info, if no return - check errors
	public function updateUser($user_id, $user_info) {
		$command = '/v1/users/' . $user_id;
		
		$params = $user_info;
				
		$result = $this->execute('PUT', $command, $params, true);
		
		if (empty($result['detail']) && empty($result['Detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  user id
	public function deleteUser($user_id) {
		$command = '/v1/users/' . $user_id;
				
		$result = $this->execute('DELETE', $command);
		
		if (empty($result['detail']) && empty($result['Detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  user id
	//OUT: user info, if no return - check errors
	public function getUser($user_id) {
		$command = '/v1/users/' . $user_id;
				
		$result = $this->execute('GET', $command);
		
		if (empty($result['detail']) && empty($result['Detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
					
	//IN:  user id, pay demand info
	public function createPayDemand($user_id, $pay_demand_info) {
		$command = '/v1/users/' . $user_id . '/pay-demands';
		
		$params = array(
			'request' => $pay_demand_info,
			'idempotencyId' => $this->token()
		);
				
		$result = $this->execute('POST', $command, $params, true);
		
		if (empty($result['detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
		
	//IN:  pay demand id
	//OUT: pay demand info, if no return - check errors
	public function getPayDemand($pay_demand_id) {
		$command = '/v1/pay-demands/' . $pay_demand_id;
				
		$result = $this->execute('GET', $command);
		
		if (empty($result['detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  user id
	//OUT: pay demands info, if no return - check errors
	public function getPayDemands($user_id) {
		$command = '/v1/users/' . $user_id . '/pay-demands';
				
		$result = $this->execute('GET', $command);
		
		if (empty($result['detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  user id, pay demand id
	public function deletePayDemand($user_id, $pay_demand_id) {
		$command = '/v1/users/' . $user_id . '/pay-demands/' . $pay_demand_id;
				
		$result = $this->execute('DELETE', $command);
		
		if (empty($result['detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  user id, pay demand id, payment info
	public function createPayment($user_id, $pay_demand_id, $payment_info) {
		$command = '/v1/users/' . $user_id . '/pay-demands/' . $pay_demand_id . '/payment';
		
		$params = $payment_info;
		
		if ($params) {
			$command .= '?' . $this->buildQuery($params);
		}		
				
		$result = $this->execute('POST', $command);
		
		if (empty($result['detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  payment id
	//OUT: payment info, if no return - check errors
	public function getPayment($payment_id) {
		$command = '/v1/payments/' . $payment_id;
				
		$result = $this->execute('GET', $command);
		
		if (empty($result['detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  pay demand id
	//OUT: payments info, if no return - check errors
	public function getPayments($pay_demand_id) {
		$command = '/v1/pay-demands/' . $pay_demand_id . '/payments';
				
		$result = $this->execute('GET', $command);
		
		if (empty($result['detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  payment id
	//OUT: payment status, if no return - check errors
	public function getPaymentStatus($payment_id) {
		$command = '/v1/payments/' . $payment_id . '/status';
				
		$result = $this->execute('GET', $command);
		
		if (empty($result['detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  country_iso_code_2, bank_feature_type
	//OUT: bank info, if no return - check errors
	public function getBanks($country_iso_code_2, $bank_feature_type) {
		$command = '/v1/banks/' . $country_iso_code_2 . '/' . $bank_feature_type;
				
		$result = $this->execute('GET', $command);
		
		if (empty($result['detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
	
	//IN:  webhook info
	public function createWebhook($webhook_info) {
		$command = '/v1/webhooks';
		
		$params = $webhook_info;
				
		$result = $this->execute('POST', $command, $params, true);
		
		if (empty($result['detail']) && empty($result['Detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
		
	//IN:  webhook id
	public function deleteWebhook($webhook_id) {
		$command = '/v1/webhooks/' . $webhook_id;
				
		$result = $this->execute('DELETE', $command);
		
		if (empty($result['detail']) && empty($result['Detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
		
	//OUT: webhooks info, if no return - check errors
	public function getWebhooks() {
		$command = '/v1/webhooks';
				
		$result = $this->execute('GET', $command);
		
		if (empty($result['detail']) && empty($result['Detail'])) {
			return $result;
		} else {
			$this->errors[] = $result;
									
			return false;
		}
	}
					
	//OUT: number of errors
	public function hasErrors()	{
		return count($this->errors);
	}
	
	//OUT: array of errors
	public function getErrors()	{
		return $this->errors;
	}
	
	//OUT: last response
	public function getResponse() {
		return $this->last_response;
	}
	
	private function execute($method, $command, $params = array(), $json = false) {
		$this->errors = array();

		if ($method && $command) {
			$curl_options = array(
				CURLOPT_URL => $this->server[$this->environment] . $command,
				CURLOPT_HEADER => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_INFILESIZE => Null,
				CURLOPT_HTTPHEADER => array(),
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT => 10
			);
			
			$curl_options[CURLOPT_HTTPHEADER][] = 'Accept-Charset: utf-8';
			$curl_options[CURLOPT_HTTPHEADER][] = 'Accept: application/json';
			$curl_options[CURLOPT_HTTPHEADER][] = 'Accept-Language: en_US';
			$curl_options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
						
			if ($this->api_key) {
				$curl_options[CURLOPT_HTTPHEADER][] = 'X-Crezco-Key: ' . $this->api_key;
			}

			switch (strtolower(trim($method))) {
				case 'get':
					$curl_options[CURLOPT_HTTPGET] = true;
					$curl_options[CURLOPT_URL] .= '?' . $this->buildQuery($params, $json);
										
					break;
				case 'post':
					$curl_options[CURLOPT_POST] = true;
					$curl_options[CURLOPT_POSTFIELDS] = $this->buildQuery($params, $json);
										
					break;
				case 'put':
					$curl_options[CURLOPT_POST] = true;
					$curl_options[CURLOPT_POSTFIELDS] = $this->buildQuery($params, $json);
					$curl_options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
										
					break;
				case 'delete':
					$curl_options[CURLOPT_POST] = true;
					$curl_options[CURLOPT_POSTFIELDS] = $this->buildQuery($params, $json);
					$curl_options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
										
					break;
			}
			//print_R($curl_options);
			$ch = curl_init();
			curl_setopt_array($ch, $curl_options);
			$response = curl_exec($ch);
			//echo $response;
			if (curl_errno($ch)) {
				$curl_code = curl_errno($ch);
				
				$constant = get_defined_constants(true);
				$curl_constant = preg_grep('/^CURLE_/', array_flip($constant['curl']));
				
				$this->errors[] = array('title' => $curl_constant[$curl_code], 'detail' => curl_strerror($curl_code));
			}
	
            /*$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			if (($status_code >= 0) && ($status_code < 200)) {
				$this->errors[] = 'Server Not Found (' . $status_code . ')';
			}
			
			if (($status_code >= 300) && ($status_code < 400)) {
				$this->errors[] = 'Page Redirect (' . $status_code . ')';
			}
			
			if (($status_code >= 400) && ($status_code < 500)) {
				$this->errors[] = 'Page not found (' . $status_code . ')';
			}
			
			if ($status_code >= 500) {
				$this->errors[] = 'Server Error (' . $status_code . ')';
			}*/
			
			$head = '';
			$body = '';
			
			$parts = explode("\r\n\r\n", $response, 3);
			
			if (isset($parts[0]) && isset($parts[1])) {
				if (($parts[0] == 'HTTP/1.1 100 Continue') && isset($parts[2])) {
					list($head, $body) = array($parts[1], $parts[2]);
				} else {
					list($head, $body) = array($parts[0], $parts[1]);
				}
            }
			
            $response_headers = array();
            $header_lines = explode("\r\n", $head);
            array_shift($header_lines);
			
            foreach ($header_lines as $line) {
                list($key, $value) = explode(':', $line, 2);
                $response_headers[$key] = $value;
            }
			
			curl_close($ch);
			
			if (isset($buffer) && is_resource($buffer)) {
                fclose($buffer);
            }

			$this->last_response = json_decode($body, true);
			
			if (isset($this->last_response['Detail'])) {
				$this->last_response['detail'] = $this->last_response['Detail'];
			}
			
			return $this->last_response;		
		}
	}
	
	private function buildQuery($params, $json = false) {
		if (is_string($params)) {
            return $params;
        }
		
		if ($json) {
			return json_encode($params);
		} else {
			return http_build_query($params);
		}
    }
	
	private function token() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
}