<?php
include '../includes/header.php';

$sukses = '';
$error = '';

// Simpan penilaian
if (isset($_POST['simpan'])) {
    $karyawan_id = clean($_POST['karyawan_id']);
    
    // Hapus nilai lama
    mysqli_query($conn, "DELETE FROM nilai WHERE karyawan_id='$karyawan_id'");
    
    // Insert nilai baru
    foreach ($_POST['nilai'] as $subkriteria_id => $nilai) {
        $nilai = clean($nilai);
        $query = "INSERT INTO nilai (karyawan_id, subkriteria_id, nilai) 
                  VALUES ('$karyawan_id', '$subkriteria_id', '$nilai')";
        mysqli_query($conn, $query);
    }

    $sukses = "Penilaian berhasil disimpan!";
}

// Ambil data karyawan
$karyawan = mysqli_query($conn, "
    SELECT k.*, j.nama_jabatan 
    FROM karyawan k 
    LEFT JOIN jabatan j ON k.jabatan_id = j.id 
    ORDER BY k.nama ASC
");

// Ambil data subkriteria (urut sesuai input kriteria & subkriteria)
$subkriteria = mysqli_query($conn, "
    SELECT s.*, k.nama_kriteria 
    FROM subkriteria s
    JOIN kriteria k ON s.kriteria_id = k.id
    ORDER BY k.id ASC, s.id ASC
");

// Karyawan yang dipilih
$selected_karyawan = isset($_GET['karyawan_id']) ? clean($_GET['karyawan_id']) : '';

// Ambil nilai yang sudah ada
$nilai_tersimpan = [];
if ($selected_karyawan) {
    $result_nilai = mysqli_query($conn, "
        SELECT subkriteria_id, nilai 
        FROM nilai 
        WHERE karyawan_id='$selected_karyawan'
    ");
    while ($row = mysqli_fetch_assoc($result_nilai)) {
        $nilai_tersimpan[$row['subkriteria_id']] = $row['nilai'];
    }
}
?>

<div class="card">
    <div class="card-header">
        <h2>Input Penilaian Karyawan</h2>
    </div>
    
    <?php if ($sukses): ?>
        <div class="alert alert-success"><?= $sukses ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="GET">
        <div class="form-group">
            <label>Pilih Karyawan *</label>
            <select name="karyawan_id" onchange="this.form.submit()" required>
                <option value="">-- Pilih Karyawan --</option>
                <?php 
                mysqli_data_seek($karyawan, 0);
                while ($row = mysqli_fetch_assoc($karyawan)): 
                ?>
                <option value="<?= $row['id'] ?>" <?= $selected_karyawan == $row['id'] ? 'selected' : '' ?>>
                    <?= $row['nama'] ?> - <?= $row['nama_jabatan'] ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($selected_karyawan): ?>
    <?php
    // Ambil info karyawan
    $info_karyawan = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT k.*, j.nama_jabatan 
        FROM karyawan k 
        LEFT JOIN jabatan j ON k.jabatan_id = j.id 
        WHERE k.id='$selected_karyawan'
    "));
    ?>
    
    <div class="card">
        <div class="card-header">
            <h2>Penilaian untuk: <?= $info_karyawan['nama'] ?></h2>
        </div>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <strong>Jabatan:</strong> <?= $info_karyawan['nama_jabatan'] ?><br>
            <small style="color: #666;">Berikan nilai untuk setiap subkriteria dengan skala 1–5</small>
        </div>
        
        <form method="POST">
            <input type="hidden" name="karyawan_id" value="<?= $selected_karyawan ?>">
            
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kriteria</th>
                        <th>Subkriteria</th>
                        <th>Tipe</th>
                        <th>Target</th>
                        <th>Nilai (1–5) *</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $current_kriteria = '';
                    mysqli_data_seek($subkriteria, 0);
                    while ($row = mysqli_fetch_assoc($subkriteria)):
                        // Tambahkan pemisah jika ganti kriteria
                        if ($current_kriteria != $row['nama_kriteria']):
                            $current_kriteria = $row['nama_kriteria'];
                            echo "<tr style='background:#e5e7eb; font-weight:bold;'>
                                    <td colspan='6'>{$current_kriteria}</td>
                                  </tr>";
                        endif;
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row['nama_kriteria'] ?></td>
                        <td><?= $row['nama_subkriteria'] ?></td>
                        <td>
                            <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; background:<?= $row['tipe']=='Core' ? '#dbeafe' : '#fef3c7' ?>; color:<?= $row['tipe']=='Core' ? '#1e40af' : '#92400e' ?>;">
                                <?= $row['tipe'] ?>
                            </span>
                        </td>
                        <td><?= $row['target'] ?></td>
                        <td>
                            <select name="nilai[<?= $row['id'] ?>]" required style="width: 100px;">
                                <option value="">-</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= (isset($nilai_tersimpan[$row['id']]) && $nilai_tersimpan[$row['id']] == $i) ? 'selected' : '' ?>>
                                    <?= $i ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="mt-3">
                <button type="submit" name="simpan" class="btn btn-primary">Simpan Penilaian</button>
                <a href="penilaian.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Rekap Penilaian</h2>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Karyawan</th>
                <th>Jabatan</th>
                <th>Jumlah Subkriteria Dinilai</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            mysqli_data_seek($karyawan, 0);
            $total_sub = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM subkriteria"));
            
            while ($row = mysqli_fetch_assoc($karyawan)): 
                $jumlah_nilai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM nilai WHERE karyawan_id='{$row['id']}'"))['total'];
                $status = ($jumlah_nilai == $total_sub) ? 'Lengkap' : 'Belum Lengkap';
                $status_color = ($jumlah_nilai == $total_sub) ? '#10b981' : '#f59e0b';
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['nama_jabatan'] ?></td>
                <td><?= $jumlah_nilai ?> / <?= $total_sub ?></td>
                <td>
                    <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; background:<?= $status_color ?>33; color:<?= $status_color ?>;">
                        <?= $status ?>
                    </span>
                </td>
                <td>
                    <a href="?karyawan_id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                        <?= $jumlah_nilai > 0 ? 'Edit' : 'Input' ?> Nilai
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
            
            <?php if (mysqli_num_rows($karyawan) == 0): ?>
            <tr>
                <td colspan="6" class="text-center">Belum ada data karyawan</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
