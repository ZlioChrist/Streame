<?php
session_start();
require_once 'midtrans_config.php';
$snapToken = $_SESSION['snap_token'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran</title>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js " data-client-key="YOUR_CLIENT_KEY"></script>
</head>
<body class="bg-black text-white flex justify-center items-center min-h-screen">
    <button id="pay-button" class="bg-yellow-400 hover:bg-yellow-500 text-black px-6 py-3 rounded-lg">Bayar Sekarang</button>

    <script>
        document.getElementById('pay-button').onclick = function () {
            snap.pay('<?= $snapToken ?>', {
                onSuccess: function(result) {
                    console.log('Pembayaran berhasil:', result);
                    window.location.href = 'dashboard_vip.php';
                },
                onPending: function(result) {
                    alert('Pembayaran sedang diproses.');
                },
                onError: function(err) {
                    alert('Pembayaran gagal: ' + JSON.stringify(err));
                }
            });
        };
    </script>
</body>
</html>