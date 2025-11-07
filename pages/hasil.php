<?php
include '../includes/header.php';

$karyawan_id = isset($_GET['id']) ? clean($_GET['id']) : '';

if (!$karyawan_id) {
    redirect('perhitungan.php');
}

// Ambil info karyawan dan hasil
$query_info = "SELECT k.nama, j.nama_jabatan, h.ncf, h.nsf, h.nilai_total, h.ranking, h.rekomendasi
               FROM karyawan k
               LEFT JOIN jabatan j ON k.jabatan_id = j.id
               LEFT JOIN hasil h ON k.id = h.karyawan_id
               WHERE k.id = '$karyawan_id'";
$info = mysqli_fetch_assoc(mysqli_query($conn, $query_info));

// Ambil detail gap
$query_gap = "SELECT gd.*, k.nama_kriteria, k.tipe, k.bobot
              FROM gap_detail gd
              JOIN kriteria k ON gd.kriteria_id = k.id
              WHERE gd.karyawan_id = '$karyawan_id'
              ORDER BY k.tipe ASC, k.id ASC";
$gap_detail = mysqli_query($conn, $query_gap);
?>

<div class="card">
    <div class="card-header">
        <h2>Detail Perhitungan GAP</h2>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px;">Informasi Karyawan</h3>
        <table style="width: auto;">
            <tr>
                <td style="border: none; padding: 8px 20px 8px 0;"><strong>Nama</strong></td>
                <td style="border: none; padding: 8px;">: <?= $info['nama'] ?></td>
            </tr>
            <tr>
                <td style="border: none; padding: 8px 20px 8px 0;"><strong>Jabatan</strong></td>
                <td style="border: none; padding: 8px;">: <?= $info['nama_jabatan'] ?></td>
            </tr>
            <tr>
                <td style="border: none; padding: 8px 20px 8px 0;"><strong>Ranking</strong></td>
                <td style="border: none; padding: 8px;">: #<?= $info['ranking'] ?></td>
            </tr>
            <tr>
                <td style="border: none; padding: 8px 20px 8px 0;"><strong>NCF (Core Factor)</strong></td>
                <td style="border: none; padding: 8px;">: <?= formatAngka($info['ncf']) ?></td>
            </tr>
            <tr>
                <td style="border: none; padding: 8px 20px 8px 0;"><strong>NSF (Secondary Factor)</strong></td>
                <td style="border: none; padding: 8px;">: <?= formatAngka($info['nsf']) ?></td>
            </tr>
            <tr>
                <td style="border: none; padding: 8px 20px 8px 0;"><strong>Nilai Total</strong></td>
                <td style="border: none; padding: 8px;">: <strong style="color: #667eea;"><?= formatAngka($info['nilai_total']) ?></strong></td>
            </tr>
            <tr>
                <td style="border: none; padding: 8px 20px 8px 0;"><strong>Rekomendasi</strong></td>
                <td style="border: none; padding: 8px;">: <?= $info['rekomendasi'] ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Detail GAP per Kriteria</h2>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kriteria</th>
                <th>Tipe</th>
                <th>Bobot Kriteria</th>
                <th>Target</th>
                <th>Nilai Aktual</th>
                <th>GAP</th>
                <th>Bobot GAP</th>
                <th>Weighted Score</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $sum_core = 0;
            $sum_secondary = 0;
            $total_bobot_core = 0;
            $total_bobot_secondary = 0;
            
            while ($row = mysqli_fetch_assoc($gap_detail)): 
                $weighted = $row['bobot_gap'] * $row['bobot'];
                
                if ($row['tipe'] == 'Core') {
                    $sum_core += $weighted;
                    $total_bobot_core += $row['bobot'];
                } else {
                    $sum_secondary += $weighted;
                    $total_bobot_secondary += $row['bobot'];
                }
                
                // Warna untuk GAP
                if ($row['gap'] == 0) {
                    $gap_color = '#10b981';
                } elseif ($row['gap'] > 0) {
                    $gap_color = '#3b82f6';
                } else {
                    $gap_color = '#ef4444';
                }
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['nama_kriteria'] ?></td>
                <td>
                    <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; background: <?= $row['tipe'] == 'Core' ? '#dbeafe' : '#fef3c7' ?>; color: <?= $row['tipe'] == 'Core' ? '#1e40af' : '#92400e' ?>;">
                        <?= $row['tipe'] ?>
                    </span>
                </td>
                <td><?= formatAngka($row['bobot'] * 100) ?>%</td>
                <td><?= $row['target'] ?></td>
                <td><?= $row['nilai'] ?></td>
                <td>
                    <strong style="color: <?= $gap_color ?>;">
                        <?= $row['gap'] > 0 ? '+' : '' ?><?= $row['gap'] ?>
                    </strong>
                </td>
                <td><?= formatAngka($row['bobot_gap']) ?></td>
                <td><?= formatAngka($weighted) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <div class="card-header">
        <h2>Perhitungan Akhir</h2>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
        <h3 style="color: #1e40af; margin-bottom: 15px;">Core Factor (60%)</h3>
        <p>Σ Weighted Score Core = <?= formatAngka($sum_core) ?></p>
        <p>Σ Bobot Core = <?= formatAngka($total_bobot_core) ?></p>
        <p><strong>NCF = <?= formatAngka($sum_core) ?> / <?= formatAngka($total_bobot_core) ?> = <?= formatAngka($info['ncf']) ?></strong></p>
        
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
        
        <h3 style="color: #92400e; margin-bottom: 15px;">Secondary Factor (40%)</h3>
        <p>Σ Weighted Score Secondary = <?= formatAngka($sum_secondary) ?></p>
        <p>Σ Bobot Secondary = <?= formatAngka($total_bobot_secondary) ?></p>
        <p><strong>NSF = <?= formatAngka($sum_secondary) ?> / <?= formatAngka($total_bobot_secondary) ?> = <?= formatAngka($info['nsf']) ?></strong></p>
        
        <hr style="margin: 20px 0; border: none; border-top: 2px solid #667eea;">
        
        <h3 style="color: #667eea; margin-bottom: 15px;">Nilai Total</h3>
        <p>Nilai Total = (NCF × 60%) + (NSF × 40%)</p>
        <p>Nilai Total = (<?= formatAngka($info['ncf']) ?> × 0.6) + (<?= formatAngka($info['nsf']) ?> × 0.4)</p>
        <p>Nilai Total = <?= formatAngka($info['ncf'] * 0.6) ?> + <?= formatAngka($info['nsf'] * 0.4) ?></p>
        <p><strong style="font-size: 20px; color: #667eea;">Nilai Total = <?= formatAngka($info['nilai_total']) ?></strong></p>
    </div>
    
    <div class="mt-3">
        <a href="perhitungan.php" class="btn btn-secondary">Kembali</a>
        <button onclick="window.print()" class="btn btn-primary">Cetak Detail</button>
    </div>
</div>

<?php include '../includes/footer.php'; ?>