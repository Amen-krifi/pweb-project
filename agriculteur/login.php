<?php
session_start();
include('../connexion.php');

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pseudo   = trim($_POST['pseudo']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM agriculteur WHERE pseudo = ? AND password = ?");
    $stmt->execute([$pseudo, $password]);
    $agri = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($agri) {
        $_SESSION['agriculteur'] = $agri;
        header("Location: mes_offres.php");
        exit();
    } else {
        $erreur = "Pseudo ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion Agriculteur</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 400px;">
  <div class="card shadow p-4">
    <h3 class="text-center text-success mb-4"> Connexion Agriculteur</h3>

    <?php if($erreur): ?>
      <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label>Pseudo</label>
        <input type="text" name="pseudo" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Mot de passe</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success w-100">Se connecter</button>
    </form>

    <p class="text-center mt-3">
      Pas encore inscrit ? <a href="inscription.php">S'inscrire</a>
    </p>
  </div>
</div>

</body>
</html>