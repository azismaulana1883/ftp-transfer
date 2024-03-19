<?php
// Informasi SFTP
$sftpHost = "eadaptor-sftp-trn.logistical.one";
$sftpUsername = "sftp-panbrothers";
$sftpPassword = "sXvozm3VjAMY36o8kgj4m";
$sftpPort = 22;
$sftpRemoteDirectory = '/inbound/';

// Lokasi direktori lokal yang akan dicopy
$localDirectory = 'C:/test_ftp/';

// Lokasi direktori lokal untuk memindahkan file setelah diunggah
$moveDirectory = 'C:/test_ftp_pindah/';

// Set batas waktu eksekusi menjadi 300 detik (5 menit)
set_time_limit(30);

// Loop melalui file di direktori lokal
foreach (glob($localDirectory . '*') as $localFile) {
    // Pindahkan file ke SFTP
    uploadFileToSFTP($localFile, $sftpHost, $sftpUsername, $sftpPassword, $sftpRemoteDirectory);

    // Pindahkan file dari direktori lokal ke direktori tujuan
    moveFileLocally($localFile, $moveDirectory);
}

echo 'Directory contents copied to SFTP and moved locally successfully!';

// Fungsi untuk mengunggah file ke SFTP
function uploadFileToSFTP($localFile, $sftpHost, $sftpUsername, $sftpPassword, $sftpRemoteDirectory) {
    // URL SFTP
    $sftpUrl = "sftp://$sftpUsername:$sftpPassword@$sftpHost$sftpRemoteDirectory" . basename($localFile);

    // Inisialisasi cURL
    $curl = curl_init();

    // Set opsi cURL
    curl_setopt($curl, CURLOPT_URL, $sftpUrl);
    curl_setopt($curl, CURLOPT_UPLOAD, 1);
    curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
    curl_setopt($curl, CURLOPT_INFILE, fopen($localFile, 'r'));
    curl_setopt($curl, CURLOPT_INFILESIZE, filesize($localFile));

    // Eksekusi cURL
    $result = curl_exec($curl);

    // Periksa hasil
    if ($result) {
        echo "File $localFile berhasil diunggah ke SFTP!\n";
    } else {
        echo "Gagal mengunggah file $localFile ke SFTP. Error: " . curl_error($curl) . "\n";
    }

    // Tutup cURL
    curl_close($curl);
}

// Fungsi untuk memindahkan file secara lokal
function moveFileLocally($sourceFile, $destinationDir) {
    $newLocation = $destinationDir . basename($sourceFile);

    // Tunggu sebentar sebelum mencoba memindahkan file
    sleep(1);

    // Coba memindahkan file dengan menangani kesalahan
    if (@rename($sourceFile, $newLocation)) {
        echo "File $sourceFile moved to $newLocation.\n";
    } else {
        echo "Failed to move file $sourceFile to $newLocation.\n";
        echo "Error: " . error_get_last()['message'] . "\n";
    }
}
?>
