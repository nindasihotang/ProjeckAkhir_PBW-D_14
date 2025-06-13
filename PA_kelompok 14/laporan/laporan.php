<?php
session_start();
require '../db_config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['tambah'])) {
  $tanggal = $_POST['tanggal'];
  $pemasukan = $_POST['pemasukan'];
  $pengeluaran = $_POST['pengeluaran'];
  $selisih = $pemasukan - $pengeluaran;

  $stmt = $pdo->prepare("INSERT INTO laporan (tanggal, pemasukan, pengeluaran, selisih, user_id) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$tanggal, $pemasukan, $pengeluaran, $selisih, $user_id]);
  header("Location: laporan.php");
  exit;
}

if (isset($_POST['update'])) {
  $id = $_POST['id'];
  $tanggal = $_POST['tanggal'];
  $pemasukan = $_POST['pemasukan'];
  $pengeluaran = $_POST['pengeluaran'];
  $selisih = $pemasukan - $pengeluaran;

  $stmt = $pdo->prepare("UPDATE laporan SET tanggal = ?, pemasukan = ?, pengeluaran = ?, selisih = ? WHERE id = ? AND user_id = ?");
  $stmt->execute([$tanggal, $pemasukan, $pengeluaran, $selisih, $id, $user_id]);
  header("Location: laporan.php");
  exit;
}

if (isset($_GET['hapus'])) {
  $hapus_id = $_GET['hapus'];
  $stmt = $pdo->prepare("DELETE FROM laporan WHERE id = ? AND user_id = ?");
  $stmt->execute([$hapus_id, $user_id]);
  header("Location: laporan.php");
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM laporan WHERE user_id = ? ORDER BY tanggal DESC");
$stmt->execute([$user_id]);
$laporan = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Keuangan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#EFE7D9] flex flex-col items-center justify-center relative px-4 pb-24">

  <div class="w-full max-w-2xl bg-white p-6 rounded-xl shadow border border-[#C5CBAF]">
    <h1 class="text-2xl font-bold mb-6 text-center text-[#A0A58C]">Laporan Keuangan Bulanan</h1>

    <?php if (isset($_GET['edit'])):
      $edit_id = $_GET['edit'];
      $data = $pdo->prepare("SELECT * FROM laporan WHERE id = ? AND user_id = ?");
      $data->execute([$edit_id, $user_id]);
      $row = $data->fetch();
    ?>

      <form method="POST" class="bg-[#F8CDBE] p-4 rounded-xl shadow mb-6 border border-[#F78E79]">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input name="tanggal" type="date" value="<?= $row['tanggal'] ?>" class="w-full p-2 mb-3 border rounded" required>
        <input name="pemasukan" type="number" value="<?= $row['pemasukan'] ?>" class="w-full p-2 mb-3 border rounded" required>
        <input name="pengeluaran" type="number" value="<?= $row['pengeluaran'] ?>" class="w-full p-2 mb-3 border rounded" required>
        <button name="update" class="bg-[#F28482] text-white px-4 py-2 rounded w-full hover:bg-[#F78E79]">Update</button>
        <a href="laporan.php" class="text-sm text-blue-600 mt-2 inline-block hover:underline">Batal</a>
      </form>

    <?php else: ?>

      <form method="POST" class="bg-[#C5CBAF] p-4 rounded-xl shadow mb-6 border border-[#A0A58C]">
        <input name="tanggal" type="date" class="w-full p-2 mb-3 border rounded" required>
        <input name="pemasukan" type="number" placeholder="Pemasukan" class="w-full p-2 mb-3 border rounded" required>
        <input name="pengeluaran" type="number" placeholder="Pengeluaran" class="w-full p-2 mb-3 border rounded" required>
        <button name="tambah" class="bg-[#A0A58C] text-white px-4 py-2 rounded w-full hover:bg-[#789262]">Simpan</button>
      </form>

    <?php endif; ?>

    <table class="w-full bg-white border rounded shadow text-sm">
      <thead class="bg-[#F8CDBE] text-[#444]">
        <tr>
          <th class="p-3 border">Tanggal</th>
          <th class="p-3 border">Pemasukan</th>
          <th class="p-3 border">Pengeluaran</th>
          <th class="p-3 border">Selisih</th>
          <th class="p-3 border">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($laporan as $row): ?>
          <tr class="hover:bg-[#F5F1E8]">
            <td class="p-3 border"><?= htmlspecialchars($row['tanggal']) ?></td>
            <td class="p-3 border text-green-600">Rp <?= number_format($row['pemasukan'], 0, ',', '.') ?></td>
            <td class="p-3 border text-red-600">Rp <?= number_format($row['pengeluaran'], 0, ',', '.') ?></td>
            <td class="p-3 border font-semibold">Rp <?= number_format($row['selisih'], 0, ',', '.') ?></td>
            <td class="p-3 border text-center">
              <a href="?edit=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
              <a href="?hapus=<?= $row['id'] ?>" class="text-red-600 hover:underline ml-2" onclick="return confirm('Hapus laporan ini?')">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="fixed bottom-4 left-1/2 -translate-x-1/2 transform bg-white shadow-lg rounded-full px-6 py-2 flex gap-4 border border-[#C5CBAF]">
    <a href="../sasaran/sasaran.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">Sasaran</a>
    <a href="../dashboard/dashboard.php" class="bg-[#A0A58C] text-white px-4 py-2 rounded-full hover:bg-[#C5CBAF] text-sm font-medium">Dashboard</a>
    <a href="../anggaran/anggaran.php" class="bg-[#A0A58C] text-white px-4 py-2 rounded-full hover:bg-[#C5CBAF] text-sm font-medium">Anggaran</a>
    <a href="../transaksi/tambah_transaksi.php" class="bg-[#F28482] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">Transaksi</a>
  </div>

</body>
</html>
