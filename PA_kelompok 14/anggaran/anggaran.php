<?php
session_start();
require '../db_config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Tambah anggaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
  $kategori = $_POST['kategori'];
  $jumlah = (int)$_POST['jumlah'];
  $tanggal = $_POST['tanggal'];

  $stmt = $pdo->prepare("INSERT INTO anggaran (kategori, jumlah, tanggal, user_id) VALUES (?, ?, ?, ?)");
  $stmt->execute([$kategori, $jumlah, $tanggal, $user_id]);

  header("Location: anggaran.php");
  exit;
}

// Update anggaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = $_POST['id'];
  $kategori = $_POST['kategori'];
  $jumlah = (int)$_POST['jumlah'];
  $tanggal = $_POST['tanggal'];

  $stmt = $pdo->prepare("UPDATE anggaran SET kategori = ?, jumlah = ?, tanggal = ? WHERE id = ? AND user_id = ?");
  $stmt->execute([$kategori, $jumlah, $tanggal, $id, $user_id]);

  header("Location: anggaran.php");
  exit;
}

// Hapus anggaran
if (isset($_GET['hapus'])) {
  $hapus_id = $_GET['hapus'];
  $stmt = $pdo->prepare("DELETE FROM anggaran WHERE id = ? AND user_id = ?");
  $stmt->execute([$hapus_id, $user_id]);

  header("Location: anggaran.php");
  exit;
}

// Ambil semua data anggaran milik user
$stmt = $pdo->prepare("SELECT * FROM anggaran WHERE user_id = ? ORDER BY tanggal DESC");
$stmt->execute([$user_id]);
$data_anggaran = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Anggaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#EFE7D9] min-h-screen flex flex-col items-center px-4 pb-24">

  <div class="w-full max-w-2xl bg-white p-6 mt-8 rounded-xl shadow border border-[#C5CBAF]">
    <h1 class="text-2xl font-bold text-center text-[#A0A58C] mb-6">Manajemen Anggaran</h1>

    <?php if (isset($_GET['edit'])):
      $edit_id = $_GET['edit'];
      $stmt = $pdo->prepare("SELECT * FROM anggaran WHERE id = ? AND user_id = ?");
      $stmt->execute([$edit_id, $user_id]);
      $row = $stmt->fetch();
    ?>
      <!-- Form Edit -->
      <form method="POST" class="bg-[#F8CDBE] p-4 rounded-xl shadow mb-6 border border-[#F78E79]">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input name="kategori" value="<?= htmlspecialchars($row['kategori']) ?>" placeholder="Kategori" class="w-full p-2 mb-3 border rounded" required>
        <input name="jumlah" type="number" value="<?= $row['jumlah'] ?>" placeholder="Jumlah" class="w-full p-2 mb-3 border rounded" required>
        <input name="tanggal" type="date" value="<?= $row['tanggal'] ?>" class="w-full p-2 mb-3 border rounded" required>
        <button name="update" class="bg-[#F28482] text-white px-4 py-2 rounded w-full hover:bg-[#F78E79]">Update</button>
        <a href="anggaran.php" class="text-sm text-blue-600 mt-2 inline-block hover:underline">Batal</a>
      </form>
    <?php else: ?>
      <!-- Form Tambah -->
      <form method="POST" class="bg-[#C5CBAF] p-4 rounded-xl shadow mb-6 border border-[#A0A58C]">
        <input name="kategori" placeholder="Kategori (contoh: Makanan)" class="w-full p-2 mb-3 border rounded" required>
        <input name="jumlah" type="number" placeholder="Jumlah Anggaran" class="w-full p-2 mb-3 border rounded" required>
        <input name="tanggal" type="date" class="w-full p-2 mb-3 border rounded" required>
        <button name="tambah" class="bg-[#A0A58C] text-white px-4 py-2 rounded w-full hover:bg-[#789262]">Simpan</button>
      </form>
    <?php endif; ?>

    <!-- Tabel Anggaran -->
    <table class="w-full bg-white border rounded shadow text-sm">
      <thead class="bg-[#F8CDBE] text-[#444]">
        <tr>
          <th class="p-3 border">Kategori</th>
          <th class="p-3 border">Jumlah</th>
          <th class="p-3 border">Tanggal</th>
          <th class="p-3 border">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data_anggaran as $row): ?>
        <tr class="hover:bg-[#F5F1E8]">
          <td class="p-3 border"><?= htmlspecialchars($row['kategori']) ?></td>
          <td class="p-3 border text-green-700">Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
          <td class="p-3 border"><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
          <td class="p-3 border text-center">
            <a href="?edit=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
            <a href="?hapus=<?= $row['id'] ?>" class="text-red-600 hover:underline ml-2" onclick="return confirm('Hapus data anggaran ini?')">Hapus</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Navigasi -->
  <div class="fixed bottom-4 left-1/2 -translate-x-1/2 transform bg-white shadow-lg rounded-full px-6 py-2 flex gap-4 border border-[#C5CBAF]">
    <a href="../dashboard/dashboard.php" class="bg-[#A0A58C] text-white px-4 py-2 rounded-full hover:bg-[#C5CBAF] text-sm font-medium">Dashboard</a>
    <a href="../sasaran/sasaran.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">Sasaran</a>
    <a href="../laporan/laporan.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">Laporan</a>
    <a href="../transaksi/tambah_transaksi.php" class="bg-[#F28482] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">Transaksi</a>
  </div>

</body>
</html>
