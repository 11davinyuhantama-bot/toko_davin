<?php
// Simpan kredensial asli di config/duitku.local.php (file tersebut tidak dilacak Git).
$localConfig = __DIR__ . '/duitku.local.php';
$local = is_file($localConfig) ? require $localConfig : [];
$environment = [];
foreach ([
    'merchant_code' => 'DUITKU_MERCHANT_CODE',
    'api_key' => 'DUITKU_API_KEY',
    'mode' => 'DUITKU_MODE',
    'base_url' => 'DUITKU_BASE_URL',
] as $key => $name) {
    $value = getenv($name);
    if ($value !== false && $value !== '') {
        $environment[$key] = $value;
    }
}

return array_replace([
    'merchant_code' => getenv('DUITKU_MERCHANT_CODE') ?: '',
    'api_key' => getenv('DUITKU_API_KEY') ?: '',
    'mode' => getenv('DUITKU_MODE') ?: 'sandbox',
    'base_url' => getenv('DUITKU_BASE_URL') ?: '',
], is_array($local) ? $local : [], $environment);
