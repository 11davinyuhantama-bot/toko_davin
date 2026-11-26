-- Jalankan sekali pada database db_tokoko untuk menyimpan instruksi kanal Duitku.
ALTER TABLE tbl_transaksi
    ADD COLUMN payment_va_number VARCHAR(100) NULL,
    ADD COLUMN payment_qr_string TEXT NULL,
    ADD COLUMN payment_app_url VARCHAR(500) NULL;
