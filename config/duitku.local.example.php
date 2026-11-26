<?php
// Salin file ini menjadi duitku.local.php, lalu isi merchant code dari dashboard Duitku.
return [
    'merchant_code' => 'ISI_MERCHANT_CODE',
    'api_key' => 'ISI_API_KEY',
    'mode' => 'sandbox', // gunakan 'production' setelah akun dan kanal pembayaran aktif
    'base_url' => 'https://tokoku.example.com', // domain publik HTTPS; callback tidak bisa menuju localhost
];
