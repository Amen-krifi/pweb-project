<?php
session_start();
include('../connexion.php');

if (!isset($_SESSION['agriculteur'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['agriculteur']['id_agriculteur'];

// Supprimer une offre si demandé
if (isset($_GET['supprimer'])) {
    $id_offre = $_GET['supprimer'];
    // Vérifier qu'aucun postulant
    $check = $conn->prepare("SELECT * FROM candidature WHERE id_offre = ?");
    $check->execute([$id_offre]);
    if ($check->rowCount() == 0) {
        $conn->prepare("DELETE FROM offre WHERE id_offre = ?")->execute([$id_offre]);
    }
    header("Location: mes_offres.php");
    exit();
}

// Récupérer les offres avec nombre de postulants acceptés
$stmt = $conn->prepare("
    SELECT o.*, 
           tf.libelle AS fruit, 
           g.libelle AS gouv,
           (SELECT COUNT(*) FROM candidature 
            WHERE id_offre = o.id_offre AND decision = 'accepte') AS nb_acceptes,
           (SELECT COUNT(*) FROM candidature 
            WHERE id_offre = o.id_offre) AS nb_postulants
    FROM offre o
    JOIN type_fruit tf ON o.id_type_fruit = tf.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    WHERE o.id_agriculteur = ?
    ORDER BY o.date_debut DESC
");
$stmt->execute([$id]);
$offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes offres</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success px-4">
  <span class="navbar-brand"> Uber-Cueillette</span>
  <div>
    <span class="text-white me-3">
      <?= $_SESSION['agriculteur']['prenom'] . ' ' . $_SESSION['agriculteur']['nom'] ?>
    </span>
    <a href="profil.php" class="btn btn-light btn-sm me-2"> Mon profil</a>
    <a href="ajouter_offre.php" class="btn btn-light btn-sm me-2">+ Ajouter offre</a>
    <a href="../deconnexion.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
  </div>
</nav>

<div class="container mt-4">
  <h4 class="mb-3"> Mes offres de récolte</h4>

  <?php if(count($offres) == 0): ?>
    <div class="alert alert-info">
      Vous n'avez aucune offre. 
      <a href="ajouter_offre.php">Ajouter une offre</a>
    </div>
  <?php endif; ?>

  <?php foreach($offres as $offre): ?>
    <?php
      $today = date('Y-m-d');
      // Offre clôturée si date_limite dépassée OU nombre ouvriers atteint
      $cloturee = ($offre['date_limite'] < $today) || 
                  ($offre['nb_acceptes'] >= $offre['nombre_ouvriers']);
    ?>
    <div class="card mb-3 shadow-sm">
      <div class="card-header d-flex justify-content-between">
        <strong> <?= $offre['fruit'] ?> — <?= $offre['gouv'] ?></strong>
        <?php if($cloturee): ?>
          <span class="badge bg-danger">Clôturée</span>
        <?php else: ?>
          <span class="badge bg-success">Active</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <p> <?= $offre['adresse'] ?></p>
        <p> Du <strong><?= $offre['date_debut'] ?></strong> 
              au <strong><?= $offre['date_fin'] ?></strong></p>
        <p> <strong><?= $offre['prix_journee'] ?> DT</strong>/jour</p>
        <p> Ouvriers : <?= $offre['nb_acceptes'] ?> / <?= $offre['nombre_ouvriers'] ?> acceptés</p>
        <p> Date limite pour postuler : <?= $offre['date_limite'] ?></p>

        <div class="d-flex gap-2 mt-2">
          <a href="postulants.php?id=<?= $offre['id_offre'] ?>" 
             class="btn btn-primary btn-sm">
             Postulants (<?= $offre['nb_postulants'] ?>)
          </a>

          <?php if($offre['nb_postulants'] == 0): ?>
            <a href="mes_offres.php?supprimer=<?= $offre['id_offre'] ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Supprimer cette offre ?')">
               Supprimer
            </a>
          <?php endif; ?>

          <?php if($offre['date_fin'] < $today): ?>
            <a href="noter_ouvrier.php?id=<?= $offre['id_offre'] ?>" 
               class="btn btn-warning btn-sm">
               Noter les ouvriers
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

</body>
</html>