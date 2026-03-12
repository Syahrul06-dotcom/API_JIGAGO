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
    if (isset($_GET['id_nilai'])) {
        $id = intval($_GET['id_nilai']);
        $query = $conn->query("SELECT n.*, m.nama_mhs, mk.nama_mk FROM nilai n
                               LEFT JOIN mahasiswa m ON n.id_mhs=m.id_mhs
                               LEFT JOIN mata_kuliah mk ON n.id_mk=mk.id_mk
                               WHERE n.id_nilai=$id");
        if ($query && $query->num_rows > 0) {
            $result['status'] = ["code" => 200, "description" => "Data ditemukan"];
            $result['result'] = $query->fetch_assoc();
        } else {
            http_response_code(404);
            $result['status'] = ["code" => 404, "description" => "Data tidak ditemukan"];
        }
    } elseif (isset($_GET['id_mhs'])) {
        $id_mhs = intval($_GET['id_mhs']);
        $query  = $conn->query("SELECT n.*, mk.nama_mk, mk.sks FROM nilai n
                                LEFT JOIN mata_kuliah mk ON n.id_mk=mk.id_mk
                                WHERE n.id_mhs=$id_mhs");
        $data = [];
        while ($row = $query->fetch_assoc()) $data[] = $row;
        $result['status'] = ["code" => 200, "description" => "Nilai mahasiswa id=$id_mhs"];
        $result['result'] = $data;
    } else {
        $query = $conn->query("SELECT n.*, m.nama_mhs, mk.nama_mk FROM nilai n
                               LEFT JOIN mahasiswa m ON n.id_mhs=m.id_mhs
                               LEFT JOIN mata_kuliah mk ON n.id_mk=mk.id_mk
                               ORDER BY n.id_nilai");
        $data = [];
        while ($row = $query->fetch_assoc()) $data[] = $row;
        $result['status'] = ["code" => 200, "description" => "Menampilkan semua data nilai"];
        $result['result'] = $data;
    }

} elseif ($method === 'POST') {
    $body       = json_decode(file_get_contents("php://input"), true);
    if (!$body) $body = $_POST;
    $id_mhs     = intval($body['id_mhs'] ?? 0);
    $id_mk      = intval($body['id_mk'] ?? 0);
    $skor_nilai = intval($body['skor_nilai'] ?? 0);
    if (!$id_mhs || !$id_mk) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "id_mhs dan id_mk wajib diisi"];
    } else {
        $conn->query("INSERT INTO nilai (id_mhs, id_mk, skor_nilai) VALUES ($id_mhs,$id_mk,$skor_nilai)");
        $result['status'] = ["code" => 200, "description" => "Nilai berhasil ditambahkan"];
        $result['result'] = ["id_nilai" => $conn->insert_id, "id_mhs" => $id_mhs, "id_mk" => $id_mk, "skor_nilai" => $skor_nilai];
    }

} elseif ($method === 'PUT') {
    $body       = json_decode(file_get_contents("php://input"), true);
    $id         = intval($_GET['id_nilai'] ?? 0);
    $skor_nilai = intval($body['skor_nilai'] ?? 0);
    if (!$id) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "id_nilai diperlukan"];
    } else {
        $conn->query("UPDATE nilai SET skor_nilai=$skor_nilai WHERE id_nilai=$id");
        $result['status'] = ["code" => 200, "description" => "Nilai id=$id berhasil diupdate"];
        $result['result'] = ["id_nilai" => $id, "skor_nilai" => $skor_nilai];
    }

} elseif ($method === 'DELETE') {
    $id = intval($_GET['id_nilai'] ?? 0);
    if (!$id) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "id_nilai diperlukan"];
    } else {
        $conn->query("DELETE FROM nilai WHERE id_nilai=$id");
        $result['status'] = ["code" => 200, "description" => "Nilai id=$id berhasil dihapus"];
    }
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$conn->close();
?>