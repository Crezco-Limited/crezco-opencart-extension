<?php
class ModelExtensionPaymentCrezco extends Model {
			
	public function log($data, $title = null) {
		if ($this->config->get('crezco_debug')) {
			$log = new Log('crezco.log');
			$log->write('Crezco debug (' . $title . '): ' . json_encode($data));
		}
	}
}
