<?php
class ControllerPaymentCrezco extends Controller {
	private $error = array();
						
	public function index() {
		if ($this->config->get('crezco_user_id')) {
			$this->load->language('payment/crezco');
		
			$this->load->model('payment/crezco');
											
			// Setting
			$_config = new Config();
			$_config->load('crezco');
			
			$config_setting = $_config->get('crezco_setting');
		
			$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('crezco_setting'));
						
			$data['api_key'] = $this->config->get('crezco_api_key');
			$data['partner_id'] = $this->config->get('crezco_partner_id');
			$data['user_id'] = $this->config->get('crezco_user_id');
			$data['environment'] = $this->config->get('crezco_environment');
			
			$data['text_loading'] = $this->language->get('text_loading');
						
			$data['button_confirm'] = $this->language->get('button_confirm');

			require_once DIR_SYSTEM .'library/crezco/crezco.php';
			
			$crezco_info = array(
				'api_key' => $data['api_key'],
				'partner_id' => $data['partner_id'],
				'environment' => $data['environment']
			);
		
			$crezco = new Crezco($crezco_info);
			
			$result = $crezco->getBanks('GB', 'DomesticInstantPayment');
			
			if ($crezco->hasErrors()) {
				$error_details = array();
				
				$errors = $crezco->getErrors();
								
				foreach ($errors as $error) {
					if (isset($error['title']) && ($error['title'] == 'CURLE_OPERATION_TIMEOUTED')) {
						$error['detail'] = $this->language->get('error_timeout');
					}
					
					if (isset($error['detail'])) {
						$error_details[] = $error['detail'];
					}
					
					$this->model_payment_crezco->log($error, $error['detail']);
				}
				
				$this->error['warning'] = implode(' ', $error_details);
			} else {
				$banks = $result;
			}
						
			if ($this->error && isset($this->error['warning'])) {
				$this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
			}

			if (isset($this->error['warning'])) {
				$data['error_warning'] = $this->error['warning'];
			} else {
				$data['error_warning'] = '';
			}			

			if (VERSION >= '2.2.0.0') {
				return $this->load->view('payment/crezco', $data);
			} elseif (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/crezco.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/payment/crezco.tpl', $data);
			} else {
				return $this->load->view('default/template/payment/crezco.tpl', $data);
			}
		}
	}
		
	public function confirm() {					
		$this->load->language('payment/crezco');
		
		$this->load->model('payment/crezco');
		$this->load->model('checkout/order');
				
		// Setting
		$_config = new Config();
		$_config->load('crezco');
			
		$config_setting = $_config->get('crezco_setting');
		
		$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('crezco_setting'));
						
		$api_key = $this->config->get('crezco_api_key');
		$partner_id = $this->config->get('crezco_partner_id');
		$user_id = $this->config->get('crezco_user_id');
		$environment = $this->config->get('crezco_environment');
							
		if (VERSION >= '2.2.0.0') {
			$currency_code = $this->session->data['currency'];
		} else {
			$currency_code = $this->currency->getCode();	
		}
				
		$currency_value = $this->currency->getValue($currency_code);
		$decimal_place = $this->currency->getDecimalPlace($currency_code);
									
		$order_id = $this->session->data['order_id'];
		
		$order_info = $this->model_checkout_order->getOrder($order_id);
			
		$order_total = number_format($order_info['total'] * $currency_value, $decimal_place, '.', '');
		
		require_once DIR_SYSTEM .'library/crezco/crezco.php';
			
		$crezco_info = array(
			'api_key' => $api_key,
			'partner_id' => $partner_id,
			'environment' => $environment
		);
		
		$crezco = new Crezco($crezco_info);
	
		$pay_demand_info = array(
			'useDefaultBeneficiaryAccount' => true,
			'dueDate' => date('Y-m-d'),
			'currency' => $currency_code,
			'amount' => $order_total,
			'reference' => 'Order ' . $order_id
		);
						
		$result = $crezco->createPayDemand($user_id, $pay_demand_info);
			
		if ($crezco->hasErrors()) {
			$error_details = array();
				
			$errors = $crezco->getErrors();
								
			foreach ($errors as $error) {
				if (isset($error['title']) && ($error['title'] == 'CURLE_OPERATION_TIMEOUTED')) {
					$error['detail'] = $this->language->get('error_timeout');
				}
					
				if (isset($error['detail'])) {
					$error_details[] = $error['detail'];
				}
					
				$this->model_payment_crezco->log($error, $error['detail']);
			}
				
			$this->error['warning'] = implode(' ', $error_details);
		} else {
			$pay_demand_id = $result;
		}
		
		if (!empty($pay_demand_id)) {
			$payment_info = array(
				'initialScreen' => 'BankSelection',
				'finalScreen' => 'PaymentStatus',
				'amount' => $order_total,
				'countryIso2Code' => 'GB',
				'successCallbackUri' => $this->url->link('checkout/success', '', true),
				'failureCallbackUri' => $this->url->link('payment/crezco/failurePage', '', true)
			);
			
			if (!empty($order_info['email'])) {
				$payment_info['payerEmail'] = $order_info['email'];
			}
									
			$result = $crezco->createPayment($user_id, $pay_demand_id, $payment_info);
			
			if ($crezco->hasErrors()) {
				$error_details = array();
				
				$errors = $crezco->getErrors();
								
				foreach ($errors as $error) {
					if (isset($error['title']) && ($error['title'] == 'CURLE_OPERATION_TIMEOUTED')) {
						$error['detail'] = $this->language->get('error_timeout');
					}
					
					if (isset($error['detail'])) {
						$error_details[] = $error['detail'];
					}
					
					$this->model_payment_crezco->log($error, $error['detail']);
				}
				
				$this->error['warning'] = implode(' ', $error_details);
			} elseif (!empty($result['redirect'])) {
				$data['redirect'] = $result['redirect'];
			}
		}
			
		if ($this->error && isset($this->error['warning'])) {
			$this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
							
		$data['error'] = $this->error;
				
		$this->response->setOutput(json_encode($data));
	}
	
	public function failurePage() {
		$this->load->language('payment/crezco');

		$this->document->setTitle($this->language->get('text_failure_page_title'));
				
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', '', 'SSL')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart', '', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_title'),
			'href' => $this->url->link('payment/crezco/failurePage', '', 'SSL')
		);
		
		$data['text_title'] = $this->language->get('text_failure_page_title');
		$data['text_message'] = sprintf($this->language->get('text_failure_page_message'), $this->url->link('information/contact', '', 'SSL'));
		
		$data['button_continue'] = $this->language->get('button_continue');
		
		$data['continue'] = $this->url->link('common/home');
				
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		if (VERSION >= '2.2.0.0') {
			$this->response->setOutput($this->load->view('payment/crezco/failure_page', $data));
		} elseif (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/crezco/failure_page.tpl')) {
			$this->response->setOutput($this->config->get('config_template') . '/template/payment/crezco/failure_page.tpl', $data);
		} else {
			$this->response->setOutput($this->load->view('default/template/payment/crezco/failure_page.tpl', $data));
		}
	}
			
	public function webhook() {				
		$this->load->model('payment/crezco');
						
		$webhooks_info = json_decode(html_entity_decode(file_get_contents('php://input')), true);
		
		foreach ($webhooks_info as $webhook_info) {
			$this->model_payment_crezco->log($webhook_info, 'Webhook');
		
			if (!empty($webhook_info['id']) && !empty($webhook_info['metadata']['payDemandId']) && !empty($webhook_info['eventType'])) {
				$payment_id = $webhook_info['id'];
				$pay_demand_id = $webhook_info['metadata']['payDemandId'];
				$event_type = $webhook_info['eventType'];
			
				// Setting
				$_config = new Config();
				$_config->load('crezco');
			
				$config_setting = $_config->get('crezco_setting');
		
				$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('crezco_setting'));
						
				$api_key = $this->config->get('crezco_api_key');
				$partner_id = $this->config->get('crezco_partner_id');
				$user_id = $this->config->get('crezco_user_id');
				$environment = $this->config->get('crezco_environment');
						
				require_once DIR_SYSTEM .'library/crezco/crezco.php';
		
				$crezco_info = array(
					'api_key' => $api_key,
					'partner_id' => $partner_id,
					'environment' => $environment
				);
		
				$crezco = new Crezco($crezco_info);
			
				$result = $crezco->getPayDemand($pay_demand_id);
						
				if ($crezco->hasErrors()) {
					$errors = $crezco->getErrors();
								
					foreach ($errors as $error) {
						if (isset($error['title']) && ($error['title'] == 'CURLE_OPERATION_TIMEOUTED')) {
							$error['detail'] = $this->language->get('error_timeout');
						}
										
						$this->model_payment_crezco->log($error, $error['detail']);
					}
				} elseif (!empty($result['reference'])) {
					$order_id = str_replace('Order ', '', $result['reference']);
				}
				
				$result = $crezco->getPaymentStatus($payment_id);
		
				if ($crezco->hasErrors()) {
					$errors = $crezco->getErrors();
								
					foreach ($errors as $error) {
						if (isset($error['title']) && ($error['title'] == 'CURLE_OPERATION_TIMEOUTED')) {
							$error['detail'] = $this->language->get('error_timeout');
						}
										
						$this->model_payment_crezco->log($error, $error['detail']);
					}
				} elseif (!empty($result['code'])) {
					$payment_status = $result['code'];
				}
										
				if (!empty($order_id) && !empty($payment_status)) {
					$order_status_id = 0;
					
					if ($event_type == 'PaymentPending') {
						$order_status_id = $setting['order_status']['pending']['id'];
					}
		
					if ($event_type == 'PaymentFailed') {
						$order_status_id = $setting['order_status']['failed']['id'];
					}
			
					if ($event_type == 'PaymentCompleted') {
						$order_status_id = $setting['order_status']['completed']['id'];
					}
				
					if ($payment_status == 'Cancelled') {
						$order_status_id = $setting['order_status']['canceled']['id'];
					}
				
					if ($payment_status == 'Failed') {
						$order_status_id = $setting['order_status']['failed']['id'];
					}
				
					if ($payment_status == 'Denied') {
						$order_status_id = $setting['order_status']['denied']['id'];
					}
				
					if ($payment_status == 'Declined') {
						$order_status_id = $setting['order_status']['denied']['id'];
					}
							
					if ($order_status_id) {
						$this->load->model('checkout/order');

						$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, '', true);
					}
				}
			}
		}
	}
}