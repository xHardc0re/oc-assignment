<?php
namespace Opencart\Catalog\Controller\Cron;
/**
 * Class AvailabilityInform
 *
 * @package Opencart\Catalog\Controller\Cron
 */
class AvailabilityInform extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @param int    $cron_id
	 * @param string $code
	 * @param string $cycle
	 * @param string $date_added
	 * @param string $date_modified
	 *
	 * @return void
	 */
	public function index(int $cron_id, string $code, string $cycle, string $date_added, string $date_modified): void {
        $log = new \Opencart\System\Library\Log('availability_inform.log');

        $this->load->model('account/availability_inform');

        // Handle product deletion
        $this->model_account_availability_inform->queueCleanup();

		$filter_data = [
            'is_available' => true,
            'limit'        => 10
		];

		// Process Queue
        $results = $this->model_account_availability_inform->getQueueData($filter_data);

        if (empty($results)) {
            return;
        }

        $this->load->language('mail/availability_inform');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $this->load->model('setting/setting');

        if (!$this->config->get('config_mail_engine')) {
            return;
        }

        $data['title'] = $this->language->get('text_subject');
        $data['text_back_in_stock'] = $this->language->get('text_back_in_stock');

        $from = $this->model_setting_setting->getValue('config_email', $this->config->get('config_store_id'));

		if (!$from) {
			$from = $this->config->get('config_email');
		}

        $mail_option = [
            'parameter'     => $this->config->get('config_mail_parameter'),
            'smtp_hostname' => $this->config->get('config_mail_smtp_hostname'),
            'smtp_username' => $this->config->get('config_mail_smtp_username'),
            'smtp_password' => html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
            'smtp_port'     => $this->config->get('config_mail_smtp_port'),
            'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
        ];

		foreach ($results as $result) {
            $availability_inform_queue_ids = [];
            $email = $result['email'];
			$queue_data = explode(',', $result['queue_data']);
            $product_ids = [];
            $data['products'] = [];

            foreach ($queue_data as $queue_row) {
                $queue = explode(':', $queue_row);
                $availability_inform_queue_ids[] = (int)$queue[0];
                $product_id = $queue[1];
                $product_ids[] = (int)$product_id;

                $product_info = $this->model_catalog_product->getProduct($product_id);

                if (is_file(DIR_IMAGE . html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'))) {
					$thumb = $this->model_tool_image->resize(html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_thumb_width'), $this->config->get('config_image_thumb_height'));
				} else {
					$thumb = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_thumb_width'), $this->config->get('config_image_thumb_height'));
				}

                $data['products'][] = [
                    'name'  => $product_info['name'],
                    'model' => $product_info['model'],
                    'thumb' => $thumb,
                    'price' => $this->currency->format($this->tax->calculate($product_info['special'] ?? $product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
                    'href'  => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_info['product_id'])
                ];
            }

            $product_ids = implode(', ', $product_ids);
            $log->write("Sending availability inform email to: $email for product ids: $product_ids!");

            $mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'), $mail_option);
            $mail->setTo($email);
            $mail->setFrom($from);
            $mail->setSender($this->config->get('config_name'));
            $mail->setSubject($data['title']);
            $mail->setHtml($this->load->view('mail/availability_inform', $data));
            $mail->send();

            $this->model_account_availability_inform->removeFromQueue($availability_inform_queue_ids);
		}
    }
}
