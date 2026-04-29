<?php
session_start();
include('../connexion.php');

if (!isset($_SESSION['ouvrier'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$erreur  = "";
$id = $_SESSION['ouvrier']['id_ouvrier'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom         = trim($_POST['nom']);
    $prenom      = trim($_POST['prenom']);
    $cin         = trim($_POST['cin']);
    $email       = trim($_POST['email']);
    $description = trim($_POST['description']);
    $pseudo      = trim($_POST['pseudo']);
    $password    = trim($_POST['password']);

    // Vérifier pseudo unique
    $check = $conn->prepare("SELECT * FROM ouvrier WHERE pseudo = ? AND id_ouvrier != ?");
    $check->execute([$pseudo, $id]);

    if ($check->rowCount() > 0) {
        $erreur = "Ce pseudo est déjà utilisé.";
    } else {
        // Mise à jour avec ou sans photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            $stmt = $conn->prepare("UPDATE ouvrier 
                SET nom=?, prenom=?, CIN=?, email=?, photo=?, description=?, pseudo=?, password=?
                WHERE id_ouvrier=?");
            $stmt->execute([$nom, $prenom, $cin, $email, $photo, $description, $pseudo, $password, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE ouvrier 
                SET nom=?, prenom=?, CIN=?, email=?, description=?, pseudo=?, password=?
                WHERE id_ouvrier=?");
            $stmt->execute([$nom, $prenom, $cin, $email, $description, $pseudo, $password, $id]);
        }

        $_SESSION['ouvrier']['nom']    = $nom;
        $_SESSION['ouvrier']['prenom'] = $prenom;
        $_SESSION['ouvrier']['pseudo'] = $pseudo;
        $success = "Profil modifié avec succès !";
    }
}

// Récupérer infos actuelles
$stmt = $conn->prepare("SELECT * FROM ouvrier WHERE id_ouvrier = ?");
$stmt->execute([$id]);
$ouvrier = $stmt->fetch(PDO::FETCH_ASSOC);

// Moyenne des notes
$avg = $conn->prepare("SELECT AVG(note) as moyenne FROM candidature WHERE id_ouvrier = ? AND note IS NOT NULL");
$avg->execute([$id]);
$moyenne = $avg->fetch(PDO::FETCH_ASSOC)['moyenne'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Profil</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-warning px-4">
  <span class="navbar-brand text-dark"> Uber-Cueillette</span>
  <div>
    <a href="offres.php" class="btn btn-light btn-sm me-2"> Offres</a>
    <a href="../deconnexion.php" class="btn btn-outline-dark btn-sm">Déconnexion</a>
  </div>
</nav>

<div class="container mt-4" style="max-width: 550px;">
  <div class="card shadow p-4">
    <h4 class="text-warning mb-3"> Mon Profil</h4>

    <!-- Photo et moyenne -->
    <div class="text-center mb-3">
      <?php if($ouvrier['photo']): ?>
        <img src="data:image/jpeg;base64,<?= base64_encode($ouvrier['photo']) ?>"
             width="100" height="100"
             style="border-radius:50%; object-fit:cover;">
      <?php else: ?>
        <div style="width:100px;height:100px;border-radius:50%;
                    background:#ccc;display:flex;align-items:center;
                    justify-content:center;font-size:40px;margin:auto;"></div>
      <?php endif; ?>
      <p class="mt-2">
         Moyenne : 
        <strong><?= $moyenne ? number_format($moyenne, 1).'/10' : 'Pas encore noté' ?></strong>
      </p>
    </div>

    <?php if($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if($erreur): ?>
      <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" onsubmit="return valider()">

      <div class="mb-2">
        <label>Nom</label>
        <input type="text" name="nom" class="form-control" value="<?= $ouvrier['nom'] ?>" required>
      </div>
      <div class="mb-2">
        <label>Prénom</label>
        <input type="text" name="prenom" class="form-control" value="<?= $ouvrier['prenom'] ?>" required>
      </div>
      <div class="mb-2">
        <label>CIN</label>
        <input type="text" id="cin" name="cin" class="form-control" value="<?= $ouvrier['CIN'] ?>" required>
      </div>
      <div class="mb-2">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= $ouvrier['email'] ?>" required>
      </div>
      <div class="mb-2">
        <label>Photo (laisser vide pour garder l'actuelle)</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
      </div>
      <div class="mb-2">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3" required><?= $ouvrier['description'] ?></textarea>
      </div>
      <div class="mb-2">
        <label>Pseudo</label>
        <input type="text" id="pseudo" name="pseudo" class="form-control" value="<?= $ouvrier['pseudo'] ?>" required>
      </div>
      <div class="mb-3">
        <label>Mot de passe</label>
        <input type="password" id="password" name="password" class="form-control" value="<?= $ouvrier['password'] ?>" required>
      </div>

      <button type="submit" class="btn btn-warning w-100"> Enregistrer</button>
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