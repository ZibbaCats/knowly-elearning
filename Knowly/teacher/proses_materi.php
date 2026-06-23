<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

// Directory untuk upload
$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Fungsi validasi file
function validateFile($file) {
    $allowed_types = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx');
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $max_size = 50 * 1024 * 1024; // 50 MB

    if (!in_array($file_ext, $allowed_types)) {
        return array('success' => false, 'message' => 'Tipe file tidak didukung!');
    }

    if ($file['size'] > $max_size) {
        return array('success' => false, 'message' => 'Ukuran file terlalu besar! Maksimal 50 MB.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array('success' => false, 'message' => 'Terjadi kesalahan saat upload file!');
    }

    return array('success' => true);
}

// ============ TAMBAH MATERI ============
if ($aksi == 'tambah') {
    $judul = htmlspecialchars($_POST['judul'] ?? '');
    $deskripsi = htmlspecialchars($_POST['deskripsi'] ?? '');
    $id_class = intval($_POST['id_class'] ?? 0);
    $file_name = '';
    $youtube_url = '';

    if (empty($judul) || $id_class == 0) {
        $_SESSION['error'] = 'Judul dan kelas harus diisi!';
        header("Location: admin_upload.php?modul=$id_class");
        exit;
    }

    // Proses upload file jika ada
    if (!empty($_FILES['file_materi']['name'])) {
        $validation = validateFile($_FILES['file_materi']);
        if (!$validation['success']) {
            $_SESSION['error'] = $validation['message'];
            header("Location: admin_upload.php?modul=$id_class");
            exit;
        }

        $file_ext = pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION);
        $file_name = 'materi_' . time() . '_' . uniqid() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['file_materi']['tmp_name'], $file_path)) {
            $_SESSION['error'] = 'Gagal upload file!';
            header("Location: admin_upload.php?modul=$id_class");
            exit;
        }
    }

    if (!empty($_POST['youtube_url'])) {
        $youtube_url = htmlspecialchars($_POST['youtube_url']);
    }

    // Minimal salah satu harus ada
    if (empty($file_name) && empty($youtube_url)) {
        $_SESSION['error'] = 'Minimal salah satu dari file atau YouTube harus diisi!';
        header("Location: admin_upload.php?modul=$id_class");
        exit;
    }

    // Insert ke database
    $sql = "INSERT INTO materials (id_class, tittle_material, deskripsi, file_name, youtube_url) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("issss", $id_class, $judul, $deskripsi, $file_name, $youtube_url);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Materi berhasil ditambahkan!';
            header("Location: teacher_list_materi.php?id=$id_class");
            exit;
        } else {
            if (!empty($file_name) && file_exists($file_path)) {
                unlink($file_path);
            }
            $_SESSION['error'] = 'Gagal menyimpan materi!';
            header("Location: admin_upload.php?modul=$id_class");
            exit;
        }
        $stmt->close();
    }
}

// ============ UPDATE MATERI ============
else if ($aksi == 'update') {
    $id_materi = intval($_POST['id_materi'] ?? 0);
    $judul = htmlspecialchars($_POST['judul'] ?? '');
    $id_class = intval($_POST['kode_modul'] ?? 0);

    if ($id_materi == 0 || empty($judul)) {
        $_SESSION['error'] = 'Data tidak valid!';
        header("Location: teacher_list_materi.php?id=$id_class");
        exit;
    }

    // Get data lama
    $sql_old = "SELECT file_name, youtube_url FROM materials WHERE id_material = ?";
    $stmt_old = $conn->prepare($sql_old);
    $stmt_old->bind_param("i", $id_materi);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    $old_data = $result_old->fetch_assoc();

    $file_name = $old_data['file_name'];
    $youtube_url = $old_data['youtube_url'];

    // Update file jika ada file baru
    if (!empty($_FILES['file_materi']['name'])) {
        $validation = validateFile($_FILES['file_materi']);
        if (!$validation['success']) {
            $_SESSION['error'] = $validation['message'];
            header("Location: teacher_list_materi.php?id=$id_class");
            exit;
        }

        $file_ext = pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION);
        $new_file_name = 'materi_' . time() . '_' . uniqid() . '.' . $file_ext;
        $file_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($_FILES['file_materi']['tmp_name'], $file_path)) {
            if (!empty($file_name) && file_exists($upload_dir . $file_name)) {
                unlink($upload_dir . $file_name);
            }
            $file_name = $new_file_name;
        } else {
            $_SESSION['error'] = 'Gagal upload file baru!';
            header("Location: teacher_list_materi.php?id=$id_class");
            exit;
        }
    }

    // Update YouTube URL jika ada
    if (!empty($_POST['youtube_url'])) {
        $youtube_url = htmlspecialchars($_POST['youtube_url']);
    } else {
        $youtube_url = '';
    }

    // Update database
    $sql = "UPDATE materials SET tittle_material = ?, file_name = ?, youtube_url = ? WHERE id_material = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssi", $judul, $file_name, $youtube_url, $id_materi);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Materi berhasil diupdate!';
            header("Location: teacher_list_materi.php?id=$id_class");
            exit;
        } else {
            $_SESSION['error'] = 'Gagal mengupdate materi!';
            header("Location: teacher_list_materi.php?id=$id_class");
            exit;
        }
        $stmt->close();
    }
}

// ============ DELETE MATERI ============
else if ($aksi == 'delete') {
    $id_materi = intval($_GET['id'] ?? 0);
    $id_class = intval($_GET['modul'] ?? 0);

    if ($id_materi == 0) {
        $_SESSION['error'] = 'ID materi tidak valid!';
        header("Location: teacher_list_materi.php?id=$id_class");
        exit;
    }

    // Get file name
    $sql = "SELECT file_name FROM materials WHERE id_material = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_materi);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // Hapus file dari server
    if (!empty($data['file_name']) && file_exists($upload_dir . $data['file_name'])) {
        unlink($upload_dir . $data['file_name']);
    }

    // Hapus dari database
    $sql_delete = "DELETE FROM materials WHERE id_material = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id_materi);

    if ($stmt_delete->execute()) {
        $_SESSION['success'] = 'Materi berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus materi!';
    }

    header("Location: teacher_list_materi.php?id=$id_class");
    exit;
}

else {
    header("Location: teacher_list_class.php");
    exit;
}
?>