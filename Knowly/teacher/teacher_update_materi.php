<?php
require_once '../config/db.php';
include '../includes/header_teacher.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses Ditolak! Halaman ini hanya khusus untuk Admin/Dosen.'); window.location='../student/student_list_class.php';</script>";
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id == 0) {
    echo "<script>alert('ID materi tidak valid!'); window.location='teacher_list_class.php';</script>";
    exit;
}

$sql = "SELECT * FROM materials WHERE id_material = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
}

if(!$data) {
    echo "<script>alert('Data materi tidak ditemukan!'); window.location='teacher_list_class.php';</script>";
    exit;
}
?>

<style>
    /* Style form edit */
    .form-container { 
        max-width: 700px; 
        margin: 40px auto 50px auto; 
        background: #ffffff; 
        border: 1px solid #E8EAED; 
        border-radius: 12px; 
        padding: 30px; 
        position: relative;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .btn-close-x { 
        position: absolute; 
        top: 25px; 
        right: 30px; 
        text-decoration: none; 
        color: #5f6368; 
        font-size: 22px; 
        font-weight: bold;
        cursor: pointer;
    }
    .btn-close-x:hover {
        color: #333;
    }
    .form-header {
        margin-bottom: 30px;
        border-bottom: 2px solid #1A73E8;
        padding-bottom: 15px;
    }
    .form-container h2 { 
        font-size: 24px; 
        color: #1A73E8; 
        margin: 0;
    }
    .form-header p {
        font-size: 14px;
        color: #5f6368;
        margin: 8px 0 0 0;
    }
    .form-group { 
        margin-bottom: 20px; 
    }
    .form-group label { 
        display: block; 
        font-size: 14px; 
        font-weight: 600; 
        margin-bottom: 8px;
        color: #333;
    }
    .form-group input[type="text"], 
    .form-group input[type="file"], 
    .form-group textarea, 
    .form-group select { 
        width: 100%; 
        padding: 12px; 
        border: 1px solid #E8EAED; 
        border-radius: 8px; 
        font-size: 14px; 
        outline: none;
        font-family: 'Poppins', sans-serif;
    }
    .form-group input[type="file"] {
        padding: 8px;
    }
    .form-group input:focus, 
    .form-group textarea:focus { 
        border-color: #1A73E8;
        box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
    }
    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }
    .footer-buttons { 
        display: flex; 
        justify-content: flex-end; 
        gap: 12px; 
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #E8EAED;
    }
    .btn-submit { 
        background-color: #1A73E8; 
        color: white; 
        border: none; 
        padding: 12px 28px; 
        border-radius: 8px; 
        cursor: pointer; 
        font-weight: 600; 
        font-size: 14px;
        transition: 0.2s;
    }
    .btn-submit:hover {
        background-color: #1666d4;
        box-shadow: 0 2px 8px rgba(26, 115, 232, 0.3);
    }
    .btn-batal { 
        background-color: #E8EAED; 
        color: #333; 
        text-decoration: none; 
        padding: 12px 28px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 14px; 
        text-align: center;
        transition: 0.2s;
        border: none;
        cursor: pointer;
    }
    .btn-batal:hover {
        background-color: #dadce0;
    }
    .back-link {
        display: inline-block;
        margin-bottom: 20px;
        color: #1A73E8;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
    }
    .back-link:hover {
        text-decoration: underline;
    }
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-top: 25px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #E8EAED;
    }
    .file-info {
        font-size: 12px;
        color: #5f6368;
        margin-top: 8px;
        padding: 10px;
        background: #F8FAFC;
        border-radius: 6px;
        line-height: 1.6;
    }
    .file-info-youtube {
        font-size: 12px;
        margin-top: 8px;
        padding: 10px;
        background: #FEF2F2;
        border-radius: 6px;
        color: #991B1B;
        line-height: 1.6;
    }
</style>

<div style="max-width:700px; margin: 40px auto 0 auto; padding:0 20px;">
    <a href="teacher_list_materi.php?id=<?php echo $data['id_class']; ?>" class="back-link">← Kembali ke Daftar Materi</a>
</div>

<div class="form-container">
    <a onclick="history.back()" class="btn-close-x" style="cursor: pointer;">✕</a>

    <div class="form-header">
        <h2>✏️ Update Data Materi</h2>
        <p>ID Materi: <strong>#<?php echo $data['id_material']; ?></strong></p>
    </div>

    <?php
    $file_name   = $data['file_name'] ?? '';
    $youtube_url = $data['youtube_url'] ?? '';
    ?>

    <form action="proses_materi.php?aksi=update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_materi" value="<?php echo $data['id_material']; ?>">
        <input type="hidden" name="kode_modul" value="<?php echo $data['id_class']; ?>">

        <div class="form-group">
            <label for="judul">📝 Nama Materi / Judul <span style="color: #EA4335;">*</span></label>
            <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($data['tittle_material']); ?>" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">📄 Deskripsi (Opsional)</label>
            <textarea id="deskripsi" name="deskripsi" placeholder="Jelaskan singkat tentang materi ini..."><?php echo htmlspecialchars($data['deskripsi'] ?? ''); ?></textarea>
        </div>

        <div class="section-title">📁 Berkas Dokumen</div>

        <div class="form-group" style="background: #F8FAFC; padding: 15px; border-radius: 8px; border: 1px solid #E2E8F0;">
            <label for="file_materi">🔄 Ganti Berkas (Kosongkan jika tidak diubah)</label>
            <input type="file" id="file_materi" name="file_materi" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
            <div class="file-info">
                ✓ Format: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX<br>
                ✓ Ukuran maksimal: 50 MB<br>
                <?php if(!empty($file_name)): ?>
                    📄 <strong>File saat ini:</strong> <?php echo htmlspecialchars($file_name); ?><br>
                    💡 Pilih file baru untuk menggantinya, atau biarkan kosong jika tidak ingin diubah.
                <?php else: ?>
                    ℹ️ Belum ada file yang diupload
                <?php endif; ?>
            </div>
        </div>

        <div class="section-title">📹 Video YouTube</div>

        <div class="form-group" style="background: #FEF2F2; padding: 15px; border-radius: 8px; border: 1px solid #FECACA;">
            <label for="youtube_url">Tautan YouTube (Kosongkan untuk menghapus)</label>
            <input type="text" id="youtube_url" name="youtube_url" value="<?php echo htmlspecialchars($youtube_url); ?>" placeholder="https://youtube.com/watch?v=... atau https://youtu.be/...">
            <div class="file-info-youtube">
                ✓ Format: https://youtube.com/watch?v=... atau https://youtu.be/...<br>
                <?php if(!empty($youtube_url)): ?>
                    ▶️ <strong>Link saat ini:</strong><br>
                    <a href="<?php echo htmlspecialchars($youtube_url); ?>" target="_blank" style="color: #DC2626; text-decoration: underline; font-weight: 600;">➜ Buka Video</a><br>
                    💡 Edit link untuk menggantinya, atau kosongkan untuk menghapus.
                <?php else: ?>
                    ℹ️ Belum ada video YouTube yang diupload
                <?php endif; ?>
            </div>
        </div>

        <div class="footer-buttons">
            <button type="button" class="btn-batal" onclick="history.back()">Batal</button>
            <button type="submit" class="btn-submit">✓ Simpan Perubahan</button>
        </div>
    </form>
</div>

</body>
</html>