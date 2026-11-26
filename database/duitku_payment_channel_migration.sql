-- Jalankan sekali pada database db_tokoko untuk menyimpan kanal Duitku yang dipilih.
ALTER TABLE tbl_transaksi
    ADD COLUMN payment_channel VARCHAR(100) NULL;
