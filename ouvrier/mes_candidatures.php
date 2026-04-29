<?php
session_start();
include('../connexion.php');

if (!isset($_SESSION['ouvrier'])) {
    header("Location: login.php");
    exit();
}

$id_ouvrier = $_SESSION['ouvrier']['id_ouvrier'];

$stmt = $conn->prepare("
    SELECT c.*, 
           o.date_debut, o.date_fin, o.prix_journee, o.adresse, o.date_limite,
           tf.libelle AS fruit,
           g.libelle AS gouv,
           ag.nom AS ag_nom, ag.prenom AS ag_prenom
    FROM candidature c
    JOIN offre o ON c.id_offre = o.id_offre
    JOIN type_fruit tf ON o.id_type_fruit = tf.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    JOIN agriculteur ag ON o.id_agriculteur = ag.id_agriculteur
    WHERE c.id_ouvrier = ?
    ORDER BY c.date_candidature DESC
");
$stmt->execute([$id_ouvrier]);
$candidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes candidatures</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-warning px-4">
  <span class="navbar-brand text-dark"> Uber-Cueillette</span>
  <div>
    <a href="offres.php" class="btn btn-light btn-sm me-2"> Offres</a>
    <a href="mes_chantiers.php" class="btn btn-light btn-sm me-2"> Mes chantiers</a>
    <a href="../deconnexion.php" class="btn btn-outline-dark btn-sm">Déconnexion</a>
  </div>
</nav>

<div class="container mt-4">
  <h4 class="mb-3"> Mes candidatures</h4>

  <?php if(count($candidatures) == 0): ?>
    <div class="alert alert-info">Vous n'avez postulé à aucune offre.</div>
  <?php endif; ?>

  <?php foreach($candidatures as $c): ?>
    <div class="card mb-3 shadow-sm">
      <div class="card-header d-flex justify-content-between">
        <strong> <?= $c['fruit'] ?> — <?= $c['gouv'] ?></strong>

        <?php if($c['decision'] == 'encours'): ?>
          <span class="badge bg-warning text-dark"> En cours</span>
        <?php elseif($c['decision'] == 'accepte'): ?>
          <span class="badge bg-success"> Accepté</span>
        <?php elseif($c['decision'] == 'refuse'): ?>
          <span class="badge bg-danger"> Refusé</span>
        <?php endif; ?>

      </div>
      <div class="card-body">
        <p> <?= $c['adresse'] ?></p>
        <p> Du <strong><?= $c['date_debut'] ?></strong> au <strong><?= $c['date_fin'] ?></strong></p>
        <p> <?= $c['prix_journee'] ?> DT/jour</p>
        <p> Agriculteur : <?= $c['ag_prenom'] . ' ' . $c['ag_nom'] ?></p>
        <p> Postulé le : <?= $c['date_candidature'] ?></p>
      </div>
    </div>
  <?php endforeach; ?>

</div>
</body>
</html>