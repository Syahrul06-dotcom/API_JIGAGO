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
    if (isset($_GET['id_mk'])) {
        $id = intval($_GET['id_mk']);
        $query = $conn->query("SELECT mk.*, j.nama_jurusan FROM mata_kuliah mk
                               LEFT JOIN jurusan j ON mk.id_jurusan=j.id_jurusan
                               WHERE id_mk=$id");
        if ($query && $query->num_rows > 0) {
            $result['status'] = ["code" => 200, "description" => "Data ditemukan"];
            $result['result'] = $query->fetch_assoc();
        } else {
            http_response_code(404);
            $result['status'] = ["code" => 404, "description" => "Data tidak ditemukan"];
        }
    } else {
        $query = $conn->query("SELECT mk.*, j.nama_jurusan FROM mata_kuliah mk
                               LEFT JOIN jurusan j ON mk.id_jurusan=j.id_jurusan
                               ORDER BY id_mk");
        $data = [];
        while ($row = $query->fetch_assoc()) $data[] = $row;
        $result['status'] = ["code" => 200, "description" => "Menampilkan semua mata kuliah"];
        $result['result'] = $data;
    }

} elseif ($method === 'POST') {
    $body = json_decode(file_get_contents("php://input"), true);
    if (!$body) $body = $_POST;
    $nama_mk    = $conn->real_escape_string($body['nama_mk'] ?? '');
    $sks        = intval($body['sks'] ?? 0);
    $id_jurusan = intval($body['id_jurusan'] ?? 0);
    if (!$nama_mk) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "nama_mk wajib diisi"];
    } else {
        $conn->query("INSERT INTO mata_kuliah (nama_mk, sks, id_jurusan) VALUES ('$nama_mk',$sks,$id_jurusan)");
        $result['status'] = ["code" => 200, "description" => "Mata kuliah berhasil ditambahkan"];
        $result['result'] = ["id_mk" => $conn->insert_id, "nama_mk" => $nama_mk, "sks" => $sks, "id_jurusan" => $id_jurusan];
    }

} elseif ($method === 'PUT') {
    $body       = json_decode(file_get_contents("php://input"), true);
    $id         = intval($_GET['id_mk'] ?? 0);
    $nama_mk    = $conn->real_escape_string($body['nama_mk'] ?? '');
    $sks        = intval($body['sks'] ?? 0);
    $id_jurusan = intval($body['id_jurusan'] ?? 0);
    if (!$id) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "id_mk diperlukan"];
    } else {
        $conn->query("UPDATE mata_kuliah SET nama_mk='$nama_mk', sks=$sks, id_jurusan=$id_jurusan WHERE id_mk=$id");
        $result['status'] = ["code" => 200, "description" => "Mata kuliah id=$id berhasil diupdate"];
        $result['result'] = ["id_mk" => $id] + $body;
    }

} elseif ($method === 'DELETE') {
    $id = intval($_GET['id_mk'] ?? 0);
    if (!$id) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "id_mk diperlukan"];
    } else {
        $conn->query("DELETE FROM mata_kuliah WHERE id_mk=$id");
        $result['status'] = ["code" => 200, "description" => "Mata kuliah id=$id berhasil dihapus"];
    }
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$conn->close();
?>