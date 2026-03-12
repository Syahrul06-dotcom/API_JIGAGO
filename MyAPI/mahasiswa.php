<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$result = [];

// ===================== GET =====================
if ($method === 'GET') {
    if (isset($_GET['id_mhs'])) {
        $id = intval($_GET['id_mhs']);
        $sql = "SELECT m.*, j.nama_jurusan FROM mahasiswa m
                LEFT JOIN jurusan j ON m.id_jurusan = j.id_jurusan
                WHERE m.id_mhs = $id";
        $query = $conn->query($sql);
        if ($query && $query->num_rows > 0) {
            $result['status'] = ["code" => 200, "description" => "Data ditemukan"];
            $result['result'] = $query->fetch_assoc();
        } else {
            http_response_code(404);
            $result['status'] = ["code" => 404, "description" => "Data tidak ditemukan"];
        }
    } else {
        $sql = "SELECT m.*, j.nama_jurusan FROM mahasiswa m
                LEFT JOIN jurusan j ON m.id_jurusan = j.id_jurusan
                ORDER BY m.id_mhs";
        $query = $conn->query($sql);
        $data = [];
        while ($row = $query->fetch_assoc()) {
            $data[] = $row;
        }
        $result['status'] = ["code" => 200, "description" => "Menampilkan semua data mahasiswa"];
        $result['result'] = $data;
    }

// ===================== POST =====================
} elseif ($method === 'POST') {
    $body = json_decode(file_get_contents("php://input"), true);
    if (!$body) $body = $_POST;

    $nim           = $conn->real_escape_string($body['nim'] ?? '');
    $nama_mhs      = $conn->real_escape_string($body['nama_mhs'] ?? '');
    $alamat        = $conn->real_escape_string($body['alamat'] ?? '');
    $umur          = intval($body['umur'] ?? 0);
    $jenis_kelamin = $conn->real_escape_string($body['jenis_kelamin'] ?? '');
    $id_jurusan    = intval($body['id_jurusan'] ?? 0);

    if (!$nim || !$nama_mhs || !$alamat) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "Field nim, nama_mhs, dan alamat wajib diisi"];
    } else {
        $sql = "INSERT INTO mahasiswa (nim, nama_mhs, alamat, umur, jenis_kelamin, id_jurusan)
                VALUES ('$nim','$nama_mhs','$alamat',$umur,'$jenis_kelamin',$id_jurusan)";
        if ($conn->query($sql)) {
            $result['status'] = ["code" => 200, "description" => "1 data mahasiswa berhasil ditambahkan"];
            $result['result'] = [
                "id_mhs"        => $conn->insert_id,
                "nim"           => $nim,
                "nama_mhs"      => $nama_mhs,
                "alamat"        => $alamat,
                "umur"          => $umur,
                "jenis_kelamin" => $jenis_kelamin,
                "id_jurusan"    => $id_jurusan
            ];
        } else {
            http_response_code(500);
            $result['status'] = ["code" => 500, "description" => "Gagal menambah data: " . $conn->error];
        }
    }

// ===================== PUT =====================
} elseif ($method === 'PUT') {
    $body = json_decode(file_get_contents("php://input"), true);
    $id   = intval($_GET['id_mhs'] ?? 0);

    if (!$id) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "Parameter id_mhs diperlukan di URL"];
    } else {
        $nim           = $conn->real_escape_string($body['nim'] ?? '');
        $nama_mhs      = $conn->real_escape_string($body['nama_mhs'] ?? '');
        $alamat        = $conn->real_escape_string($body['alamat'] ?? '');
        $umur          = intval($body['umur'] ?? 0);
        $jenis_kelamin = $conn->real_escape_string($body['jenis_kelamin'] ?? '');
        $id_jurusan    = intval($body['id_jurusan'] ?? 0);

        $sql = "UPDATE mahasiswa SET
                    nim='$nim', nama_mhs='$nama_mhs', alamat='$alamat',
                    umur=$umur, jenis_kelamin='$jenis_kelamin', id_jurusan=$id_jurusan
                WHERE id_mhs=$id";
        if ($conn->query($sql)) {
            $result['status'] = ["code" => 200, "description" => "Data mahasiswa id=$id berhasil diupdate"];
            $result['result'] = ["id_mhs" => $id] + $body;
        } else {
            http_response_code(500);
            $result['status'] = ["code" => 500, "description" => "Gagal update: " . $conn->error];
        }
    }

// ===================== DELETE =====================
} elseif ($method === 'DELETE') {
    $id = intval($_GET['id_mhs'] ?? 0);
    if (!$id) {
        http_response_code(400);
        $result['status'] = ["code" => 400, "description" => "Parameter id_mhs diperlukan di URL"];
    } else {
        $sql = "DELETE FROM mahasiswa WHERE id_mhs=$id";
        if ($conn->query($sql)) {
            if ($conn->affected_rows > 0) {
                $result['status'] = ["code" => 200, "description" => "Data mahasiswa id=$id berhasil dihapus"];
            } else {
                http_response_code(404);
                $result['status'] = ["code" => 404, "description" => "Data tidak ditemukan"];
            }
        } else {
            http_response_code(500);
            $result['status'] = ["code" => 500, "description" => "Gagal hapus: " . $conn->error];
        }
    }
} else {
    http_response_code(405);
    $result['status'] = ["code" => 405, "description" => "Method tidak diizinkan"];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$conn->close();
?>