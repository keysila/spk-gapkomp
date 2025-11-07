<?php
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['admin_id'])) {
    redirect('../index.php');
}

$karyawan_id = isset($_GET['id']) ? clean($_GET['id']) : '';

if (!$karyawan_id) {
    redirect('hasil.php');
}

// Ambil info karyawan dan hasil
$query_info = "SELECT kar.nama, jab.nama_jabatan, h.ncf, h.nsf, h.nilai_total, h.ranking, h.rekomendasi
               FROM karyawan kar
               LEFT JOIN jabatan jab ON kar.jabatan_id = jab.id
               LEFT JOIN hasil h ON kar.id = h.karyawan_id
               WHERE kar.id = '$karyawan_id'";
$result_info = mysqli_query($conn, $query_info);
$info = mysqli_fetch_assoc($result_info);

if (!$info) {
    redirect('hasil.php');
}

// Ambil detail gap
$query_gap = "SELECT gd.*, krit.nama_kriteria, krit.tipe, krit.bobot
              FROM gap_detail gd
              JOIN kriteria krit ON gd.kriteria_id = krit.id
              WHERE gd.karyawan_id = '$karyawan_id'
              ORDER BY krit.tipe ASC, krit.id ASC";
$gap_detail = mysqli_query($conn, $query_gap);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Detail GAP Kompetensi - <?= $info['nama'] ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 20px;
            line-height: 1.6;
        }
        
        /* Header yang bisa disembunyikan saat print */
        .print-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            margin: -20px -20px 20px -20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .print-header h3 {
            margin: 0;
        }
        
        .btn-print {
            background: white;
            color: #667eea;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .btn-print:hover {
            background: #f0f0f0;
        }
        
        .btn-back {
            background: #ef4444;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        
        /* Header laporan untuk print */
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        
        .report-header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .report-header h2 {
            font-size: 18px;
            color: #667eea;
            font-weight: normal;
        }
        
        .report-header .tanggal {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        
        .info-box {
            border: 2px solid #333;
            padding: 20px;
            margin-bottom: 25px;
            background: #f8f9fa;
        }
        
        .info-box h3 {
            margin-bottom: 15px;
            font-size: 16px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
        }
        
        .info-table {
            width: 100%;
        }
        
        .info-table td {
            padding: 8px 0;
        }
        
        .info-table td:first-child {
            width: 200px;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        
        table th, table td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        
        table th {
            background-color: #333;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        table td {
            vertical-align: middle;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .badge-core {
            display: inline-block;
            padding: 4px 10px;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-secondary {
            display: inline-block;
            padding: 4px 10px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .gap-positive {
            color: #2563eb;
            font-weight: bold;
        }
        
        .gap-negative {
            color: #dc2626;
            font-weight: bold;
        }
        
        .gap-zero {
            color: #059669;
            font-weight: bold;
        }
        
        .calculation-box {
            border: 2px solid #667eea;
            padding: 20px;
            background: #f0f4ff;
            margin-bottom: 25px;
        }
        
        .calculation-box h3 {
            color: #1e40af;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .calculation-box p {
            margin: 8px 0;
            font-size: 14px;
        }
        
        .final-result {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            border-radius: 5px;
        }
        
        .keterangan {
            margin-top: 25px;
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
        }
        
        .keterangan h4 {
            margin-bottom: 10px;
            color: #856404;
        }
        
        .keterangan ul {
            margin-left: 20px;
        }
        
        .keterangan li {
            margin: 5px 0;
            font-size: 13px;
        }
        
        .footer-info {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
            font-size: 12px;
            color: #666;
        }
        
        /* Print styles */
        @media print {
            body {
                padding: 0;
            }
            
            .print-header {
                display: none !important;
            }
            
            .report-header {
                border-bottom: 3px solid #000;
            }
            
            .info-box, .calculation-box {
                break-inside: avoid;
            }
            
            table {
                break-inside: avoid;
            }
            
            .btn-print, .btn-back {
                display: none !important;
            }
            
            @page {
                margin: 2cm;
            }
        }
    </style>
</head>
<body>
    <!-- Header dengan tombol (disembunyikan saat print) -->
    <div class="print-header">
        <h3>📄 Preview Laporan Detail</h3>
        <div>
            <button onclick="window.print()" class="btn-print">🖨️ Cetak / Save PDF</button>
            <a href="hasil.php" class="btn-back">← Kembali</a>
        </div>
    </div>
    
    <!-- Header Laporan (muncul saat print) -->
    <div class="report-header">
        <h1>LAPORAN HASIL ANALISIS GAP KOMPETENSI</h1>
        <h2><?= strtoupper($info['nama']) ?></h2>
        <p class="tanggal">Tanggal Cetak: <?= date('d F Y') ?></p>
    </div>
    
    <!-- Informasi Karyawan -->
    <div class="info-box">
        <h3>INFORMASI KARYAWAN</h3>
        <table class="info-table">
            <tr>
                <td>Nama Lengkap</td>
                <td>: <?= $info['nama'] ?></td>
            </tr>
            <tr>
                <td>Jabatan Saat Ini</td>
                <td>: <?= $info['nama_jabatan'] ?></td>
            </tr>
            <tr>
                <td>Ranking</td>
                <td>: <strong>#<?= $info['ranking'] ?></strong></td>
            </tr>
            <tr>
                <td>Rekomendasi</td>
                <td>: <strong><?= $info['rekomendasi'] ?></strong></td>
            </tr>
        </table>
    </div>
    
    <!-- Detail GAP per Kriteria -->
    <h3 style="margin-bottom: 15px;">DETAIL PENILAIAN DAN GAP ANALYSIS</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Kriteria</th>
                <th width="10%">Tipe</th>
                <th width="10%">Bobot</th>
                <th width="8%">Target</th>
                <th width="8%">Nilai</th>
                <th width="8%">GAP</th>
                <th width="13%">Bobot GAP</th>
                <th width="13%">Weighted Score</th>
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
                
                // Class untuk GAP
                if ($row['gap'] == 0) {
                    $gap_class = 'gap-zero';
                } elseif ($row['gap'] > 0) {
                    $gap_class = 'gap-positive';
                } else {
                    $gap_class = 'gap-negative';
                }
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= $row['nama_kriteria'] ?></td>
                <td class="text-center">
                    <span class="<?= $row['tipe'] == 'Core' ? 'badge-core' : 'badge-secondary' ?>">
                        <?= $row['tipe'] ?>
                    </span>
                </td>
                <td class="text-center"><?= formatAngka($row['bobot'] * 100, 1) ?>%</td>
                <td class="text-center"><?= $row['target'] ?></td>
                <td class="text-center"><?= $row['nilai'] ?></td>
                <td class="text-center">
                    <span class="<?= $gap_class ?>">
                        <?= $row['gap'] > 0 ? '+' : '' ?><?= $row['gap'] ?>
                    </span>
                </td>
                <td class="text-center"><?= formatAngka($row['bobot_gap']) ?></td>
                <td class="text-center"><?= formatAngka($weighted, 4) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <!-- Perhitungan Final -->
    <div class="calculation-box">
        <h3>📊 PERHITUNGAN CORE FACTOR (Bobot 60%)</h3>
        <p>Σ Weighted Score Core = <?= formatAngka($sum_core, 4) ?></p>
        <p>Σ Bobot Core = <?= formatAngka($total_bobot_core, 4) ?></p>
        <p><strong>NCF = <?= formatAngka($sum_core, 4) ?> ÷ <?= formatAngka($total_bobot_core, 4) ?> = <?= formatAngka($info['ncf'], 4) ?></strong></p>
    </div>
    
    <div class="calculation-box">
        <h3>📊 PERHITUNGAN SECONDARY FACTOR (Bobot 40%)</h3>
        <p>Σ Weighted Score Secondary = <?= formatAngka($sum_secondary, 4) ?></p>
        <p>Σ Bobot Secondary = <?= formatAngka($total_bobot_secondary, 4) ?></p>
        <p><strong>NSF = <?= formatAngka($sum_secondary, 4) ?> ÷ <?= formatAngka($total_bobot_secondary, 4) ?> = <?= formatAngka($info['nsf'], 4) ?></strong></p>
    </div>
    
    <div class="calculation-box">
        <h3>🎯 PERHITUNGAN NILAI TOTAL</h3>
        <p>Nilai Total = (NCF × 60%) + (NSF × 40%)</p>
        <p>Nilai Total = (<?= formatAngka($info['ncf'], 4) ?> × 0,6) + (<?= formatAngka($info['nsf'], 4) ?> × 0,4)</p>
        <p>Nilai Total = <?= formatAngka($info['ncf'] * 0.6, 4) ?> + <?= formatAngka($info['nsf'] * 0.4, 4) ?></p>
        <div class="final-result">
            NILAI TOTAL = <?= formatAngka($info['nilai_total'], 4) ?>
        </div>
    </div>
    
    <!-- Keterangan -->
    <div class="keterangan">
        <h4>📌 KETERANGAN</h4>
        <ul>
            <li><strong>GAP</strong> = Nilai Aktual - Nilai Target</li>
            <li><strong>Bobot GAP</strong> menggunakan tabel konversi standar Profile Matching</li>
            <li><strong>Weighted Score</strong> = Bobot GAP × Bobot Kriteria</li>
            <li><strong>NCF (Core Factor)</strong> = Faktor utama dengan bobot 60%</li>
            <li><strong>NSF (Secondary Factor)</strong> = Faktor pendukung dengan bobot 40%</li>
            <li><strong>Nilai Total</strong> digunakan untuk menentukan ranking karyawan</li>
            <li>Semakin tinggi nilai total, semakin direkomendasikan untuk promosi jabatan</li>
        </ul>
    </div>
    
    <!-- Footer -->
    <div class="footer-info">
        <p><strong>Sistem Analisis GAP Kompetensi - Profile Matching Method</strong></p>
        <p>Dicetak pada: <?= date('d F Y H:i:s') ?> WIB</p>
        <p>Dokumen ini dihasilkan secara otomatis oleh sistem</p>
    </div>
    
    <script>
        // Auto focus untuk print dialog
        window.onload = function() {
            // Jika ada parameter print, langsung buka dialog print
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === '1') {
                setTimeout(() => window.print(), 500);
            }
        };
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>