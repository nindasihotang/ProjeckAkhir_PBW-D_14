<?php
session_start();
require '../db_config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data dari transaksi
$stmt_trx_pemasukan = $pdo->prepare("SELECT SUM(jumlah) FROM transaksi WHERE user_id = ? AND jenis = 'Pemasukan'");
$stmt_trx_pemasukan->execute([$user_id]);
$trx_pemasukan = $stmt_trx_pemasukan->fetchColumn() ?: 0;

$stmt_trx_pengeluaran = $pdo->prepare("SELECT SUM(jumlah) FROM transaksi WHERE user_id = ? AND jenis = 'Pengeluaran'");
$stmt_trx_pengeluaran->execute([$user_id]);
$trx_pengeluaran = $stmt_trx_pengeluaran->fetchColumn() ?: 0;

// Ambil data dari laporan
$stmt_laporan = $pdo->prepare("SELECT SUM(pemasukan) as pemasukan, SUM(pengeluaran) as pengeluaran FROM laporan WHERE user_id = ?");
$stmt_laporan->execute([$user_id]);
$laporan = $stmt_laporan->fetch();
$laporan_pemasukan = $laporan['pemasukan'] ?: 0;
$laporan_pengeluaran = $laporan['pengeluaran'] ?: 0;

// Hitung total
$total_pemasukan = $trx_pemasukan + $laporan_pemasukan;
$total_pengeluaran = $trx_pengeluaran + $laporan_pengeluaran;
$saldo = $total_pemasukan - $total_pengeluaran;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-[#EFE7D9] min-h-screen">

  <h1 class="text-2xl font-bold mb-6 text-[#A0A58C]">Dashboard Manajemen Keuangan</h1>

  <!-- Ringkasan -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
    <div class="bg-white p-4 rounded-xl shadow border border-[#C5CBAF]">
      <span class="block text-gray-600 mb-1">Saldo Saat Ini</span>
      <span class="text-[#A0A58C] font-semibold text-xl">Rp <?= number_format($saldo, 0, ',', '.') ?></span>
    </div>
    <div class="bg-white p-4 rounded-xl shadow border border-[#C5CBAF]">
      <span class="block text-gray-600 mb-1">Total Pemasukan</span>
      <span class="text-[#78B678] font-semibold text-xl">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></span>
    </div>
    <div class="bg-white p-4 rounded-xl shadow border border-[#C5CBAF]">
      <span class="block text-gray-600 mb-1">Total Pengeluaran</span>
      <span class="text-[#F28482] font-semibold text-xl">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></span>
    </div>
  </div>

  <!-- Navigasi bawah -->
  <div class="fixed bottom-4 left-1/2 -translate-x-1/2 transform bg-white shadow-lg rounded-full px-6 py-2 flex gap-3 border border-[#C5CBAF]">
    <a href="../sasaran/sasaran.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">Sasaran</a>
    <a href="../laporan/laporan.php" class="bg-[#A0A58C] text-white px-4 py-2 rounded-full hover:bg-[#C5CBAF] text-sm font-medium">Laporan</a>
    <a href="../anggaran/anggaran.php" class="bg-[#A0A58C] text-white px-4 py-2 rounded-full hover:bg-[#C5CBAF] text-sm font-medium">Anggaran</a>
    <a href="../transaksi/tambah_transaksi.php" class="bg-[#F28482] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">Transaksi</a>
    <a href="../auth/logout.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">Logout</a>
  </div>

</body>
</html>
