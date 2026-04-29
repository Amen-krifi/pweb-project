<?php
session_start();
include('../connexion.php');

if (!isset($_SESSION['ouvrier'])) {
    header("Location: login.php");
    exit();
}

$id_ouvrier = $_SESSION['ouvrier']['id_ouvrier'];

// Postuler
if (isset($_GET['postuler'])) {
    $id_offre = $_GET['postuler'];

    // Vérifier qu'il n'a pas déjà postulé
    $check = $conn->prepare("SELECT * FROM candidature WHERE id_offre = ? AND id_ouvrier = ?");
    $check->execute([$id_offre, $id_ouvrier]);

    if ($check->rowCount() == 0) {
        $stmt = $conn->prepare("INSERT INTO candidature (id_offre, id_ouvrier, decision) VALUES (?, ?, 'encours')");
        $stmt->execute([$id_offre, $id_ouvrier]);
    }
    header("Location: offres.php");
    exit();
}

// Filtres
$where = "WHERE o.date_limite >= CURDATE()";
$params = [];

if (!empty($_GET['fruit'])) {
    $where .= " AND o.id_type_fruit = ?";
    $params[] = $_GET['fruit'];
}
if (!empty($_GET['gouvernorat'])) {
    $where .= " AND o.id_gouvernorat = ?";
    $params[] = $_GET['gouvernorat'];
}

// Tri par prix
$order = "ORDER BY o.prix_journee ASC";
if (!empty($_GET['tri']) && $_GET['tri'] == 'desc') {
    $order = "ORDER BY o.prix_journee DESC";
}

// Récupérer les offres
$sql = "
    SELECT o.*, 
           tf.libelle AS fruit, 
           g.libelle AS gouv,
           ag.nom AS ag_nom, ag.prenom AS ag_prenom,
           (SELECT COUNT(*) FROM candidature 
            WHERE id_offre = o.id_offre AND decision = 'accepte') AS nb_acceptes,
           (SELECT id_candidature FROM candidature 
            WHERE id_offre = o.id_offre AND id_ouvrier = $id_ouvrier) AS deja_postule
    FROM offre o
    JOIN type_fruit tf ON o.id_type_fruit = tf.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    JOIN agriculteur ag ON o.id_agriculteur = ag.id_agriculteur
    $where
    $order
";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$offres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Listes pour les filtres
$fruits       = $conn->query("SELECT * FROM type_fruit")->fetchAll(PDO::FETCH_ASSOC);
$gouvernorats = $conn->query("SELECT * FROM gouvernorat")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Offres de récolte</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-warning px-4">
  <span class="navbar-brand text-dark"> Uber-Cueillette</span>
  <div>
    <span class="me-3"> <?= $_SESSION['ouvrier']['prenom'] . ' ' . $_SESSION['ouvrier']['nom'] ?></span>
    <a href="profil.php" class="btn btn-light btn-sm me-2"> Mon profil</a>
    <a href="mes_candidatures.php" class="btn btn-light btn-sm me-2"> Mes candidatures</a>
    <a href="mes_chantiers.php" class="btn btn-light btn-sm me-2"> Mes chantiers</a>
    <a href="../deconnexion.php" class="btn btn-outline-dark btn-sm">Déconnexion</a>
  </div>
</nav>

<div class="container mt-4">
  <h4 class="mb-3"> Offres de récolte disponibles</h4>

  <!-- Filtres -->
  <form method="GET" class="card p-3 mb-4 shadow-sm">
    <div class="row g-2">

      <div class="col-md-3">
        <select name="fruit" class="form-select">
          <option value="">-- Tous les fruits --</option>
          <?php foreach($fruits as $f): ?>
            <option value="<?= $f['id_type_fruit'] ?>"
              <?= (!empty($_GET['fruit']) && $_GET['fruit'] == $f['id_type_fruit']) ? 'selected' : '' ?>>
              <?= $f['libelle'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <select name="gouvernorat" class="form-select">
          <option value="">-- Tous les gouvernorats --</option>
          <?php foreach($gouvernorats as $g): ?>
            <option value="<?= $g['id_gouvernorat'] ?>"
              <?= (!empty($_GET['gouvernorat']) && $_GET['gouvernorat'] == $g['id_gouvernorat']) ? 'selected' : '' ?>>
              <?= $g['libelle'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <select name="tri" class="form-select">
          <option value="asc" <?= (empty($_GET['tri']) || $_GET['tri'] == 'asc') ? 'selected' : '' ?>>
            Prix croissant ↑
          </option>
          <option value="desc" <?= (!empty($_GET['tri']) && $_GET['tri'] == 'desc') ? 'selected' : '' ?>>
            Prix décroissant ↓
          </option>
        </select>
      </div>

      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-warning w-100"> Filtrer</button>
        <a href="offres.php" class="btn btn-secondary w-100">Reset</a>
      </div>

    </div>
  </form>

  <!-- Liste des offres -->
  <?php if(count($offres) == 0): ?>
    <div class="alert alert-info">Aucune offre disponible pour le moment.</div>
  <?php endif; ?>

  <?php foreach($offres as $offre): ?>
    <?php
      $places_restantes = $offre['nombre_ouvriers'] - $offre['nb_acceptes'];
    ?>
    <div class="card mb-3 shadow-sm">
      <div class="card-header d-flex justify-content-between">
        <strong> <?= $offre['fruit'] ?> — <?= $offre['gouv'] ?></strong>
        <span class="text-success fw-bold"><?= $offre['prix_journee'] ?> DT/jour</span>
      </div>
      <div class="card-body">
        <p> <?= $offre['adresse'] ?></p>
        <p> Du <strong><?= $offre['date_debut'] ?></strong> au <strong><?= $offre['date_fin'] ?></strong></p>
        <p> Agriculteur : <?= $offre['ag_prenom'] . ' ' . $offre['ag_nom'] ?></p>
        <p> Places restantes : <strong><?= $places_restantes ?></strong></p>
        <p> Date limite : <?= $offre['date_limite'] ?></p>

        <?php if($offre['deja_postule']): ?>
          <button class="btn btn-secondary btn-sm" disabled> Déjà postulé</button>
        <?php elseif($places_restantes <= 0): ?>
          <button class="btn btn-danger btn-sm" disabled> Complet</button>
        <?php else: ?>
          <a href="offres.php?postuler=<?= $offre['id_offre'] ?>"
             class="btn btn-warning btn-sm"
             onclick="return confirm('Postuler à cette offre ?')">
             Postuler
          </a>
        <?php endif; ?>

      </div>
    </div>
  <?php endforeach; ?>

</div>
</body>
</html>