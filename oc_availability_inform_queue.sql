CREATE TABLE `oc_availability_inform_queue` (
  `availability_inform_queue_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `email` varchar(96) NOT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `oc_availability_inform_queue`
  ADD PRIMARY KEY (`availability_inform_queue_id`),
  ADD KEY `product_id` (`product_id`);

ALTER TABLE `oc_availability_inform_queue`
  MODIFY `availability_inform_queue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;
