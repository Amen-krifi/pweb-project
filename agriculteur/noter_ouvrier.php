<?php
session_start();
include('../connexion.php');

if (!isset($_SESSION['agriculteur'])) {
    header("Location: login.php");
    exit();
}

$id_offre = $_GET['id'];
$success = "";

// Enregistrer les notes
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['notes'] as $id_candidature => $note) {
        $commentaire  = $_POST['commentaires'][$id_candidature];
        $remuneration = $_POST['remunerations'][$id_candidature];

        $stmt = $conn->prepare("UPDATE candidature 
            SET note = ?, commentaire = ?, remuneration = ?
            WHERE id_candidature = ?");
        $stmt->execute([$note, $commentaire, $remuneration, $id_candidature]);
    }
    $success = "Notes enregistrées avec succès !";
}

// Récupérer les ouvriers acceptés pour cette offre
$stmt = $conn->prepare("
    SELECT c.*, ou.nom, ou.prenom, ou.photo
    FROM candidature c
    JOIN ouvrier ou ON c.id_ouvrier = ou.id_ouvrier
    WHERE c.id_offre = ? AND c.decision = 'accepte'
");
$stmt->execute([$id_offre]);
$ouvriers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Infos de l'offre
$offre = $conn->prepare("
    SELECT o.*, tf.libelle AS fruit 
    FROM offre o
    JOIN type_fruit tf ON o.id_type_fruit = tf.id_type_fruit
    WHERE o.id_offre = ?
");
$offre->execute([$id_offre]);
$offre = $offre->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Noter les ouvriers</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success px-4">
  <span class="navbar-brand"> Uber-Cueillette</span>
  <div>
    <a href="mes_offres.php" class="btn btn-light btn-sm me-2">← Mes offres</a>
    <a href="../deconnexion.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
  </div>
</nav>

<div class="container mt-4" style="max-width: 700px;">
  <div class="card shadow p-4">
    <h4 class="text-success mb-1"> Noter les ouvriers</h4>
    <p class="text-muted mb-4">
      Chantier : <strong><?= $offre['fruit'] ?></strong> — 
      Du <?= $offre['date_debut'] ?> au <?= $offre['date_fin'] ?>
    </p>

    <?php if($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if(count($ouvriers) == 0): ?>
      <div class="alert alert-info">Aucun ouvrier accepté pour cette offre.</div>
    <?php else: ?>

    <form method="POST">
      <?php foreach($ouvriers as $o): ?>
        <div class="card mb-3 shadow-sm">
          <div class="card-body">

            <!-- Photo et nom -->
            <div class="d-flex align-items-center gap-3 mb-3">
              <?php if($o['photo']): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($o['photo']) ?>"
                     width="60" height="60"
                     style="border-radius:50%; object-fit:cover;">
              <?php else: ?>
                <div style="width:60px;height:60px;border-radius:50%;
                            background:#ccc;display:flex;align-items:center;
                            justify-content:center;font-size:24px;">👤</div>
              <?php endif; ?>
              <h5 class="mb-0"><?= $o['prenom'] . ' ' . $o['nom'] ?></h5>
            </div>

            <!-- Note -->
            <div class="mb-2">
              <label> Note (0 à 10)</label>
              <input type="number" 
                     name="notes[<?= $o['id_candidature'] ?>]"
                     class="form-control"
                     min="0" max="10" step="1"
                     value="<?= $o['note'] ?? '' ?>"
                     required>
            </div>

            <!-- Commentaire -->
            <div class="mb-2">
              <label> Commentaire</label>
              <textarea name="commentaires[<?= $o['id_candidature'] ?>]"
                        class="form-control" rows="2"
                        placeholder="Qualité du travail, ponctualité..."
                        ><?= $o['commentaire'] ?? '' ?></textarea>
            </div>

            <!-- Rémunération -->
            <div class="mb-2">
              <label> Rémunération accordée (DT)</label>
              <input type="number"
                     name="remunerations[<?= $o['id_candidature'] ?>]"
                     class="form-control"
                     min="0" step="0.5"
                     value="<?= $o['remuneration'] ?? '' ?>"
                     required>
            </div>

          </div>
        </div>
      <?php endforeach; ?>

      <button type="submit" class="btn btn-success w-100">
         Enregistrer les notes
      </button>
    </form>

    <?php endif; ?>
  </div>
</div>

</body>
</html>