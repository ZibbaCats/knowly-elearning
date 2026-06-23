<?php
require_once '../config/db.php';
include '../includes/header_teacher.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses Ditolak! Halaman ini hanya khusus untuk Admin/Dosen.'); window.location='../student/student_list_class.php';</script>";
    exit;
}

$id_class = isset($_GET['modul']) ? intval($_GET['modul']) : 0;

if ($id_class == 0) {
    echo "<script>alert('Kode modul tidak valid!'); window.location='teacher_list_class.php';</script>";
    exit;
}

// Get class info
$sql_class = "SELECT * FROM class WHERE id = ?";
$stmt_class = $conn->prepare($sql_class);
$stmt_class->bind_param("i", $id_class);
$stmt_class->execute();
$result_class = $stmt_class->get_result();
$class_data = $result_class->fetch_assoc();

if (!$class_data) {
    echo "<script>alert('Kelas tidak ditemukan!'); window.location='teacher_list_class.php';</script>";
    exit;
}
?>

<style>
    .form-container { 
        max-width: 700px; 
        margin: 40px auto 50px auto; 
        background: #ffffff; 
        border: 1px solid #E8EAED; 
        border-radius: 12px; 
        padding: 30px; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .form-header {
        margin-bottom: 30px;
        border-bottom: 2px solid #1A73E8;
        padding-bottom: 15px;
    }
    .form-header h2 { 
        font-size: 24px; 
        color: #1A73E8; 
        margin: 0;
    }
    .form-header p {
        font-size: 14px;
        color: #5f6368;
        margin: 5px 0 0 0;
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
    .form-group textarea { 
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
    .file-info {
        font-size: 12px;
        color: #5f6368;
        margin-top: 8px;
        padding: 10px;
        background: #F8FAFC;
        border-radius: 6px;
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
    .note {
        background: #FEF7E0;
        border-left: 4px solid #FBBC04;
        padding: 12px;
        border-radius: 4px;
        font-size: 12px;
        color: #9e6e00;
        margin-bottom: 20px;
    }
</style>

<div style="max-width:700px; margin: 40px auto 0 auto; padding:0 20px;">
    <a href="teacher_list_materi.php?id=<?php echo $id_class; ?>" class="back-link">← Kembali ke Daftar Materi</a>
</div>

<div class="form-container">
    <div class="form-header">
        <h2>📚 Tambah Materi Baru</h2>
        <p>Kelas: <strong><?php echo htmlspecialchars($class_data['name']); ?></strong></p>
    </div>

    <div class="note">
        💡 <strong>Tips:</strong> Anda dapat mengunggah file (PDF, DOC, PPT, dll) atau menyertakan tautan YouTube. Minimal salah satu harus diisi.
    </div>

    <form action="proses_materi.php?aksi=tambah" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_class" value="<?php echo $id_class; ?>">

        <div class="form-group">
            <label for="judul">📝 Nama Materi / Judul <span style="color: #EA4335;">*</span></label>
            <input type="text" id="judul" name="judul" placeholder="Contoh: Pengantar Web Development" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">📄 Deskripsi (Opsional)</label>
            <textarea id="deskripsi" name="deskripsi" placeholder="Jelaskan singkat tentang materi ini..."></textarea>
        </div>

        <div class="section-title">📁 Unggah Berkas</div>
        
        <div class="form-group">
            <label for="file_materi">Pilih File (PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX)</label>
            <input type="file" id="file_materi" name="file_materi" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
            <div class="file-info">
                ✓ Format yang didukung: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX<br>
                ✓ Ukuran maksimal: 50 MB<br>
                ℹ️ Biarkan kosong jika tidak ada file
            </div>
        </div>

        <div class="section-title">📹 Video YouTube</div>
        
        <div class="form-group">
            <label for="youtube_url">Link Video YouTube</label>
            <input type="text" id="youtube_url" name="youtube_url" placeholder="Contoh: https://youtube.com/watch?v=...">
            <div class="file-info">
                ✓ Masukkan URL YouTube lengkap<br>
                ℹ️ Biarkan kosong jika tidak ada video
            </div>
        </div>

        <div class="footer-buttons">
            <button type="button" class="btn-batal" onclick="history.back()">Batal</button>
            <button type="submit" class="btn-submit">✓ Tambah Materi</button>
        </div>
    </form>
</div>

</body>
</html>