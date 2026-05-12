<?php
// expects $post
?>
<h2>Modifier le post</h2>
<form id="update-post-form" action="index.php?action=update_post" method="post">
  <input type="hidden" name="id" value="<?= $post['id'] ?>">
  <input type="text" name="title" id="post-title" value="<?= htmlspecialchars($post['title']) ?>" />
  <textarea name="content" id="post-content"><?= htmlspecialchars($post['content']) ?></textarea>
  <button class="btn" type="submit">Enregistrer</button>
</form>
