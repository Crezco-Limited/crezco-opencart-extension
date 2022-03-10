<?php
class ControllerExtensionPaymentCrezco extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->language('extension/payment/crezco');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/payment/crezco');
		$this->load->model('setting/setting');
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('crezco', $this->request->post);
														
			$this->session->data['success'] = $this->language->get('success_save');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}
		
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_auto'] = $this->language->get('text_auto');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_general'] = $this->language->get('text_general');
		$data['text_order_status'] = $this->language->get('text_order_status');
		$data['text_production'] = $this->language->get('text_production');
		$data['text_sandbox'] = $this->language->get('text_sandbox');
		$data['text_connect'] = $this->language->get('text_connect');
		$data['text_completed_status'] = $this->language->get('text_completed_status');
		$data['text_denied_status'] = $this->language->get('text_denied_status');
		$data['text_canceled_status'] = $this->language->get('text_canceled_status');
		$data['text_failed_status'] = $this->language->get('text_failed_status');
		$data['text_pending_status'] = $this->language->get('text_pending_status');		
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['entry_api_key'] = $this->language->get('entry_api_key');
		$data['entry_partner_id'] = $this->language->get('entry_partner_id');
		$data['entry_environment'] = $this->language->get('entry_environment');
		$data['entry_connect'] = $this->language->get('entry_connect');
		$data['entry_debug'] = $this->language->get('entry_debug');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$data['help_total'] = $this->language->get('help_total');
						
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_connect'] = $this->language->get('button_connect');
		$data['button_disconnect'] = $this->language->get('button_disconnect');
			
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extensions'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/crezco', 'token=' . $this->session->data['token'], true)
		);
						
		$data['action'] = $this->url->link('extension/payment/crezco', 'token=' . $this->session->data['token'], true);
		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);
		$data['connect_url'] =  str_replace('&amp;', '&', $this->url->link('extension/payment/crezco/connect', 'token=' . $this->session->data['token'], true));
		$data['disconnect_url'] =  str_replace('&amp;', '&', $this->url->link('extension/payment/crezco/disconnect', 'token=' . $this->session->data['token'], true));
		
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$data['server'] = HTTPS_SERVER;
			$data['catalog'] = HTTPS_CATALOG;
		} else {
			$data['server'] = HTTP_SERVER;
			$data['catalog'] = HTTP_CATALOG;
		}
		
		// Setting 		
		$_config = new Config();
		$_config->load('crezco');
		
		$data['setting'] = $_config->get('crezco_setting');
		
		if (!empty($this->request->request['error-message'])) {
			$this->error['warning'] = $this->request->request['error-message'];
		}
					
		if (!empty($this->session->data['environment']) && !empty($this->request->request['user-id'])) {						
			$api_key = $this->session->data['api_key'];
			$partner_id = $this->session->data['partner_id'];
			$environment = $this->session->data['environment'];
			$user_id = $this->request->request['user-id'];
			$webhook_id = '';
						
			require_once DIR_SYSTEM . 'library/crezco/crezco.php';
			
			$crezco_info = array(
				'api_key' => $api_key,
				'partner_id' => $partner_id,
				'environment' => $environment
			);
		
			$crezco = new Crezco($crezco_info);
				
			$webhook_info = array(
				'type' => 'payment',
				'eventType' => 'PaymentAll',
				'callback' => $data['catalog'] . 'index.php?route=extension/payment/crezco/webhook'
			);
			
			$result = $crezco->createWebhook($webhook_info);
			
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
					
					$this->model_extension_payment_crezco->log($error, $error['detail']);
				}
				
				$this->error['warning'] = implode(' ', $error_details);
			} else {
				$webhook_id = $result;
			}
						
			$setting = $this->model_setting_setting->getSetting('crezco');
						
			$setting['crezco_api_key'] = $api_key;
			$setting['crezco_partner_id'] = $partner_id;
			$setting['crezco_environment'] = $environment;
			$setting['crezco_user_id'] = $user_id;
			$setting['crezco_webhook_id'] = $webhook_id;
			
			$this->model_setting_setting->editSetting('crezco', $setting);
						
			unset($this->session->data['api_key']);
			unset($this->session->data['partner_id']);
			unset($this->session->data['environment']);
		}
		
		if (isset($api_key)) {
			$data['api_key'] = $api_key;
		} elseif (isset($this->request->post['crezco_api_key'])) {
			$data['api_key'] = $this->request->post['crezco_api_key'];
		} elseif ($this->config->get('crezco_api_key')) {
			$data['api_key'] = $this->config->get('crezco_api_key');
		} else {
			$data['api_key'] = '';
		}
		
		if (isset($partner_id)) {
			$data['partner_id'] = $partner_id;
		} elseif (isset($this->request->post['crezco_partner_id'])) {
			$data['partner_id'] = $this->request->post['crezco_partner_id'];
		} elseif ($this->config->get('crezco_partner_id')) {
			$data['partner_id'] = $this->config->get('crezco_partner_id');
		} else {
			$data['partner_id'] = '';
		}
		
		if (isset($environment)) {
			$data['environment'] = $environment;
		} elseif (isset($this->request->post['crezco_environment'])) {
			$data['environment'] = $this->request->post['crezco_environment'];
		} elseif ($this->config->get('crezco_environment')) {
			$data['environment'] = $this->config->get('crezco_environment');
		} else {
			$data['environment'] = 'production';
		}
						
		if (isset($user_id)) {
			$data['user_id'] = $user_id;
		} elseif (isset($this->request->post['crezco_user_id'])) {
			$data['user_id'] = $this->request->post['crezco_user_id'];
		} else {
			$data['user_id'] = $this->config->get('crezco_user_id');
		}
		
		if (isset($webhook_id)) {
			$data['webhook_id'] = $webhook_id;
		} elseif (isset($this->request->post['crezco_webhook_id'])) {
			$data['webhook_id'] = $this->request->post['crezco_webhook_id'];
		} else {
			$data['webhook_id'] = $this->config->get('crezco_webhook_id');
		}
		
		if (isset($this->request->post['crezco_debug'])) {
			$data['debug'] = $this->request->post['crezco_debug'];
		} else {
			$data['debug'] = $this->config->get('crezco_debug');
		}
								
		if (isset($this->request->post['crezco_total'])) {
			$data['total'] = $this->request->post['crezco_total'];
		} else {
			$data['total'] = $this->config->get('crezco_total');
		}
		
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['crezco_geo_zone_id'])) {
			$data['geo_zone_id'] = $this->request->post['crezco_geo_zone_id'];
		} else {
			$data['geo_zone_id'] = $this->config->get('crezco_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['crezco_status'])) {
			$data['status'] = $this->request->post['crezco_status'];
		} else {
			$data['status'] = $this->config->get('crezco_status');
		}

		if (isset($this->request->post['crezco_sort_order'])) {
			$data['sort_order'] = $this->request->post['crezco_sort_order'];
		} else {
			$data['sort_order'] = $this->config->get('crezco_sort_order');
		}
								
		if (isset($this->request->post['crezco_setting'])) {
			$data['setting'] = array_replace_recursive((array)$data['setting'], (array)$this->request->post['crezco_setting']);
		} else {
			$data['setting'] = array_replace_recursive((array)$data['setting'], (array)$this->config->get('crezco_setting'));
		}
		
		if ($data['user_id']) {										
			require_once DIR_SYSTEM . 'library/crezco/crezco.php';
			
			$crezco_info = array(
				'api_key' => $data['api_key'],
				'partner_id' => $data['partner_id'],
				'environment' => $data['environment']
			);
		
			$crezco = new Crezco($crezco_info);
			
			$result = $crezco->getUser($data['user_id']);
											
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
					
					$this->model_extension_payment_crezco->log($error, $error['detail']);
				}
				
				$this->error['warning'] = implode(' ', $error_details);
			} else {
				$data['text_connect'] = sprintf($this->language->get('text_connect'), $data['user_id'], $result['eMail'], $result['displayName']);
			}
		}
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['api_key'])) {
			$data['error_api_key'] = $this->error['api_key'];
		} else {
			$data['error_api_key'] = '';
		}
		
		if (isset($this->error['partner_id'])) {
			$data['error_partner_id'] = $this->error['partner_id'];
		} else {
			$data['error_partner_id'] = '';
		}
					
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/crezco', $data));
	}
	
	public function connect() {
		$this->load->language('extension/payment/crezco');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$partner_url = $this->url->link('extension/payment/crezco', 'token=' . $this->session->data['token'], true);
			$partner_url = str_replace('&amp;', '%26', $partner_url);
			$partner_url = str_replace('?', '%3F', $partner_url);
		
			if ($this->request->post['crezco_environment'] == 'production') {
				$data['redirect'] = 'https://app.crezco.com/onboarding?partner_id=' . $this->request->post['crezco_partner_id'] . '&redirect_uri=' . $partner_url;
			} else {
				$data['redirect'] = 'https://app.sandbox.crezco.com/onboarding?partner_id=' . $this->request->post['crezco_partner_id'] . '-sandbox&redirect_uri=' . $partner_url;
			}

			$this->session->data['api_key'] = $this->request->post['crezco_api_key'];
			$this->session->data['partner_id'] = $this->request->post['crezco_partner_id'];
			$this->session->data['environment'] = $this->request->post['crezco_environment'];
		}
				
		$data['error'] = $this->error;
		
		$this->response->setOutput(json_encode($data));
	}
	
	public function disconnect() {
		$this->load->model('setting/setting');
				
		// Setting 		
		$_config = new Config();
		$_config->load('crezco');
		
		$config_setting = $_config->get('crezco_setting');
		
		$setting = $this->model_setting_setting->getSetting('crezco');

		if (!empty($setting['crezco_webhook_id'])) {
			require_once DIR_SYSTEM . 'library/crezco/crezco.php';
			
			$crezco_info = array(
				'api_key' => $setting['crezco_api_key'],
				'partner_id' => $setting['crezco_partner_id'],
				'environment' => $setting['crezco_environment']
			);
		
			$crezco = new Crezco($crezco_info);
				
			$result = $crezco->deleteWebhook($setting['crezco_webhook_id']);
			
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
					
					$this->model_extension_payment_crezco->log($error, $error['detail']);
				}
			
				$this->error['warning'] = implode(' ', $error_details);
			}
		}
			
		$setting['crezco_user_id'] = '';
		$setting['crezco_webhook_id'] = '';
				
		$this->model_setting_setting->editSetting('crezco', $setting);
		
		$data['error'] = $this->error;
		
		$this->response->setOutput(json_encode($data));
	}
		
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/crezco')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$this->request->post['crezco_api_key'] = trim($this->request->post['crezco_api_key']);

		if (utf8_strlen($this->request->post['crezco_api_key']) != 32) {
			$this->error['api_key'] = $this->language->get('error_api_key');
			$this->error['warning'] = $this->language->get('error_warning');
		} 
		
		$this->request->post['crezco_partner_id'] = trim($this->request->post['crezco_partner_id']);
		
		preg_match('/[a-z][a-z0-9]{3,41}/', $this->request->post['crezco_partner_id'], $partner_id, PREG_OFFSET_CAPTURE);

		if (empty($partner_id[0])) {
			$this->error['partner_id'] = $this->language->get('error_partner_id');
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}