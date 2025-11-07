<?php
include '../includes/header.php';

$sukses = '';
$error = '';

// Tambah data
if (isset($_POST['tambah'])) {
    $nama = clean($_POST['nama']);
    $jabatan_id = clean($_POST['jabatan_id']);
    
    $query = "INSERT INTO karyawan (nama, jabatan_id) VALUES ('$nama', '$jabatan_id')";
    if (mysqli_query($conn, $query)) {
        $sukses = "Data karyawan berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan data!";
    }
}

// Edit data
if (isset($_POST['edit'])) {
    $id = clean($_POST['id']);
    $nama = clean($_POST['nama']);
    $jabatan_id = clean($_POST['jabatan_id']);
    
    $query = "UPDATE karyawan SET nama='$nama', jabatan_id='$jabatan_id' WHERE id='$id'";
    if (mysqli_query($conn, $query)) {
        $sukses = "Data karyawan berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate data!";
    }
}

// Hapus data
if (isset($_GET['hapus'])) {
    $id = clean($_GET['hapus']);
    
    // Hapus data terkait di tabel nilai dan hasil
    mysqli_query($conn, "DELETE FROM nilai WHERE karyawan_id='$id'");
    mysqli_query($conn, "DELETE FROM hasil WHERE karyawan_id='$id'");
    mysqli_query($conn, "DELETE FROM gap_detail WHERE karyawan_id='$id'");
    
    $query = "DELETE FROM karyawan WHERE id='$id'";
    if (mysqli_query($conn, $query)) {
        $sukses = "Data karyawan berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data!";
    }
}

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = clean($_GET['edit']);
    $query = "SELECT * FROM karyawan WHERE id='$id'";
    $result = mysqli_query($conn, $query);
    $edit_data = mysqli_fetch_assoc($result);
}

// Ambil semua data karyawan dengan join jabatan
$query = "SELECT k.*, j.nama_jabatan 
          FROM karyawan k 
          LEFT JOIN jabatan j ON k.jabatan_id = j.id 
          ORDER BY k.id DESC";
$karyawan = mysqli_query($conn, $query);

// Ambil data jabatan untuk dropdown
$jabatan = mysqli_query($conn, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
?>

<div class="card">
    <div class="card-header">
        <h2><?= $edit_data ? 'Edit' : 'Tambah' ?> Karyawan</h2>
    </div>
    
    <?php if ($sukses): ?>
        <div class="alert alert-success"><?= $sukses ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label>Nama Karyawan *</label>
            <input type="text" name="nama" value="<?= $edit_data['nama'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Jabatan Saat Ini *</label>
            <select name="jabatan_id" required>
                <option value="">-- Pilih Jabatan --</option>
                <?php 
                mysqli_data_seek($jabatan, 0);
                while ($row = mysqli_fetch_assoc($jabatan)): 
                ?>
                <option value="<?= $row['id'] ?>" <?= ($edit_data && $edit_data['jabatan_id'] == $row['id']) ? 'selected' : '' ?>>
                    <?= $row['nama_jabatan'] ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <button type="submit" name="<?= $edit_data ? 'edit' : 'tambah' ?>" class="btn btn-primary">
            <?= $edit_data ? 'Update' : 'Simpan' ?>
        </button>
        
        <?php if ($edit_data): ?>
            <a href="karyawan.php" class="btn btn-secondary">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Data Karyawan</h2>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Karyawan</th>
                <th>Jabatan Saat Ini</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($row = mysqli_fetch_assoc($karyawan)): 
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['nama_jabatan'] ?></td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="javascript:void(0)" onclick="confirmDelete('?hapus=<?= $row['id'] ?>', '<?= $row['nama'] ?>')" class="btn btn-danger btn-sm">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
            
            <?php if (mysqli_num_rows($karyawan) == 0): ?>
            <tr>
                <td colspan="4" class="text-center">Belum ada data karyawan</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>