-- Jalankan pada database db_tokoko setelah tbl_transaksi dan tbl_detail_transaksi tersedia.
-- Transaksi lama dianggap sudah dibayar; transaksi pelanggan baru menunggu bukti QRIS.
ALTER TABLE `tbl_transaksi`
  ADD COLUMN IF NOT EXISTS `payment_status` varchar(20) NOT NULL DEFAULT 'paid',
  ADD COLUMN IF NOT EXISTS `payment_order_id` varchar(64) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `payment_method` varchar(30) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `payment_proof` varchar(255) DEFAULT NULL;

ALTER TABLE `tbl_detail_transaksi`
  ADD COLUMN IF NOT EXISTS `id_produk` int(11) DEFAULT NULL AFTER `id_transaksi`;

CREATE OR REPLACE VIEW `view_transaksi_pelanggan` AS
SELECT `t`.`id_transaksi`, `t`.`tanggal`, `p`.`nama_pelanggan`, `p`.`kota`, `t`.`total_belanja`
FROM `tbl_transaksi` `t`
JOIN `tbl_pelanggan` `p` ON `t`.`id_pelanggan` = `p`.`id_pelanggan`
WHERE `t`.`payment_status` = 'paid';

CREATE OR REPLACE VIEW `view_total_belanja` AS
SELECT `p`.`id_pelanggan`, `p`.`nama_pelanggan`, COUNT(`t`.`id_transaksi`) AS `total_transaksi`, COALESCE(SUM(`t`.`total_belanja`), 0) AS `total_belanja`
FROM `tbl_pelanggan` `p`
LEFT JOIN `tbl_transaksi` `t` ON `p`.`id_pelanggan` = `t`.`id_pelanggan` AND `t`.`payment_status` = 'paid'
GROUP BY `p`.`id_pelanggan`, `p`.`nama_pelanggan`;

