<?php
// Backoffice header with sidebar layout
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ProLink - Backoffice</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="back-layout">
  <aside class="back-sidebar">
    <div class="back-logo">ProLink</div>
    <nav class="back-menu">
      <a href="index.php?page=backoffice" class="active"><span class="icon">🏠</span><span>Dashboard</span></a>
      <a href="index.php?page=backoffice&section=users"><span class="icon">👥</span><span>Gestion Users</span></a>
      <a href="#"><span class="icon">📁</span><span>Gestion Projets</span></a>
      <a href="#"><span class="icon">📅</span><span>Gestion Events</span></a>
      <a href="index.php?page=profile"><span class="icon">👤</span><span>Mon profil (admin)</span></a>
      <a href="#"><span class="icon">🔒</span><span>Se déconnecter</span></a>
    </nav>
  </aside>
  <main class="back-main">
    <header class="back-topbar">
      <div class="container">
        <h2>Backoffice</h2>
      </div>
    </header>
    <div class="container back-content">
