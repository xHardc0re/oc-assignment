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
		$sql = "SELECT
					`aiq`.`email`,
					GROUP_CONCAT(CONCAT(`aiq`.`availability_inform_queue_id`, ':', `aiq`.`product_id`)) AS `queue_data`
				FROM
					`" . DB_PREFIX . "availability_inform_queue` `aiq`
				LEFT JOIN
					`" . DB_PREFIX . "product` `p`
				ON
					`aiq`.`product_id` = `p`.`product_id`
				WHERE 1";

		if (!empty($data['email'])) {
			$sql .= " AND `aiq`.`email` = '" . $this->db->escape($data['email']) . "'";
		}

		if (!empty($data['product_id'])) {
			$sql .= " AND `aiq`.`product_id` = " . (int)$data['product_id'];
		}

		if (!empty($data['is_available'])) {
			$sql .= " AND `p`.`quantity` >= 0 AND `p`.`status` = '1' AND `p`.`date_available` <= NOW()";
		}

		$limit = (int)($data['limit'] ?? 20);
		$sql .= " GROUP BY `aiq`.`email`";
		$sql .= " ORDER BY `aiq`.`date_added`";
		$sql .= " LIMIT " . $limit;

		return $this->db->query($sql)->rows;
	}

	/**
	 * @param array $availability_inform_queue_id
	 *
	 * @return void
	 */
	public function removeFromQueue(array $availability_inform_queue_ids): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "availability_inform_queue` WHERE `availability_inform_queue_id` IN (" . implode(',', $availability_inform_queue_ids) . ")");
	}

	/**
	 *
	 * @return void
	 */
	public function queueCleanup(): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "availability_inform_queue` WHERE `product_id` NOT IN (SELECT `product_id` FROM `" . DB_PREFIX . "product`)");
	}
}
