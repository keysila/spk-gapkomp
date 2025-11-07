<?php
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['admin_id'])) {
    redirect('../index.php');
}

// Ambil nama file aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem GAP Kompetensi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-brand {
            color: white;
            font-size: 20px;
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
        }
        
        .nav-menu li a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: background 0.3s;
        }
        
        .nav-menu li a:hover,
        .nav-menu li a.active {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .card-header {
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .card-header h2 {
            color: #333;
            font-size: 24px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table thead {
            background: #f8f9fa;
        }
        
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        table th {
            font-weight: 600;
            color: #333;
        }
        
        table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #3b82f6;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: 600;
            color: #667eea;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        .mb-3 {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">Sistem GAP Kompetensi</div>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                <li><a href="jabatan.php" class="<?= $current_page == 'jabatan.php' ? 'active' : '' ?>">Jabatan</a></li>
                <li><a href="karyawan.php" class="<?= $current_page == 'karyawan.php' ? 'active' : '' ?>">Karyawan</a></li>
                <li><a href="kriteria.php" class="<?= $current_page == 'kriteria.php' ? 'active' : '' ?>">Kriteria</a></li>
                <li><a href="penilaian.php" class="<?= $current_page == 'penilaian.php' ? 'active' : '' ?>">Penilaian</a></li>
                <li><a href="perhitungan.php" class="<?= $current_page == 'perhitungan.php' ? 'active' : '' ?>">Perhitungan</a></li>
                <li><a href="hasil.php" class="<?= $current_page == 'hasil.php' ? 'active' : '' ?>">Hasil</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">