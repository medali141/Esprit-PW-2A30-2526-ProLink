<?php
// expects $posts and $commentsByPost from controller
?>
<section class="hero">
  <div class="hero-inner container">
    <div class="hero-left">
      <h1>Bienvenue<br/>sur ProLink</h1>
      <p>Connectez-vous avec des professionnels, partagez vos projets et développez votre réseau.</p>
    </div>
    <div class="hero-right">
      <a class="btn primary" href="#new-post">Commencer</a>
    </div>
  </div>
</section>

<section class="container forum-section">
  <div class="new-post" id="new-post">
    <h2>Créer un post</h2>
    <form id="create-post-form" action="index.php?action=create_post" method="post">
      <input type="text" name="title" id="post-title" placeholder="Titre" />
      <textarea name="content" id="post-content" placeholder="Votre contenu"></textarea>
      <button class="btn" type="submit">Publier</button>
    </form>
  </div>

  <?php include __DIR__ . '/_posts_fragment.php'; ?>
</section>
