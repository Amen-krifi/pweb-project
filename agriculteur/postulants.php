<?php
session_start();
include('../connexion.php');

if (!isset($_SESSION['agriculteur'])) {
    header("Location: login.php");
    exit();
}

$id_offre = $_GET['id'];

// Accepter ou refuser un postulant
if (isset($_GET['action']) && isset($_GET['id_candidature'])) {
    $action = $_GET['action'];
    $id_candidature = $_GET['id_candidature'];

    if ($action == 'accepter') {
        // Vérifier si le nombre max d'ouvriers n'est pas atteint
        $offre_check = $conn->prepare("
            SELECT o.nombre_ouvriers,
                   (SELECT COUNT(*) FROM candidature 
                    WHERE id_offre = o.id_offre AND decision = 'accepte') AS nb_acceptes
            FROM offre o WHERE o.id_offre = ?
        ");
        $offre_check->execute([$id_offre]);
        $offre_data = $offre_check->fetch(PDO::FETCH_ASSOC);

        if ($offre_data['nb_acceptes'] < $offre_data['nombre_ouvriers']) {
            $conn->prepare("UPDATE candidature SET decision = 'accepte' 
                           WHERE id_candidature = ?")
                 ->execute([$id_candidature]);
        } else {
            $erreur = "Nombre maximum d'ouvriers atteint, offre clôturée !";
        }

    } elseif ($action == 'refuser') {
        $conn->prepare("UPDATE candidature SET decision = 'refuse' 
                       WHERE id_candidature = ?")
             ->execute([$id_candidature]);
    }

    header("Location: postulants.php?id=$id_offre");
    exit();
}

// Récupérer les infos de l'offre
$stmt = $conn->prepare("
    SELECT o.*, tf.libelle AS fruit, g.libelle AS gouv
    FROM offre o
    JOIN type_fruit tf ON o.id_type_fruit = tf.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    WHERE o.id_offre = ?
");
$stmt->execute([$id_offre]);
$offre = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer la liste des postulants avec leur moyenne
$stmt2 = $conn->prepare("
    SELECT c.*, 
           ou.nom, ou.prenom, ou.photo, ou.description,
           (SELECT AVG(note) FROM candidature 
            WHERE id_ouvrier = ou.id_ouvrier 
            AND note IS NOT NULL) AS moyenne_notes,
           (SELECT GROUP_CONCAT(commentaire SEPARATOR ' | ') 
            FROM candidature 
            WHERE id_ouvrier = ou.id_ouvrier 
            AND commentaire IS NOT NULL) AS commentaires
    FROM candidature c
    JOIN ouvrier ou ON c.id_ouvrier = ou.id_ouvrier
    WHERE c.id_offre = ?
    ORDER BY c.date_candidature DESC
");
$stmt2->execute([$id_offre]);
$postulants = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Nombre acceptés
$nb_acceptes = $conn->prepare("
    SELECT COUNT(*) FROM candidature 
    WHERE id_offre = ? AND decision = 'accepte'
");
$nb_acceptes->execute([$id_offre]);
$nb_acceptes = $nb_acceptes->fetchColumn();

$cloturee = $nb_acceptes >= $offre['nombre_ouvriers'] || 
            $offre['date_limite'] < date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Postulants</title>
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

<div class="container mt-4">

  <!-- Infos de l'offre -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-success text-white">
      <strong> Offre : <?= $offre['fruit'] ?> — <?= $offre['gouv'] ?></strong>
      <?php if($cloturee): ?>
        <span class="badge bg-danger ms-2">Clôturée</span>
      <?php else: ?>
        <span class="badge bg-light text-success ms-2">Active</span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <p> Du <?= $offre['date_debut'] ?> au <?= $offre['date_fin'] ?></p>
      <p> <?= $offre['prix_journee'] ?> DT/jour</p>
      <p> <?= $nb_acceptes ?> / <?= $offre['nombre_ouvriers'] ?> ouvriers acceptés</p>
    </div>
  </div>

  <?php if(isset($erreur)): ?>
    <div class="alert alert-danger"><?= $erreur ?></div>
  <?php endif; ?>

  <h5 class="mb-3"> Liste des postulants (<?= count($postulants) ?>)</h5>

  <?php if(count($postulants) == 0): ?>
    <div class="alert alert-info">Aucun postulant pour cette offre.</div>
  <?php endif; ?>

  <?php foreach($postulants as $p): ?>
    <div class="card mb-3 shadow-sm">
      <div class="card-body d-flex align-items-start gap-3">

        <!-- Photo -->
        <div>
          <?php if($p['photo']): ?>
            <img src="data:image/jpeg;base64,<?= base64_encode($p['photo']) ?>"
                 width="80" height="80" 
                 style="border-radius:50%; object-fit:cover;">
          <?php else: ?>
            <div style="width:80px;height:80px;border-radius:50%;
                        background:#ccc;display:flex;align-items:center;
                        justify-content:center;font-size:30px;">👤</div>
          <?php endif; ?>
        </div>

        <!-- Infos -->
        <div class="flex-grow-1">
          <h5><?= $p['prenom'] . ' ' . $p['nom'] ?></h5>
          <p class="text-muted mb-1"> <?= $p['description'] ?></p>

          <!-- Moyenne des notes -->
          <?php if($p['moyenne_notes'] !== null): ?>
            <p> Moyenne : <strong><?= number_format($p['moyenne_notes'], 1) ?>/10</strong></p>
          <?php else: ?>
            <p> Pas encore noté</p>
          <?php endif; ?>

          <!-- Commentaires anciens agriculteurs -->
          <?php if($p['commentaires']): ?>
            <p class="text-muted small"> <?= $p['commentaires'] ?></p>
          <?php endif; ?>

          <!-- Statut et boutons -->
          <div class="d-flex align-items-center gap-2 mt-2">

            <?php if($p['decision'] == 'encours'): ?>
              <span class="badge bg-warning text-dark">En cours</span>
              <?php if(!$cloturee): ?>
                <a href="postulants.php?id=<?= $id_offre ?>&action=accepter&id_candidature=<?= $p['id_candidature'] ?>"
                   class="btn btn-success btn-sm"
                   onclick="return confirm('Accepter ce postulant ?')">
                   Accepter
                </a>
                <a href="postulants.php?id=<?= $id_offre ?>&action=refuser&id_candidature=<?= $p['id_candidature'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Refuser ce postulant ?')">
                   Refuser
                </a>
              <?php endif; ?>

            <?php elseif($p['decision'] == 'accepte'): ?>
              <span class="badge bg-success"> Accepté</span>

            <?php elseif($p['decision'] == 'refuse'): ?>
              <span class="badge bg-danger"> Refusé</span>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

</div>
</body>
</html>