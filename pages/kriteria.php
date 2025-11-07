<?php
include '../includes/header.php';

$sukses = '';
$error = '';

// ========================
// TAMBAH KRITERIA
// ========================
if (isset($_POST['tambah_kriteria'])) {
    $nama_kriteria = clean($_POST['nama_kriteria']);
    $bobot_input = (float) clean($_POST['bobot']);
    $bobot = $bobot_input / 100;

    $total_bobot = (float) mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(bobot),0) AS total FROM kriteria"))['total'];

    if (($total_bobot + $bobot) > 1.0) {
        $error = "Total bobot tidak boleh melebihi 100% (1.0)";
    } else {
        $query = "INSERT INTO kriteria (nama_kriteria, bobot) VALUES ('$nama_kriteria', '$bobot')";
        if (mysqli_query($conn, $query)) {
            $sukses = "Kriteria berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan kriteria: " . mysqli_error($conn);
        }
    }
}

// ========================
// EDIT KRITERIA
// ========================
if (isset($_POST['edit_kriteria'])) {
    $id = clean($_POST['id']);
    $nama_kriteria = clean($_POST['nama_kriteria']);
    $bobot_input = (float) clean($_POST['bobot']);
    $bobot = $bobot_input / 100;

    $total_bobot_lain = (float) mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT IFNULL(SUM(bobot),0) AS total FROM kriteria WHERE id != '$id'
    "))['total'];

    if (($total_bobot_lain + $bobot) > 1.0) {
        $error = "Total bobot tidak boleh melebihi 100% (1.0)";
    } else {
        $query = "UPDATE kriteria SET nama_kriteria='$nama_kriteria', bobot='$bobot' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            $sukses = "Kriteria berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui kriteria: " . mysqli_error($conn);
        }
    }
}

// ========================
// TAMBAH SUBKRITERIA
// ========================
if (isset($_POST['tambah_sub'])) {
    $kriteria_id = clean($_POST['kriteria_id']);
    $nama_sub = clean($_POST['nama_sub']);
    $tipe = clean($_POST['tipe']);
    $target = (int) clean($_POST['target']);

    if ($target < 1 || $target > 5) {
        $error = "Nilai target harus antara 1 - 5!";
    } else {
        if (mysqli_query($conn, "INSERT INTO subkriteria (kriteria_id, nama_subkriteria, tipe, target) VALUES ('$kriteria_id', '$nama_sub', '$tipe', '$target')")) {
            $sukses = "Subkriteria berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan subkriteria: " . mysqli_error($conn);
        }
    }
}

// ========================
// EDIT SUBKRITERIA
// ========================
if (isset($_POST['edit_sub'])) {
    $id = clean($_POST['id']);
    $nama_sub = clean($_POST['nama_sub']);
    $tipe = clean($_POST['tipe']);
    $target = (int) clean($_POST['target']);

    if ($target < 1 || $target > 5) {
        $error = "Target harus antara 1–5!";
    } else {
        if (mysqli_query($conn, "UPDATE subkriteria SET nama_subkriteria='$nama_sub', tipe='$tipe', target='$target' WHERE id='$id'")) {
            $sukses = "Subkriteria berhasil diperbarui!";
        } else {
            $error = "Gagal mengedit subkriteria: " . mysqli_error($conn);
        }
    }
}

// ========================
// HAPUS KRITERIA
// ========================
if (isset($_GET['hapus'])) {
    $id = clean($_GET['hapus']);
    // Hapus subkriteria terkait dulu
    mysqli_query($conn, "DELETE FROM subkriteria WHERE kriteria_id='$id'");
    // Hapus kriteria
    mysqli_query($conn, "DELETE FROM kriteria WHERE id='$id'");
    $sukses = "Kriteria beserta subkriteria berhasil dihapus!";
}

// ========================
// HAPUS SUBKRITERIA
// ========================
if (isset($_GET['hapus_sub'])) {
    $id = clean($_GET['hapus_sub']);
    mysqli_query($conn, "DELETE FROM subkriteria WHERE id='$id'");
    // Hapus nilai terkait
    mysqli_query($conn, "DELETE FROM nilai WHERE subkriteria_id='$id'");
    mysqli_query($conn, "DELETE FROM gap_detail WHERE kriteria_id='$id'");
    $sukses = "Subkriteria berhasil dihapus!";
}

// Ambil data untuk edit subkriteria
$edit_sub_data = null;
if (isset($_GET['edit_sub'])) {
    $id = clean($_GET['edit_sub']);
    $edit_sub_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM subkriteria WHERE id='$id'"));
}

// ========================
// AMBIL DATA
// ========================
$kriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");
$total_bobot = (float) mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(bobot),0) AS total FROM kriteria"))['total'];
$sisa_bobot = 1 - $total_bobot;

// Untuk dropdown edit kriteria
$edit_kriteria_id = isset($_GET['edit']) ? clean($_GET['edit']) : '';
$edit_kriteria_data = null;
if ($edit_kriteria_id) {
    $edit_kriteria_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kriteria WHERE id='$edit_kriteria_id'"));
}
?>

<!-- Form Tambah/Edit Kriteria -->
<div class="card">
    <div class="card-header">
        <h2><?= $edit_kriteria_data ? 'Edit' : 'Tambah' ?> Kriteria</h2>
    </div>

    <?php if ($sukses): ?>
        <div class="alert alert-success"><?= $sukses ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
        Total Bobot Saat Ini: <strong><?= formatAngka($total_bobot * 100) ?>%</strong> | 
        Sisa Bobot: <strong><?= formatAngka($sisa_bobot * 100) ?>%</strong>
    </div>

    <form method="POST">
        <?php if ($edit_kriteria_data): ?>
            <input type="hidden" name="id" value="<?= $edit_kriteria_data['id'] ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label>Nama Kriteria *</label>
            <input type="text" name="nama_kriteria" value="<?= $edit_kriteria_data['nama_kriteria'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Bobot (%) *</label>
            <input type="number" name="bobot" step="0.01" min="0" max="100" 
                   value="<?= $edit_kriteria_data ? ($edit_kriteria_data['bobot'] * 100) : '' ?>" required>
            <small style="color: #666;">Masukkan nilai antara 0–100 (contoh: 20 untuk 20%)</small>
        </div>
        
        <button type="submit" name="<?= $edit_kriteria_data ? 'edit_kriteria' : 'tambah_kriteria' ?>" class="btn btn-primary">
            <?= $edit_kriteria_data ? 'Update' : 'Simpan' ?>
        </button>
        
        <?php if ($edit_kriteria_data): ?>
            <a href="kriteria.php" class="btn btn-secondary">Batal</a>
        <?php endif; ?>
    </form>
</div>

<!-- Daftar Kriteria & Subkriteria -->
<div class="card">
    <div class="card-header">
        <h2>Data Kriteria & Subkriteria</h2>
    </div>

    <?php
    mysqli_data_seek($kriteria, 0);
    $no = 1;
    while ($row = mysqli_fetch_assoc($kriteria)):
        $subs = mysqli_query($conn, "SELECT * FROM subkriteria WHERE kriteria_id='{$row['id']}' ORDER BY id ASC");
        $jumlah_sub = mysqli_num_rows($subs);
    ?>
    
    <!-- Kriteria Box -->
    <div style="border: 2px solid #667eea; border-radius: 10px; padding: 20px; margin-bottom: 20px; background: #f8f9fa;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
            <div>
                <h3 style="margin: 0; color: #667eea;">
                    <?= $no++ ?>. <?= htmlspecialchars($row['nama_kriteria']) ?>
                </h3>
                <p style="margin: 5px 0 0 0; color: #666;">
                    Bobot: <strong><?= formatAngka($row['bobot'] * 100) ?>%</strong> | 
                    Subkriteria: <strong><?= $jumlah_sub ?></strong>
                </p>
            </div>
            <div>
                <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit Kriteria</a>
                <a href="javascript:void(0)" 
                   onclick="confirmDelete('?hapus=<?= $row['id'] ?>', '<?= htmlspecialchars($row['nama_kriteria']) ?> beserta semua subkriterianya')" 
                   class="btn btn-danger btn-sm">Hapus</a>
            </div>
        </div>

        <!-- Tabel Subkriteria -->
        <table style="margin-bottom: 15px;">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Nama Subkriteria</th>
                    <th width="15%">Tipe</th>
                    <th width="15%">Target</th>
                    <th width="25%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($jumlah_sub > 0): ?>
                    <?php 
                    $sub_no = 1;
                    while ($sub = mysqli_fetch_assoc($subs)): 
                    ?>
                    <tr>
                        <td><?= $sub_no++ ?></td>
                        <td><?= htmlspecialchars($sub['nama_subkriteria']) ?></td>
                        <td>
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; background: <?= $sub['tipe'] == 'Core' ? '#dbeafe' : '#fef3c7' ?>; color: <?= $sub['tipe'] == 'Core' ? '#1e40af' : '#92400e' ?>;">
                                <?= $sub['tipe'] ?>
                            </span>
                        </td>
                        <td><?= $sub['target'] ?></td>
                        <td>
                            <a href="?edit_sub=<?= $sub['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="javascript:void(0)" 
                               onclick="confirmDelete('?hapus_sub=<?= $sub['id'] ?>', '<?= htmlspecialchars($sub['nama_subkriteria']) ?>')" 
                               class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #999;">Belum ada subkriteria</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Form Tambah/Edit Subkriteria -->
        <div style="background: white; padding: 15px; border-radius: 5px; border: 1px dashed #667eea;">
            <h4 style="margin-bottom: 10px; color: #667eea;">
                <?= ($edit_sub_data && $edit_sub_data['kriteria_id'] == $row['id']) ? '✏️ Edit' : '➕ Tambah' ?> Subkriteria
            </h4>
            
            <form method="POST" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: end;">
                <?php if ($edit_sub_data && $edit_sub_data['kriteria_id'] == $row['id']): ?>
                    <input type="hidden" name="id" value="<?= $edit_sub_data['id'] ?>">
                <?php else: ?>
                    <input type="hidden" name="kriteria_id" value="<?= $row['id'] ?>">
                <?php endif; ?>
                
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #666;">Nama Subkriteria *</label>
                    <input type="text" name="nama_sub" 
                           value="<?= ($edit_sub_data && $edit_sub_data['kriteria_id'] == $row['id']) ? htmlspecialchars($edit_sub_data['nama_subkriteria']) : '' ?>" 
                           placeholder="Contoh: Kemampuan Analisis" 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;" 
                           required>
                </div>
                
                <div style="width: 150px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #666;">Tipe *</label>
                    <select name="tipe" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;" required>
                        <option value="">-- Pilih --</option>
                        <option value="Core" <?= ($edit_sub_data && $edit_sub_data['kriteria_id'] == $row['id'] && $edit_sub_data['tipe'] == 'Core') ? 'selected' : '' ?>>Core</option>
                        <option value="Secondary" <?= ($edit_sub_data && $edit_sub_data['kriteria_id'] == $row['id'] && $edit_sub_data['tipe'] == 'Secondary') ? 'selected' : '' ?>>Secondary</option>
                    </select>
                </div>
                
                <div style="width: 100px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #666;">Target (1-5) *</label>
                    <input type="number" name="target" min="1" max="5" 
                           value="<?= ($edit_sub_data && $edit_sub_data['kriteria_id'] == $row['id']) ? $edit_sub_data['target'] : '' ?>" 
                           placeholder="3" 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;" 
                           required>
                </div>
                
                <button type="submit" name="<?= ($edit_sub_data && $edit_sub_data['kriteria_id'] == $row['id']) ? 'edit_sub' : 'tambah_sub' ?>" 
                        class="btn btn-success btn-sm">
                    <?= ($edit_sub_data && $edit_sub_data['kriteria_id'] == $row['id']) ? 'Update' : 'Tambah' ?>
                </button>
                
                <?php if ($edit_sub_data && $edit_sub_data['kriteria_id'] == $row['id']): ?>
                    <a href="kriteria.php" class="btn btn-secondary btn-sm">Batal</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <?php endwhile; ?>

    <?php if (mysqli_num_rows($kriteria) == 0): ?>
        <div style="text-align: center; padding: 40px; color: #999;">
            <p style="font-size: 18px; margin-bottom: 10px;">📋 Belum ada data kriteria</p>
            <p>Silakan tambahkan kriteria terlebih dahulu menggunakan form di atas</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>