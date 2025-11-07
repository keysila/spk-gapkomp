<?php
include '../includes/header.php';

$sukses = '';
$error = '';

// ========================
// PROSES PERHITUNGAN
// ========================
if (isset($_POST['hitung'])) {
    mysqli_query($conn, "TRUNCATE TABLE hasil");
    mysqli_query($conn, "TRUNCATE TABLE gap_detail");

    $karyawan = mysqli_query($conn, "SELECT * FROM karyawan");

    while ($kar = mysqli_fetch_assoc($karyawan)) {
        $karyawan_id = $kar['id'];

        // Ambil semua nilai karyawan beserta subkriteria dan kriteria
        $query_nilai = "
            SELECT n.*, s.id AS sub_id, s.kriteria_id, s.target, s.tipe
            FROM nilai n
            JOIN subkriteria s ON n.subkriteria_id = s.id
            WHERE n.karyawan_id = '$karyawan_id'
        ";
        $nilai = mysqli_query($conn, $query_nilai);

        // Simpan GAP ke tabel gap_detail
        while ($nil = mysqli_fetch_assoc($nilai)) {
            $gap = $nil['nilai'] - $nil['target'];
            $bobot_gap = getBobotGap($gap);
            mysqli_query($conn, "
                INSERT INTO gap_detail (karyawan_id, subkriteria_id, nilai, target, gap, bobot_gap)
                VALUES ('$karyawan_id', '{$nil['sub_id']}', '{$nil['nilai']}', '{$nil['target']}', '$gap', '$bobot_gap')
            ");
        }

        // Tahap 1: Hitung Nilai Total per Kriteria
        $kriteria = mysqli_query($conn, "SELECT * FROM kriteria");
        $nilai_total_karyawan = 0;
        $total_ncf = 0;
        $total_nsf = 0;

        while ($kr = mysqli_fetch_assoc($kriteria)) {
            $kriteria_id = $kr['id'];
            // Konversi otomatis ke desimal bila input dalam % (misal 20 berarti 0.20)
            $bobot_kriteria = ($kr['bobot'] > 1) ? $kr['bobot'] / 100 : $kr['bobot'];

            // NCF
            $core = mysqli_query($conn, "
                SELECT AVG(g.bobot_gap) AS avg_core
                FROM gap_detail g
                JOIN subkriteria s ON g.subkriteria_id = s.id
                WHERE g.karyawan_id = '$karyawan_id'
                AND s.kriteria_id = '$kriteria_id'
                AND s.tipe = 'Core'
            ");
            $ncf = mysqli_fetch_assoc($core)['avg_core'] ?? 0;

            // NSF
            $secondary = mysqli_query($conn, "
                SELECT AVG(g.bobot_gap) AS avg_secondary
                FROM gap_detail g
                JOIN subkriteria s ON g.subkriteria_id = s.id
                WHERE g.karyawan_id = '$karyawan_id'
                AND s.kriteria_id = '$kriteria_id'
                AND s.tipe = 'Secondary'
            ");
            $nsf = mysqli_fetch_assoc($secondary)['avg_secondary'] ?? 0;

            // Nilai total per kriteria
            $nilai_total_kriteria = ($ncf * 0.6) + ($nsf * 0.4);
            $nilai_total_karyawan += $nilai_total_kriteria * $bobot_kriteria;

            $total_ncf += $ncf;
            $total_nsf += $nsf;
        }

        // Tahap 2: Simpan ke tabel hasil
        $avg_ncf = $total_ncf / max(mysqli_num_rows($kriteria), 1);
        $avg_nsf = $total_nsf / max(mysqli_num_rows($kriteria), 1);

        mysqli_query($conn, "
            INSERT INTO hasil (karyawan_id, ncf, nsf, nilai_akhir)
            VALUES ('$karyawan_id', '$avg_ncf', '$avg_nsf', '$nilai_total_karyawan')
        ");
    }

    // Ranking dan rekomendasi
    $hasil = mysqli_query($conn, "SELECT * FROM hasil ORDER BY nilai_akhir DESC");
    $ranking = 1;

    while ($hsl = mysqli_fetch_assoc($hasil)) {
        if ($ranking == 1) {
            $rekomendasi = "Sangat Direkomendasikan";
        } elseif ($ranking <= 3) {
            $rekomendasi = "Direkomendasikan";
        } else {
            $rekomendasi = "Cukup Direkomendasikan";
        }

        mysqli_query($conn, "
            UPDATE hasil 
            SET ranking='$ranking', rekomendasi='$rekomendasi' 
            WHERE id='{$hsl['id']}'
        ");
        $ranking++;
    }

    $sukses = "Perhitungan GAP berhasil dilakukan dengan pembobotan per kriteria!";
}

// ========================
// AMBIL DATA HASIL
// ========================
$query_hasil = "
    SELECT h.*, k.nama AS nama_karyawan, j.nama_jabatan
    FROM hasil h
    JOIN karyawan k ON h.karyawan_id = k.id
    JOIN jabatan j ON k.jabatan_id = j.id
    ORDER BY h.ranking ASC
";
$hasil = mysqli_query($conn, $query_hasil);

// Info data
$total_karyawan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM karyawan"))['total'];
$total_nilai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT karyawan_id) as total FROM nilai"))['total'];
?>

<div class="card">
    <div class="card-header">
        <h2>Proses Perhitungan GAP Kompetensi</h2>
    </div>

    <?php if ($sukses): ?><div class="alert alert-success"><?= $sukses ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div style="background:#f8f9fa; padding:20px; border-radius:5px; margin-bottom:20px;">
        <h3 style="margin-bottom:15px;">Informasi Data:</h3>
        <p><strong>Total Karyawan:</strong> <?= $total_karyawan ?></p>
        <p><strong>Karyawan dengan Penilaian Lengkap:</strong> <?= $total_nilai ?></p>
        <p><strong>Status:</strong>
            <?php if ($total_nilai == 0): ?>
                <span style="color:#ef4444;">Belum ada data penilaian</span>
            <?php elseif ($total_nilai < $total_karyawan): ?>
                <span style="color:#f59e0b;">Penilaian belum lengkap untuk semua karyawan</span>
            <?php else: ?>
                <span style="color:#10b981;">Semua karyawan sudah dinilai</span>
            <?php endif; ?>
        </p>
    </div>

    <div style="background:#dbeafe; padding:20px; border-radius:5px; margin-bottom:20px;">
        <h3 style="color:#1e40af; margin-bottom:15px;">Tahapan Perhitungan Profile Matching:</h3>
        <ol style="line-height:2; color:#1e40af;">
            <li>Menghitung GAP = Nilai Aktual - Nilai Target</li>
            <li>Mengkonversi GAP menjadi Bobot GAP berdasarkan tabel standar</li>
            <li>Menghitung weighted score untuk setiap kriteria = Bobot GAP × Bobot Kriteria</li>
            <li>Menghitung NCF (Core Factor) = Σ(weighted score Core) / Σ(bobot Core)</li>
            <li>Menghitung NSF (Secondary Factor) = Σ(weighted score Secondary) / Σ(bobot Secondary)</li>
            <li>Menghitung Nilai Total = (NCF × 60%) + (NSF × 40%)</li>
            <li>Membuat ranking berdasarkan Nilai Total tertinggi</li>
        </ol>
    </div>

    <form method="POST">
        <button type="submit" name="hitung" class="btn btn-success" <?= $total_nilai == 0 ? 'disabled' : '' ?>>
            <strong>🧮 Mulai Perhitungan</strong>
        </button>
        <?php if ($total_nilai == 0): ?>
            <p style="color:#ef4444; margin-top:10px; font-size:14px;">
                *Silakan input penilaian terlebih dahulu di menu Penilaian
            </p>
        <?php endif; ?>
    </form>
</div>

<?php if (mysqli_num_rows($hasil) > 0): ?>
<div class="card">
    <div class="card-header">
        <h2>Hasil Perhitungan</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ranking</th>
                <th>Nama Karyawan</th>
                <th>Jabatan</th>
                <th>Nilai Akhir</th>
                <th>Rekomendasi</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($hasil)):
            if ($row['ranking'] == 1) $bg_color = '#10b981';
            elseif ($row['ranking'] <= 3) $bg_color = '#3b82f6';
            else $bg_color = '#6b7280';
        ?>
            <tr>
                <td><strong style="font-size:18px; color:<?= $bg_color ?>;">#<?= $row['ranking'] ?></strong></td>
                <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                <td><strong><?= formatAngka($row['nilai_akhir']) ?></strong></td>
                <td>
                    <span style="display:inline-block; padding:6px 12px; border-radius:20px; font-size:12px; background:<?= $bg_color ?>; color:white;">
                        <?= htmlspecialchars($row['rekomendasi']) ?>
                    </span>
                </td>
                <td>
                    <a href="detail_perhitungan.php?id=<?= $row['karyawan_id'] ?>" class="btn btn-primary btn-sm">Lihat Detail</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <div class="mt-3">
        <a href="hasil.php" class="btn btn-success">Lihat Halaman Hasil Lengkap</a>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
