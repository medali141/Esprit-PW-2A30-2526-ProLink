<?php
// expects $post
?>
<section class="container backoffice">
  <h2>Modifier le post</h2>
  <form action="index.php?action=update_post" method="post">
    <input type="hidden" name="id" value="<?= $post['id'] ?>">
    <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>">
    <textarea name="content"><?= htmlspecialchars($post['content']) ?></textarea>
    <button class="btn" type="submit">Enregistrer</button>
  </form>
</section>
