-- Jalankan pada database db_tokoko untuk mengaktifkan login RFID admin.
ALTER TABLE `tbl_user`
  ADD COLUMN IF NOT EXISTS `rfid_uid` varchar(128) DEFAULT NULL AFTER `role`;

-- UID kartu admin dan kasir.
UPDATE `tbl_user`
SET `rfid_uid` = '0008493873'
WHERE `username` = 'admin' AND `role` = 'admin';

UPDATE `tbl_user`
SET `rfid_uid` = '0008305215'
WHERE `username` = 'kasir' AND `role` = 'kasir';

UPDATE `tbl_user`
SET `rfid_uid` = '0008442193'
WHERE `username` = 'pelanggan' AND `role` = 'pelanggan';
 
