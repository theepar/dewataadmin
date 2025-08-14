<?php
// Jalankan perintah git pull
$output = [];
$return_var = 0;
exec('git pull origin main 2>&1', $output, $return_var);

// Tampilkan hasil eksekusi
echo "Pulling latest code from origin/main...\n";
echo implode("\n", $output);

$logFile = __DIR__ . '/cronjob.log';
file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] Cronjob dijalankan\n", FILE_APPEND);

if ($return_var === 0) {
    echo "\nGit pull sukses!\n";
} else {
    echo "\nGit pull gagal!\n";
}
