-- Jalankan sekali pada database db_tokoko sebelum memakai checkout Duitku.
ALTER TABLE tbl_transaksi
    ADD COLUMN payment_reference VARCHAR(100) NULL,
    ADD COLUMN payment_url VARCHAR(500) NULL;
