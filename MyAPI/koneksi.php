<?php
$servername = "sql301.infinityfree.com";
$username   = "if0_41369382";
$password   = "SYAHRUL0606";
$dbname     = "if0_41369382_mahasiswa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => [
            "code"        => 500,
            "description" => "Gagal koneksi: " . $conn->connect_error
        ]
    ]);
    exit;
}
?>