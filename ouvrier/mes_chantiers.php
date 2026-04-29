<?php
session_start();
include('../connexion.php');

if (!isset($_SESSION['ouvrier'])) {
    header("Location: login.php");
    exit();
}

$id_ouvrier = $_SESSION['ouvrier']['id_ouvrier'];

// Chantiers clôturés où l'ouvrier a été accepté
$stmt = $conn->prepare("
    SELECT c.*,
           o.date_debut, o.date_fin, o.prix_journee, o.adresse,
           tf.libelle AS fruit,
           g.libelle AS gouv,
           ag.nom AS ag_nom, ag.prenom AS ag_prenom
    FROM candidature c
    JOIN offre o ON c.id_offre = o.id_offre
    JOIN type_fruit tf ON o.id_type_fruit = tf.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    JOIN agriculteur ag ON o.id_agriculteur = ag.id_agriculteur
    WHERE c.id_ouvrier = ?
    AND c.decision = 'accepte'
    AND o.date_fin < CURDATE()
    ORDER BY o.date_fin DESC
");
$stmt->execute([$id_ouvrier]);
$chantiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul total des gains
$total_gains = 0;
foreach($chantiers as $ch) {
    if($ch['remuneration']) $total_gains += $ch['remuneration'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes chantiers</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-warning px-4">
  <span class="navbar-brand text-dark"> Uber-Cueillette</span>
  <div>
    <a href="offres.php" class="btn btn-light btn-sm me-2"> Offres</a>
    <a href="mes_candidatures.php" class="btn btn-light btn-sm me-2"> Candidatures</a>
    <a href="../deconnexion.php" class="btn btn-outline-dark btn-sm">Déconnexion</a>
  </div>
</nav>

<div class="container mt-4">
  <h4 class="mb-3"> Mes anciens chantiers</h4>

  <!-- Total gains -->
  <div class="alert alert-success">
     Total de mes gains : <strong><?= number_format($total_gains, 2) ?> DT</strong>
  </div>

  <?php if(count($chantiers) == 0): ?>
    <div class="alert alert-info">Aucun chantier terminé pour le moment.</div>
  <?php endif; ?>

  <?php foreach($chantiers as $ch): ?>
    <div class="card mb-3 shadow-sm">
      <div class="card-header">
        <strong> <?= $ch['fruit'] ?> — <?= $ch['gouv'] ?></strong>
      </div>
      <div class="card-body">
        <p> <?= $ch['adresse'] ?></p>
        <p> Du <strong><?= $ch['date_debut'] ?></strong> au <strong><?= $ch['date_fin'] ?></strong></p>
        <p> Agriculteur : <?= $ch['ag_prenom'] . ' ' . $ch['ag_nom'] ?></p>

        <hr>

        <!-- Note et commentaire -->
        <?php if($ch['note'] !== null): ?>
          <p> Note reçue : 
            <strong class="text-warning"><?= $ch['note'] ?>/10</strong>
          </p>
          <p> Commentaire : <?= $ch['commentaire'] ?? 'Aucun commentaire' ?></p>
          <p> Rémunération : <strong><?= $ch['remuneration'] ?> DT</strong></p>
        <?php else: ?>
          <div class="alert alert-warning mb-0">
             Pas encore noté par l'agriculteur
          </div>
        <?php endif; ?>

      </div>
    </div>
  <?php endforeach; ?>

</div>
</body>
</html>