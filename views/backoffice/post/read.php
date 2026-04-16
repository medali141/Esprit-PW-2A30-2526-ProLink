<?php
// expects $posts
?>
<section class="container backoffice">
  <h2>Backoffice - Posts</h2>
  <table class="admin-table">
    <thead><tr><th>ID</th><th>Titre</th><th>Auteur</th><th>Créé</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
      <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['title']) ?></td>
        <td><?= htmlspecialchars($p['author']) ?></td>
        <td><?= htmlspecialchars($p['createdAt']) ?></td>
        <td>
          <a class="btn small" href="index.php?page=backoffice&action=update_post&id=<?= $p['id'] ?>">Edit</a>
          <form class="inline" action="index.php?action=delete_post" method="post" onsubmit="return confirm('Delete post?');">
            <input type="hidden" name="id" value="<?= $p['id'] ?>" />
            <button class="btn small danger" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
