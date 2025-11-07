<?php
include '../includes/header.php';

$karyawan_id = isset($_GET['id']) ? clean($_GET['id']) : '';
if (!$karyawan_id) redirect('perhitungan.php');

// Ambil info karyawan dan nilai akhir (pakai nilai_total dari tabel hasil jika tersedia)
$query_info = "
    SELECT k.nama, j.nama_jabatan, h.nilai_total, h.ranking, h.rekomendasi
    FROM karyawan k
    LEFT JOIN jabatan j ON k.jabatan_id = j.id
    LEFT JOIN hasil h ON k.id = h.karyawan_id
    WHERE k.id = '$karyawan_id'
";
$info = mysqli_fetch_assoc(mysqli_query($conn, $query_info));

// Ambil semua kriteria
$kriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");

// Kita akan hitung kembali nilai akhir per kriteria (untuk tampil jika hasil.nilai_total kosong)
// dan sekaligus memastikan bobot diperlakukan konsisten
$nilai_akhir_karyawan = 0;
$perhitungan_per_kriteria = []; // simpan detail tiap kriteria untuk ditampilkan

while ($kr = mysqli_fetch_assoc($kriteria)) {
    $kriteria_id = $kr['id'];

    // Normalisasi bobot: jika > 1 dianggap persen (mis. 20) -> ubah ke desimal 0.20,
    // jika <= 1 dianggap sudah desimal (mis. 0.20)
    $bobot_decimal = ($kr['bobot'] > 1) ? ($kr['bobot'] / 100) : $kr['bobot'];
    $bobot_display = $bobot_decimal * 100; // untuk ditampilkan seperti "20%"

    // Ambil semua subkriteria & gap_detail untuk kriteria ini
    $sub = mysqli_query($conn, "
        SELECT gd.*, sk.nama_subkriteria, sk.target, sk.tipe
        FROM gap_detail gd
        JOIN subkriteria sk ON gd.subkriteria_id = sk.id
        WHERE gd.karyawan_id = '$karyawan_id' AND sk.kriteria_id = '$kriteria_id'
        ORDER BY sk.id ASC
    ");

    $sum_core = 0; $sum_secondary = 0;
    $count_core = 0; $count_secondary = 0;
    $subs_arr = [];

    while ($row = mysqli_fetch_assoc($sub)) {
        // simpan tiap baris untuk ditampilkan
        $subs_arr[] = $row;

        if ($row['tipe'] === 'Core') {
            $sum_core += (float)$row['bobot_gap'];
            $count_core++;
        } else {
            $sum_secondary += (float)$row['bobot_gap'];
            $count_secondary++;
        }
    }

    // hitung NCF dan NSF per kriteria (rata-rata bobot_gap)
    $ncf = $count_core ? ($sum_core / $count_core) : 0;
    $nsf = $count_secondary ? ($sum_secondary / $count_secondary) : 0;

    // nilai total kriteria = 60% NCF + 40% NSF
    $nilai_total_kriteria = ($ncf * 0.6) + ($nsf * 0.4);

    // nilai bobot kriteria terhadap nilai akhir = nilai_total_kriteria * bobot_decimal
    $nilai_bobot_kriteria = $nilai_total_kriteria * $bobot_decimal;

    // akumulasikan
    $nilai_akhir_karyawan += $nilai_bobot_kriteria;

    // simpan untuk rendering
    $perhitungan_per_kriteria[] = [
        'kriteria' => $kr['nama_kriteria'],
        'bobot_display' => $bobot_display,
        'subs' => $subs_arr,
        'sum_core' => $sum_core,
        'sum_secondary' => $sum_secondary,
        'count_core' => $count_core,
        'count_secondary' => $count_secondary,
        'ncf' => $ncf,
        'nsf' => $nsf,
        'nilai_total_kriteria' => $nilai_total_kriteria,
        'nilai_bobot_kriteria' => $nilai_bobot_kriteria
    ];
}

// Jika di tabel hasil sudah ada nilai_total (kolom bisa bernama nilai_total atau nilai_akhir tergantung skema),
// tampilkan nilai dari tabel hasil agar konsisten dengan perhitungan utama.
// Jika kosong atau nol, gunakan hasil hitung ulang ($nilai_akhir_karyawan) sebagai fallback.
$nilai_dari_hasil = null;
if (isset($info['nilai_total'])) {
    $nilai_dari_hasil = (float)$info['nilai_total'];
}
if (!$nilai_dari_hasil || $nilai_dari_hasil == 0) {
    // fallback ke perhitungan lokal
    $display_nilai_akhir = $nilai_akhir_karyawan;
} else {
    $display_nilai_akhir = $nilai_dari_hasil;
}
?>
<div class="card">
    <div class="card-header">
        <h2>Detail Perhitungan GAP</h2>
    </div>

    <div style="background:#f8f9fa;padding:20px;border-radius:5px;margin-bottom:20px;">
        <h3>Informasi Karyawan</h3>
        <table style="width:auto;">
            <tr><td><strong>Nama</strong></td><td>: <?= htmlspecialchars($info['nama']) ?></td></tr>
            <tr><td><strong>Jabatan</strong></td><td>: <?= htmlspecialchars($info['nama_jabatan']) ?></td></tr>
            <tr><td><strong>Ranking</strong></td><td>: #<?= htmlspecialchars($info['ranking']) ?></td></tr>
            <tr>
                <td><strong>Nilai Akhir</strong></td>
                <td>: <strong style="color:#667eea;"><?= formatAngka($display_nilai_akhir) ?></strong></td>
            </tr>
            <tr><td><strong>Rekomendasi</strong></td><td>: <?= htmlspecialchars($info['rekomendasi']) ?></td></tr>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Detail Perhitungan per Kriteria</h2>
    </div>

    <?php foreach ($perhitungan_per_kriteria as $item): ?>
    <div style="background:#f8fafc;padding:15px;border-radius:10px;margin-bottom:20px;border:1px solid #e5e7eb;">
        <h3 style="color:#1e3a8a;">Kriteria: <?= htmlspecialchars($item['kriteria']) ?> (Bobot <?= formatAngka($item['bobot_display'], 2) ?>%)</h3>

        <table>
            <thead>
                <tr>
                    <th>No</th><th>Subkriteria</th><th>Tipe</th><th>Target</th>
                    <th>Nilai Aktual</th><th>GAP</th><th>Bobot GAP</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($item['subs'] as $row): 
                    $gap_color = $row['gap'] == 0 ? '#10b981' : ($row['gap'] > 0 ? '#3b82f6' : '#ef4444');
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_subkriteria']) ?></td>
                    <td>
                        <span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;background:<?= $row['tipe']=='Core'?'#dbeafe':'#fef3c7' ?>;color:<?= $row['tipe']=='Core'?'#1e40af':'#92400e' ?>;">
                            <?= htmlspecialchars($row['tipe']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['target']) ?></td>
                    <td><?= htmlspecialchars($row['nilai']) ?></td>
                    <td><strong style="color:<?= $gap_color ?>;"><?= $row['gap'] > 0 ? '+' : '' ?><?= $row['gap'] ?></strong></td>
                    <td><?= formatAngka($row['bobot_gap']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:15px;padding:10px;background:#f9fafb;border-radius:8px;">
            <p>Nilai Total Kriteria = (NCF × 0.6) + (NSF × 0.4) = <strong><?= formatAngka($item['nilai_total_kriteria']) ?></strong></p>
            <p>Nilai Bobot Kriteria = <?= formatAngka($item['bobot_display'],2) ?>% × <?= formatAngka($item['nilai_total_kriteria']) ?> = 
                <strong style="color:#2563eb;"><?= formatAngka($item['nilai_bobot_kriteria']) ?></strong>
            </p>
        </div>
    </div>
    <?php endforeach; ?>

    <div style="background:#e0f2fe;padding:20px;border-radius:10px;margin-top:30px;">
        <h3 style="color:#1d4ed8;">💡 Nilai Akhir Karyawan</h3>
        <p>Nilai Akhir = Σ (Bobot Kriteria × Nilai Total Kriteria)</p>
        <p><strong style="font-size:20px;color:#1d4ed8;">Nilai Akhir = <?= formatAngka($nilai_akhir_karyawan) ?></strong></p>
        <p style="font-size:12px;color:#555;">(Jika tabel <em>hasil</em> sudah berisi nilai, bagian atas menampilkan nilai dari tabel <em>hasil</em> untuk konsistensi.)</p>
    </div>

    <div class="mt-3">
        <a href="perhitungan.php" class="btn btn-secondary">Kembali</a>
        <button onclick="window.print()" class="btn btn-primary">📄 Cetak PDF</button>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
