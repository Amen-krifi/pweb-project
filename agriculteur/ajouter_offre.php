<?php
session_start();
include('../connexion.php');

// Si pas connecté, rediriger vers login
if (!isset($_SESSION['agriculteur'])) {
    header("Location: login.php");
    exit();
}

$erreur = "";
$success = "";

// Récupérer les listes déroulantes
$fruits = $conn->query("SELECT * FROM type_fruit")->fetchAll(PDO::FETCH_ASSOC);
$gouvernorats = $conn->query("SELECT * FROM gouvernorat")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_type_fruit    = $_POST['type_fruit'];
    $id_gouvernorat   = $_POST['gouvernorat'];
    $adresse          = trim($_POST['adresse']);
    $date_debut       = $_POST['date_debut'];
    $date_fin         = $_POST['date_fin'];
    $nombre_ouvriers  = $_POST['nombre_ouvriers'];
    $prix_journee     = $_POST['prix_journee'];
    $date_limite      = $_POST['date_limite'];
    $id_agriculteur   = $_SESSION['agriculteur']['id_agriculteur'];

    $stmt = $conn->prepare("INSERT INTO offre 
        (id_type_fruit, id_gouvernorat, adresse, date_debut, date_fin, 
         nombre_ouvriers, prix_journee, date_limite, id_agriculteur)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $id_type_fruit, $id_gouvernorat, $adresse,
        $date_debut, $date_fin, $nombre_ouvriers,
        $prix_journee, $date_limite, $id_agriculteur
    ]);

    $success = "Offre publiée avec succès !";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter une offre</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4" style="max-width: 600px;">
  <div class="card shadow p-4">
    <h3 class="text-center text-success mb-4"> Ajouter une offre de récolte</h3>

    <?php if($erreur): ?>
      <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <?php if($success): ?>
      <div class="alert alert-success"><?= $success ?>
        <a href="mes_offres.php">Voir mes offres</a>
      </div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validerOffre()">

      <div class="mb-2">
        <label>Type de fruit</label>
        <select name="type_fruit" class="form-select" required>
          <option value="">-- Choisir --</option>
          <?php foreach($fruits as $f): ?>
            <option value="<?= $f['id_type_fruit'] ?>"><?= $f['libelle'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-2">
        <label>Gouvernorat</label>
        <select name="gouvernorat" class="form-select" required>
          <option value="">-- Choisir --</option>
          <?php foreach($gouvernorats as $g): ?>
            <option value="<?= $g['id_gouvernorat'] ?>"><?= $g['libelle'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-2">
        <label>Adresse du site de récolte</label>
        <input type="text" name="adresse" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Date de début</label>
        <input type="date" id="date_debut" name="date_debut" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Date de fin</label>
        <input type="date" id="date_fin" name="date_fin" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Date limite pour postuler</label>
        <input type="date" id="date_limite" name="date_limite" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Nombre d'ouvriers demandés</label>
        <input type="number" name="nombre_ouvriers" class="form-control" min="1" required>
      </div>

      <div class="mb-3">
        <label>Prix à la journée (DT)</label>
        <input type="number" name="prix_journee" class="form-control" 
               min="0" step="0.5" required>
      </div>

      <button type="submit" class="btn btn-success w-100">Publier l'offre</button>
      <a href="mes_offres.php" class="btn btn-secondary w-100 mt-2">Annuler</a>

    </form>
  </div>
</div>

<script>
function validerOffre() {
    const debut  = new Date(document.getElementById('date_debut').value);
    const fin    = new Date(document.getElementById('date_fin').value);
    const limite = new Date(document.getElementById('date_limite').value);
    const today  = new Date();
    today.setHours(0,0,0,0);

    if (debut < today) {
        alert(" La date de début ne peut pas être dans le passé !");
        return false;
    }
    if (fin <= debut) {
        alert(" La date de fin doit être après la date de début !");
        return false;
    }
    if (limite >= debut) {
        alert(" La date limite doit être avant la date de début du chantier !");
        return false;
    }
    return true;
}
</script>

</body>
</html>