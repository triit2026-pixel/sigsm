<?php
function url_base(array $config): string {
    $auto = !empty($config['base_url_auto']);
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $hostSolo = strtolower(preg_replace('/:\d+$/', '', $host));
    $locales = ['localhost', '127.0.0.1', '[::1]'];

    if ($auto && $host !== '' && !in_array($hostSolo, $locales, true)) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        $carpeta = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        return ($https ? 'https' : 'http') . '://' . $host . $carpeta;
    }
    return rtrim($config['base_url'], '/');
}