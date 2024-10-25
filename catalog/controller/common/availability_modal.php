<?php
namespace Opencart\Catalog\Controller\Common;
/**
 * Class AvailabilityModal
 *
 * @package Opencart\Catalog\Controller\Common
 */
class AvailabilityModal extends \Opencart\System\Engine\Controller {
	/**
	 * @return string
	 */
	public function index(): string {
        $this->load->language('common/availability_modal');

        $data['inform'] = $this->url->link('account/availability_inform.add', 'language=' . $this->config->get('config_language'));

		return $this->load->view('common/availability_modal', $data);
	}
}
