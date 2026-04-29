<?php
session_start();
include('../connexion.php');

if (!isset($_SESSION['agriculteur'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$erreur = "";
$id = $_SESSION['agriculteur']['id_agriculteur'];

// Modifier le profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom     = trim($_POST['nom']);
    $prenom  = trim($_POST['prenom']);
    $cin     = trim($_POST['cin']);
    $email   = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    $pseudo  = trim($_POST['pseudo']);
    $password = trim($_POST['password']);

    // Vérifier si le pseudo est déjà pris par un autre
    $check = $conn->prepare("SELECT * FROM agriculteur WHERE pseudo = ? AND id_agriculteur != ?");
    $check->execute([$pseudo, $id]);
    if ($check->rowCount() > 0) {
        $erreur = "Ce pseudo est déjà utilisé par quelqu'un d'autre.";
    } else {
        $stmt = $conn->prepare("UPDATE agriculteur 
            SET nom=?, prenom=?, CIN=?, email=?, adresse=?, pseudo=?, password=?
            WHERE id_agriculteur=?");
        $stmt->execute([$nom, $prenom, $cin, $email, $adresse, $pseudo, $password, $id]);

        // Mettre à jour la session
        $_SESSION['agriculteur']['nom']     = $nom;
        $_SESSION['agriculteur']['prenom']  = $prenom;
        $_SESSION['agriculteur']['pseudo']  = $pseudo;

        $success = "Profil modifié avec succès !";
    }
}

// Récupérer les infos actuelles
$stmt = $conn->prepare("SELECT * FROM agriculteur WHERE id_agriculteur = ?");
$stmt->execute([$id]);
$agri = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Profil</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success px-4">
  <span class="navbar-brand"> Uber-Cueillette</span>
  <div>
    <a href="mes_offres.php" class="btn btn-light btn-sm me-2"> Mes offres</a>
    <a href="ajouter_offre.php" class="btn btn-light btn-sm me-2">+ Ajouter offre</a>
    <a href="../deconnexion.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
  </div>
</nav>

<div class="container mt-4" style="max-width: 550px;">
  <div class="card shadow p-4">
    <h4 class="text-success mb-4"> Mon Profil</h4>

    <?php if($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if($erreur): ?>
      <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return valider()">

      <div class="mb-2">
        <label>Nom</label>
        <input type="text" name="nom" class="form-control"
               value="<?= $agri['nom'] ?>" required>
      </div>

      <div class="mb-2">
        <label>Prénom</label>
        <input type="text" name="prenom" class="form-control"
               value="<?= $agri['prenom'] ?>" required>
      </div>

      <div class="mb-2">
        <label>CIN (8 chiffres)</label>
        <input type="text" id="cin" name="cin" class="form-control"
               value="<?= $agri['CIN'] ?>" required>
      </div>

      <div class="mb-2">
        <label>Email</label>
        <input type="email" name="email" class="form-control"
               value="<?= $agri['email'] ?>" required>
      </div>

      <div class="mb-2">
        <label>Adresse</label>
        <input type="text" name="adresse" class="form-control"
               value="<?= $agri['adresse'] ?>" required>
      </div>

      <div class="mb-2">
        <label>Pseudo (lettres uniquement)</label>
        <input type="text" id="pseudo" name="pseudo" class="form-control"
               value="<?= $agri['pseudo'] ?>" required>
      </div>

      <div class="mb-3">
        <label>Mot de passe (min 8 caractères, finit par $ ou #)</label>
        <input type="password" id="password" name="password" class="form-control"
               value="<?= $agri['password'] ?>" required>
      </div>

      <button type="submit" class="btn btn-success w-100">
         Enregistrer les modifications
      </button>

    </form>
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