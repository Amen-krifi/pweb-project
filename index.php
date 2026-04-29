<?php session_start(); ?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Uber-Cueillette</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #e8f5e9, #fff8e1);
      min-height: 100vh;
    }
    .hero {
      padding: 80px 20px;
      text-align: center;
    }
    .hero h1 {
      font-size: 3rem;
      font-weight: bold;
      color: #2e7d32;
    }
    .hero p {
      font-size: 1.2rem;
      color: #555;
      margin-bottom: 40px;
    }
    .card-role {
      border: none;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }
    .card-role:hover {
      transform: translateY(-5px);
    }
    .icon-big {
      font-size: 60px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success px-4">
  <span class="navbar-brand fw-bold">Uber-Cueillette</span>
  <span class="text-white ms-auto">
    Plateforme de récolte en Tunisie 🇹🇳
  </span>
</nav>

<!-- Hero Section -->
<div class="hero">
  <h1>Uber-Cueillette</h1>
  <p>
    La plateforme qui met en relation les <strong>agriculteurs</strong> 
    et les <strong>ouvriers de récolte</strong><br>
    pour les saisons d'olives, agrumes, tomates et plus encore.
  </p>

  <!-- Cards -->
  <div class="container">
    <div class="row justify-content-center g-4">

      <!-- Agriculteur -->
      <div class="col-md-4">
        <div class="card-role bg-white">
          <div class="icon-big"></div>
          <h4 class="text-success fw-bold">Je suis Agriculteur</h4>
          <p class="text-muted mb-4">
            Publiez vos offres de récolte, gérez vos ouvriers 
            et notez leur travail.
          </p>
          <a href="agriculteur/login.php" class="btn btn-success btn-lg w-100 mb-2">
            Se connecter
          </a>
          <a href="agriculteur/inscription.php" class="btn btn-outline-success w-100">
            S'inscrire
          </a>
        </div>
      </div>

      <!-- Ouvrier -->
      <div class="col-md-4">
        <div class="card-role bg-white">
          <div class="icon-big"></div>
          <h4 class="text-warning fw-bold">Je suis Ouvrier</h4>
          <p class="text-muted mb-4">
            Consultez les offres disponibles, postulez 
            et suivez vos chantiers et gains.
          </p>
          <a href="ouvrier/login.php" class="btn btn-warning btn-lg w-100 mb-2">
            Se connecter
          </a>
          <a href="ouvrier/inscription.php" class="btn btn-outline-warning w-100">
            S'inscrire
          </a>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Footer -->
<footer class="text-center text-muted py-4 mt-4 border-top">
  <p> Uber-Cueillette — ISG Tunis 2025/2026</p>
</footer>

</body>
</html>