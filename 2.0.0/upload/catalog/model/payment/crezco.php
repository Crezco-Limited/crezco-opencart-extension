<?php
class ModelPaymentCrezco extends Model {
	
	public function getMethod($address, $total) {
		$this->load->language('payment/crezco');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('crezco_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (VERSION >= '2.2.0.0') {
			$currency_code = $this->session->data['currency'];
		} else {
			$currency_code = $this->currency->getCode();	
		}
		
		if ($this->config->get('crezco_user_id') && ($this->config->get('crezco_total') > 0 && $this->config->get('crezco_total') > $total) || ($currency_code != 'GBP')) {
			$status = false;
		} elseif (!$this->config->get('crezco_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'crezco',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('crezco_sort_order')
			);
		}

		return $method_data;
	}
	
	public function log($data, $title = null) {
		if ($this->config->get('crezco_debug')) {
			$log = new Log('crezco.log');
			$log->write('Crezco debug (' . $title . '): ' . json_encode($data));
		}
	}
}