<?php
include '../includes/header.php';

// Hitung statistik
$total_jabatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM jabatan"))['total'];
$total_karyawan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM karyawan"))['total'];
$total_kriteria = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kriteria"))['total'];
$total_penilaian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM nilai"))['total'];
?>

<div class="card">
    <div class="card-header">
        <h2>Dashboard</h2>
    </div>
    
    <p>Selamat datang, <strong><?= $_SESSION['username'] ?></strong>!</p>
    <p>Sistem Analisis GAP Kompetensi untuk Pengambilan Keputusan Promosi Jabatan</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Jabatan</h3>
        <div class="number"><?= $total_jabatan ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Total Karyawan</h3>
        <div class="number"><?= $total_karyawan ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Total Kriteria</h3>
        <div class="number"><?= $total_kriteria ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Total Penilaian</h3>
        <div class="number"><?= $total_penilaian ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Panduan Penggunaan Sistem</h2>
    </div>
    
    <ol style="line-height: 2;">
        <li><strong>Master Data Jabatan:</strong> Tambahkan data jabatan yang tersedia di perusahaan</li>
        <li><strong>Master Data Karyawan:</strong> Tambahkan data karyawan beserta jabatan saat ini</li>
        <li><strong>Master Data Kriteria:</strong> Tentukan kriteria penilaian (Core/Secondary) beserta bobot dan target nilai</li>
        <li><strong>Input Penilaian:</strong> Berikan nilai untuk setiap karyawan pada masing-masing kriteria (skala 1-5)</li>
        <li><strong>Proses Perhitungan:</strong> Sistem akan menghitung GAP, NCF, NSF, dan Nilai Total</li>
        <li><strong>Lihat Hasil:</strong> Ranking karyawan berdasarkan nilai total dengan rekomendasi</li>
    </ol>
</div>

<div class="card">
    <div class="card-header">
        <h2>Metode Profile Matching</h2>
    </div>
    
    <p><strong>Langkah Perhitungan:</strong></p>
    <ol style="line-height: 2;">
        <li>Hitung GAP = Nilai Aktual - Nilai Target</li>
        <li>Konversi GAP menjadi Bobot GAP (lihat tabel bobot)</li>
        <li>Hitung NCF (Core Factor) = Rata-rata Bobot GAP kriteria Core</li>
        <li>Hitung NSF (Secondary Factor) = Rata-rata Bobot GAP kriteria Secondary</li>
        <li>Hitung Nilai Total = (NCF × 60%) + (NSF × 40%)</li>
        <li>Ranking berdasarkan Nilai Total tertinggi</li>
    </ol>
    
    <table style="margin-top: 20px; max-width: 600px;">
        <thead>
            <tr>
                <th>Selisih GAP</th>
                <th>Bobot Nilai</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>0</td><td>5</td><td>Kompetensi sesuai</td></tr>
            <tr><td>1</td><td>4.5</td><td>Kompetensi lebih 1 tingkat</td></tr>
            <tr><td>-1</td><td>4</td><td>Kompetensi kurang 1 tingkat</td></tr>
            <tr><td>2</td><td>3.5</td><td>Kompetensi lebih 2 tingkat</td></tr>
            <tr><td>-2</td><td>3</td><td>Kompetensi kurang 2 tingkat</td></tr>
            <tr><td>3</td><td>2.5</td><td>Kompetensi lebih 3 tingkat</td></tr>
            <tr><td>-3</td><td>2</td><td>Kompetensi kurang 3 tingkat</td></tr>
            <tr><td>4</td><td>1.5</td><td>Kompetensi lebih 4 tingkat</td></tr>
            <tr><td>-4</td><td>1</td><td>Kompetensi kurang 4 tingkat</td></tr>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>