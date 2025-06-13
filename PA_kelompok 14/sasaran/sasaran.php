<?php
session_start();
require '../db_config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
  // Belum login, redirect ke login
  header("Location: ../auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Tambah sasaran baru, kaitkan dengan user_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
  $tujuan = $_POST['tujuan'];
  $target = $_POST['target'];
  $stmt = $pdo->prepare("INSERT INTO sasaran (tujuan, target, user_id) VALUES (?, ?, ?)");
  $stmt->execute([$tujuan, $target, $user_id]);
  header("Location: sasaran.php");
  exit;
}

// Tambah saldo sasaran milik user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_saldo'])) {
  $id = $_POST['id'];
  $jumlah = $_POST['jumlah'];

  // Pastikan sasaran ini milik user ini
  $stmtCheck = $pdo->prepare("SELECT id FROM sasaran WHERE id = ? AND user_id = ?");
  $stmtCheck->execute([$id, $user_id]);
  if ($stmtCheck->rowCount() > 0) {
    $stmt = $pdo->prepare("UPDATE sasaran SET tercapai = tercapai + ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$jumlah, $id, $user_id]);
  }
  header("Location: sasaran.php");
  exit;
}

// Update sasaran milik user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = $_POST['id'];
  $tujuan = $_POST['tujuan'];
  $target = $_POST['target'];

  $stmt = $pdo->prepare("UPDATE sasaran SET tujuan = ?, target = ? WHERE id = ? AND user_id = ?");
  $stmt->execute([$tujuan, $target, $id, $user_id]);
  header("Location: sasaran.php");
  exit;
}

// Hapus sasaran milik user
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];

  // Pastikan user hanya bisa hapus sasaran miliknya
  $stmtCheck = $pdo->prepare("SELECT id FROM sasaran WHERE id = ? AND user_id = ?");
  $stmtCheck->execute([$id, $user_id]);
  if ($stmtCheck->rowCount() > 0) {
    $stmt = $pdo->prepare("DELETE FROM sasaran WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
  }
  header("Location: sasaran.php");
  exit;
}

// Ambil sasaran milik user saja
$stmt = $pdo->prepare("SELECT * FROM sasaran WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$sasaran = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Sasaran Keuangan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#F3EBDD] flex flex-col justify-center items-center relative px-4 pb-24">

  <div class="shadow rounded-lg p-6 w-full max-w-xl text-center" style="background-color:rgb(255, 255, 255);">
    <h1 class="text-2xl font-bold mb-4 text-[#4A5240]">Sasaran Keuangan</h1>

    <?php if (isset($_GET['edit'])):
      $edit_id = $_GET['edit'];
      $data = $pdo->prepare("SELECT * FROM sasaran WHERE id = ? AND user_id = ?");
      $data->execute([$edit_id, $user_id]);
      $row = $data->fetch();
      if (!$row) {
        // Kalau data tidak ditemukan / bukan milik user
        echo "<p class='text-red-600 mb-4'>Sasaran tidak ditemukan.</p>";
      }
    ?>

      <?php if ($row): ?>
      <form method="POST" class="p-4 rounded shadow mb-6" style="background-color: #F8D5C2;">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="text" name="tujuan" value="<?= htmlspecialchars($row['tujuan']) ?>" class="w-full p-2 mb-3 border rounded" required>
        <input type="number" name="target" value="<?= $row['target'] ?>" class="w-full p-2 mb-3 border rounded" required>
        <button name="update" class="text-white px-4 py-2 rounded" style="background-color: #F89C8C;">Update</button>
        <a href="sasaran.php" class="text-sm ml-4" style="color: #9AA089;">Batal</a>
      </form>
      <?php endif; ?>

    <?php else: ?>

      <form method="POST" class="p-4 rounded shadow mb-6" style="background-color: #F3EBDD;">
        <input type="text" name="tujuan" placeholder="Tujuan" class="w-full p-2 mb-3 border rounded" required>
        <input type="number" name="target" placeholder="Target Nominal" class="w-full p-2 mb-3 border rounded" required>
        <button name="tambah" class="text-white px-4 py-2 rounded w-full" style="background-color: #F89C8C;">Simpan Sasaran</button>
      </form>

    <?php endif; ?>

    <?php foreach ($sasaran as $s): 
      $progres = $s['target'] > 0 ? min(100, ($s['tercapai'] / $s['target']) * 100) : 0;
    ?>
      <div class="p-4 rounded shadow text-left mb-6" style="background-color: #F3EBDD;">
        <div class="flex justify-between items-center mb-2">
          <p class="font-semibold text-[#4A5240]"><?= htmlspecialchars($s['tujuan']) ?></p>
          <div class="space-x-2 text-sm">
            <a href="?edit=<?= $s['id'] ?>" class="hover:underline" style="color: #F89C8C;">Edit</a>
            <a href="?hapus=<?= $s['id'] ?>" class="hover:underline" style="color: #D76B61;" onclick="return confirm('Yakin hapus sasaran ini?')">Hapus</a>
          </div>
        </div>
        <div class="w-full bg-gray-200 h-4 rounded">
          <div class="h-4 rounded" style="background-color: #F89C8C; width: <?= $progres ?>%"></div>
        </div>
        <p class="text-sm mt-2 mb-3 text-[#4A5240]">Rp <?= number_format($s['tercapai'], 0, ',', '.') ?> dari Rp <?= number_format($s['target'], 0, ',', '.') ?></p>

        <form method="POST" class="flex items-center space-x-2">
          <input type="hidden" name="id" value="<?= $s['id'] ?>">
          <input type="number" name="jumlah" placeholder="Tambah saldo" class="p-2 border rounded w-full" required>
          <button name="tambah_saldo" class="text-white px-4 py-2 rounded" style="background-color: #9AA089;">Tambah</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  
<div class="fixed bottom-4 left-1/2 -translate-x-1/2 transform bg-white shadow-lg rounded-full px-6 py-2 flex gap-4 border">
  <a href="../dashboard/dashboard.php" class="text-white px-4 py-2 rounded-full text-sm font-medium" style="background-color: #9AA089;">Dashboard</a>
  <a href="../laporan/laporan.php" class="text-white px-4 py-2 rounded-full text-sm font-medium" style="background-color: #F89C8C;">Laporan</a>
  <a href="../anggaran/anggaran.php" class="text-white px-4 py-2 rounded-full text-sm font-medium" style="background-color: #F8D5C2;">Anggaran</a>
  <a href="../transaksi/tambah_transaksi.php" class="text-white px-4 py-2 rounded-full text-sm font-medium" style="background-color: #D76B61;">Transaksi</a>
</div>

</body>
</html>
