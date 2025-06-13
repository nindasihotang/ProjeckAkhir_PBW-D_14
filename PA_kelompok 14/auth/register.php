<?php
session_start();
require '../db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $password_plain = $_POST["password"];
  $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
  try {
    $stmt->execute([$username, $password_hashed]);

    $_SESSION['username'] = $username;
    $_SESSION['user_id'] = $pdo->lastInsertId();

    header("Location: ../dashboard/dashboard.php");
    exit;
  } catch (PDOException $e) {
    $error = "Username sudah digunakan.";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Akun</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#EFE7D9] flex items-center justify-center min-h-screen">
  <form method="POST" class="bg-white p-6 rounded-lg shadow-lg w-80 border border-[#C5CBAF]">
    <h2 class="text-2xl font-bold text-[#A0A58C] mb-4 text-center">Daftar Akun</h2>

    <?php if (!empty($error)) echo "<p class='text-[#F78E79] mb-3 text-sm'>$error</p>"; ?>

    <input name="username" type="text" placeholder="Username" required
      class="w-full p-2 mb-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#C5CBAF]" />

    <input name="password" type="password" placeholder="Password" required
      class="w-full p-2 mb-4 border rounded focus:outline-none focus:ring-2 focus:ring-[#C5CBAF]" />

    <button type="submit"
      class="w-full bg-[#A0A58C] hover:bg-[#C5CBAF] text-white p-2 rounded transition duration-200">
      Register
    </button>

    <p class="text-sm text-center mt-4">
      Sudah punya akun? <a href="login.php" class="text-[#F78E79] hover:underline">Login di sini</a>
    </p>
  </form>
</body>
</html>
