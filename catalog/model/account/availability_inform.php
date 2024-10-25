<?php
namespace Opencart\Catalog\Model\Account;
/**
 * Class AvailabilityInform
 *
 * @package Opencart\Catalog\Model\Account
 */
class AvailabilityInform extends \Opencart\System\Engine\Model {
	/**
	 * Store customer's email and product ID
	 * to inform queue
	 *
	 * @param string $email
	 * @param int $product_id
	 *
	 * @return void
	 */
	public function addToQueue(string $email, int $product_id): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "availability_inform_queue` SET `email` = '" . $this->db->escape($email) . "', `product_id` = '" . (int)$product_id . "'");
	}

	/**
	 * @param array $data filters
	 *
	 * @return array
	 */
	public function getQueueData(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "availability_inform_queue` WHERE 1"; 

		if (!empty($data['email'])) {
			$sql .= " AND `email` = '" . $this->db->escape($data['email']) . "'";
		}

		if (!empty($data['product_id'])) {
			$sql .= " AND `product_id` = " . (int)$data['product_id'];
		}

		$limit = (int)($data['limit'] ?? 20);
		$sql .= " LIMIT " . $limit;

		return $this->db->query($sql)->rows;
	}
}
