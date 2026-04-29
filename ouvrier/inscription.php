<?php
session_start();
include('../connexion.php');

$erreur = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom         = trim($_POST['nom']);
    $prenom      = trim($_POST['prenom']);
    $cin         = trim($_POST['cin']);
    $email       = trim($_POST['email']);
    $description = trim($_POST['description']);
    $pseudo      = trim($_POST['pseudo']);
    $password    = trim($_POST['password']);

    // Photo
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
    }

    // Vérifier pseudo unique
    $check = $conn->prepare("SELECT * FROM ouvrier WHERE pseudo = ?");
    $check->execute([$pseudo]);

    if ($check->rowCount() > 0) {
        $erreur = "Ce pseudo est déjà utilisé, choisissez un autre.";
    } else {
        $stmt = $conn->prepare("INSERT INTO ouvrier 
            (nom, prenom, CIN, email, photo, description, pseudo, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $cin, $email, $photo, $description, $pseudo, $password]);
        $success = "Inscription réussie !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Inscription Ouvrier</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 500px;">
  <div class="card shadow p-4">
    <h3 class="text-center text-warning mb-4"> Inscription Ouvrier</h3>

    <?php if($erreur): ?>
      <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <?php if($success): ?>
      <div class="alert alert-success"><?= $success ?>
        <a href="login.php">Se connecter</a>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" onsubmit="return valider()">

      <div class="mb-2">
        <label>Nom</label>
        <input type="text" name="nom" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Prénom</label>
        <input type="text" name="prenom" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>CIN (8 chiffres)</label>
        <input type="text" id="cin" name="cin" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Photo d'identité</label>
        <input type="file" name="photo" class="form-control" accept="image/*" required>
      </div>

      <div class="mb-2">
        <label>Description (niveau éducatif, expérience...)</label>
        <textarea name="description" class="form-control" rows="3" required></textarea>
      </div>

      <div class="mb-2">
        <label>Pseudo (lettres uniquement)</label>
        <input type="text" id="pseudo" name="pseudo" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Mot de passe (min 8 caractères, finit par $ ou #)</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-warning w-100">S'inscrire</button>
    </form>

    <p class="text-center mt-3">
      Déjà inscrit ? <a href="login.php">Se connecter</a>
    </p>
  </div>
</div>

<script>
function valider() {
    const cin      = document.getElementById('cin').value;
    const pseudo   = document.getElementById('pseudo').value;
    const password = document.getElementById('password').value;

    if (!/^\d{8}$/.test(cin)) {
        alert(" CIN doit contenir exactement 8 chiffres !");
        return false;
    }
    if (!/^[a-zA-Z]+$/.test(pseudo)) {
        alert(" Pseudo doit contenir uniquement des lettres !");
        return false;
    }
    if (!/^[a-zA-Z0-9]{7,}[$#]$/.test(password)) {
        alert(" Mot de passe : min 8 caractères et doit finir par $ ou #");
        return false;
    }
    return true;
}
</script>

</body>
</html>