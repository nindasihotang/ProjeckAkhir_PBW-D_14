<?php
session_start();
require '../db_config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $jenis = $_POST['jenis'];
  $kategori = $_POST['kategori'];
  $jumlah = $_POST['jumlah'];
  $keterangan = $_POST['keterangan'];
  $tanggal = $_POST['tanggal'];

  if (!in_array($jenis, ['Pemasukan', 'Pengeluaran'])) {
    die("Jenis transaksi tidak valid.");
  }

  $stmt = $pdo->prepare("INSERT INTO transaksi (user_id, jenis, kategori, jumlah, keterangan, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([$user_id, $jenis, $kategori, $jumlah, $keterangan, $tanggal]);

  header("Location: tambah_transaksi.php");
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE user_id = ? ORDER BY tanggal DESC");
$stmt->execute([$user_id]);
$transaksi = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Transaksi</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#EFE7D9] flex flex-col items-center justify-center relative px-4 pb-24">

  <div class="w-full max-w-2xl bg-white p-6 rounded-xl shadow border border-[#C5CBAF]">
    <h1 class="text-2xl font-bold mb-6 text-center text-[#A0A58C]">Tambah Transaksi</h1>

    <form method="POST" class="bg-[#C5CBAF] p-4 rounded-xl shadow mb-6 border border-[#A0A58C]">
      <select name="jenis" class="w-full p-2 mb-3 border rounded" required>
        <option value="">Pilih Jenis</option>
        <option value="Pemasukan">Pemasukan</option>
        <option value="Pengeluaran">Pengeluaran</option>
      </select>
      <input name="kategori" placeholder="Kategori" class="w-full p-2 mb-3 border rounded" required>
      <input name="jumlah" type="number" placeholder="Jumlah (Rp)" class="w-full p-2 mb-3 border rounded" required>
      <input name="keterangan" placeholder="Keterangan" class="w-full p-2 mb-3 border rounded">
      <input name="tanggal" type="date" class="w-full p-2 mb-3 border rounded" required>
      <button type="submit" class="bg-[#A0A58C] text-white px-4 py-2 rounded w-full hover:bg-[#789262]">Simpan</button>
    </form>

    <h2 class="text-lg font-semibold mb-3 text-[#444]">Riwayat Transaksi</h2>
    <?php if (count($transaksi) === 0): ?>
      <p class="text-center text-gray-600">Belum ada transaksi.</p>
    <?php else: ?>
      <table class="w-full bg-white border rounded shadow text-sm">
        <thead class="bg-[#F8CDBE] text-[#444]">
          <tr>
            <th class="p-3 border">Tanggal</th>
            <th class="p-3 border">Jenis</th>
            <th class="p-3 border">Kategori</th>
            <th class="p-3 border">Jumlah</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($transaksi as $t): ?>
            <tr class="hover:bg-[#F5F1E8]">
              <td class="p-3 border"><?= date('d-m-Y', strtotime($t['tanggal'])) ?></td>
              <td class="p-3 border"><?= htmlspecialchars($t['jenis']) ?></td>
              <td class="p-3 border"><?= htmlspecialchars($t['kategori']) ?></td>
              <td class="p-3 border <?= $t['jenis'] == 'Pemasukan' ? 'text-green-600' : 'text-red-600' ?>">
                Rp <?= number_format($t['jumlah'], 0, ',', '.') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="fixed bottom-4 left-1/2 -translate-x-1/2 transform bg-white shadow-lg rounded-full px-6 py-2 flex gap-4 border border-[#C5CBAF]">
    <a href="../dashboard/dashboard.php" class="bg-[#A0A58C] text-white px-4 py-2 rounded-full hover:bg-[#C5CBAF] text-sm font-medium">Dashboard</a>
    <a href="../laporan/laporan.php" class="bg-[#C5CBAF] text-white px-4 py-2 rounded-full hover:bg-[#A0A58C] text-sm font-medium">Laporan</a>
    <a href="../sasaran/sasaran.php" class="bg-[#C5CBAF] text-white px-4 py-2 rounded-full hover:bg-[#A0A58C] text-sm font-medium">Sasaran</a>
    <a href="../anggaran/anggaran.php" class="bg-[#A0A58C] text-white px-4 py-2 rounded-full hover:bg-[#C5CBAF] text-sm font-medium">Anggaran</a>
  </div>

</body>
</html>
