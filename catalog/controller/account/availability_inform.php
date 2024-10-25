<?php
namespace Opencart\Catalog\Controller\Account;
/**
 * Class AvailabilityInform
 *
 * @package Opencart\Catalog\Controller\Account
 */
class AvailabilityInform extends \Opencart\System\Engine\Controller {
	/**
	 * @return void
	 */
	public function add(): void {
		$this->load->language('account/availability_inform');

		$json = [];
		$email = $this->request->post['email'] ?? null;
		$product_id = (int)($this->request->post['product_id'] ?? 0);

		// Validate email
		if (!$email || oc_strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$json['error']['email'] = $this->language->get('error_email');
		}

		// Load product model and check if product exists
		$this->load->model('catalog/product');
		$product_info = $this->model_catalog_product->getProduct($product_id);

		if (!$product_info) {
			$json['error']['product'] = $this->language->get('error_product');
		}

		// Check if the email is already in the queue for the product
		if (!$json && $this->isAlreadyInQueue($email, $product_id)) {
			$json['error']['in_queue'] = $this->language->get('error_in_queue');
		}

		// If no errors, add email to the queue
		if (!$json) {
			$this->load->model('account/availability_inform');
			$this->model_account_availability_inform->addToQueue($email, $product_id);
			$json['success'] = sprintf($this->language->get('text_success'), $product_info['name']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * @param string $email
	 * @param int $product_id
	 *
	 * @return bool
	 */
	private function isAlreadyInQueue(string $email, int $product_id): bool {
		$this->load->model('account/availability_inform');

		return !empty($this->model_account_availability_inform->getQueueData(['email' => $email, 'product_id' => $product_id])) ? true : false;
	}
}
