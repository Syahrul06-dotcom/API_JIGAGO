<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'koneksi.php';
$method = $_SERVER['REQUEST_METHOD'];
$result = [];

if ($method === 'GET') {
    if (isset($_GET['id_jurusan'])) {
        $id = intval($_GET['id_jurusan']);
        $query = $conn->query("SELECT * FROM jurusan WHERE id_jurusan=$id");
        if ($query && $query->num_rows > 0) {
            $result['status'] = ["code" => 200, "description" => "Data ditemukan"];
            $result['result'] = $query->fetch_assoc();
        } else {
            http_response_code(404);
            $result['status'] = ["code" => 404, "description" => "Data tidak ditemukan"];
        }
    } else {
        $query = $conn->query("SELECT * FROM jurusan ORDER BY id_jurusan");
        $data = [];
        while ($row = $query->fetch_assoc()) $data[] = $row;
        $result['status'] = ["code" => 200, "description" => "Menampilkan semua data jurusan"];
        $result['result'] = $data;
    }

} elseif ($method === 'POST') {
    $body = json_decode(file_get_contents("php://input"), true);
    if (!$body) $body = $_POST;
    $nama = $conn->real_escape_string($body['nama_jurusan'] ?? '');
    if (!$nama) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "Field nama_jurusan wajib diisi"];
    } else {
        $conn->query("INSERT INTO jurusan (nama_jurusan) VALUES ('$nama')");
        $result['status'] = ["code" => 200, "description" => "Jurusan berhasil ditambahkan"];
        $result['result'] = ["id_jurusan" => $conn->insert_id, "nama_jurusan" => $nama];
    }

} elseif ($method === 'PUT') {
    $body = json_decode(file_get_contents("php://input"), true);
    $id   = intval($_GET['id_jurusan'] ?? 0);
    $nama = $conn->real_escape_string($body['nama_jurusan'] ?? '');
    if (!$id) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "id_jurusan diperlukan"];
    } else {
        $conn->query("UPDATE jurusan SET nama_jurusan='$nama' WHERE id_jurusan=$id");
        $result['status'] = ["code" => 200, "description" => "Jurusan id=$id berhasil diupdate"];
        $result['result'] = ["id_jurusan" => $id, "nama_jurusan" => $nama];
    }

} elseif ($method === 'DELETE') {
    $id = intval($_GET['id_jurusan'] ?? 0);
    if (!$id) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "id_jurusan diperlukan"];
    } else {
        $conn->query("DELETE FROM jurusan WHERE id_jurusan=$id");
        $result['status'] = ["code" => 200, "description" => "Jurusan id=$id berhasil dihapus"];
    }
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$conn->close();
?>