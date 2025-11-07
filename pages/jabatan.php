<?php
include '../includes/header.php';

$sukses = '';
$error = '';

// Tambah data
if (isset($_POST['tambah'])) {
    $nama_jabatan = clean($_POST['nama_jabatan']);
    $deskripsi = clean($_POST['deskripsi']);
    
    $query = "INSERT INTO jabatan (nama_jabatan, deskripsi) VALUES ('$nama_jabatan', '$deskripsi')";
    if (mysqli_query($conn, $query)) {
        $sukses = "Data jabatan berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan data!";
    }
}

// Edit data
if (isset($_POST['edit'])) {
    $id = clean($_POST['id']);
    $nama_jabatan = clean($_POST['nama_jabatan']);
    $deskripsi = clean($_POST['deskripsi']);
    
    $query = "UPDATE jabatan SET nama_jabatan='$nama_jabatan', deskripsi='$deskripsi' WHERE id='$id'";
    if (mysqli_query($conn, $query)) {
        $sukses = "Data jabatan berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate data!";
    }
}

// Hapus data
if (isset($_GET['hapus'])) {
    $id = clean($_GET['hapus']);
    
    // Cek apakah jabatan digunakan di karyawan
    $cek = mysqli_query($conn, "SELECT COUNT(*) as total FROM karyawan WHERE jabatan_id='$id'");
    $data_cek = mysqli_fetch_assoc($cek);
    
    if ($data_cek['total'] > 0) {
        $error = "Jabatan tidak bisa dihapus karena masih digunakan oleh karyawan!";
    } else {
        $query = "DELETE FROM jabatan WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            $sukses = "Data jabatan berhasil dihapus!";
        } else {
            $error = "Gagal menghapus data!";
        }
    }
}

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = clean($_GET['edit']);
    $query = "SELECT * FROM jabatan WHERE id='$id'";
    $result = mysqli_query($conn, $query);
    $edit_data = mysqli_fetch_assoc($result);
}

// Ambil semua data jabatan
$query = "SELECT * FROM jabatan ORDER BY id DESC";
$jabatan = mysqli_query($conn, $query);
?>

<div class="card">
    <div class="card-header">
        <h2><?= $edit_data ? 'Edit' : 'Tambah' ?> Jabatan</h2>
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
            <label>Nama Jabatan *</label>
            <input type="text" name="nama_jabatan" value="<?= $edit_data['nama_jabatan'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="3"><?= $edit_data['deskripsi'] ?? '' ?></textarea>
        </div>
        
        <button type="submit" name="<?= $edit_data ? 'edit' : 'tambah' ?>" class="btn btn-primary">
            <?= $edit_data ? 'Update' : 'Simpan' ?>
        </button>
        
        <?php if ($edit_data): ?>
            <a href="jabatan.php" class="btn btn-secondary">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Data Jabatan</h2>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Jabatan</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($row = mysqli_fetch_assoc($jabatan)): 
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['nama_jabatan'] ?></td>
                <td><?= $row['deskripsi'] ?></td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="javascript:void(0)" onclick="confirmDelete('?hapus=<?= $row['id'] ?>', '<?= $row['nama_jabatan'] ?>')" class="btn btn-danger btn-sm">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
            
            <?php if (mysqli_num_rows($jabatan) == 0): ?>
            <tr>
                <td colspan="4" class="text-center">Belum ada data jabatan</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>