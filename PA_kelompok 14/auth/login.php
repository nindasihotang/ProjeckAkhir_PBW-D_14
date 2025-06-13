<?php
session_start();
require '../db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $password = $_POST["password"];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user["password"])) {
    $_SESSION["user_id"] = $user["id"];
    header("Location: ../dashboard/dashboard.php");
    exit;
  } else {
    $error = "Login gagal. Periksa kembali username dan password Anda.";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#EFE7D9] flex items-center justify-center min-h-screen">
  <form method="POST" class="bg-white p-6 rounded-lg shadow-lg w-80 border border-[#C5CBAF]">
    <h2 class="text-2xl font-bold text-[#A0A58C] mb-4 text-center">Masuk ke Akun</h2>
    <?php if (!empty($error)) echo "<p class='text-[#F78E79] mb-3 text-sm'>$error</p>"; ?>

    <input name="username" type="text" placeholder="Username" class="w-full p-2 mb-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#C5CBAF]" required/>

    <input name="password" type="password" placeholder="Password" class="w-full p-2 mb-4 border rounded focus:outline-none focus:ring-2 focus:ring-[#C5CBAF]" required/>

    <button type="submit" class="w-full bg-[#A0A58C] hover:bg-[#C5CBAF] text-white p-2 rounded transition duration-200">Login</button>

    <p class="text-sm text-center mt-4">
      Belum punya akun? <a href="register.php" class="text-[#F78E79] hover:underline">Daftar di sini</a>
    </p>
  </form>
</body>
</html>
