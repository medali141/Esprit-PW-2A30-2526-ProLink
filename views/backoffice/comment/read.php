<?php
// expects $comments
?>
<section class="container backoffice">
  <h2>Backoffice - Comments</h2>
  <table class="admin-table">
    <thead><tr><th>ID</th><th>Post ID</th><th>Auteur</th><th>Contenu</th><th>Créé</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($comments as $c): ?>
      <tr>
        <td><?= $c['id'] ?></td>
        <td><?= $c['post_id'] ?></td>
        <td><?= htmlspecialchars($c['author']) ?></td>
        <td><?= htmlspecialchars($c['content']) ?></td>
        <td><?= htmlspecialchars($c['createdAt']) ?></td>
        <td>
          <a class="btn small" href="index.php?page=backoffice&action=update_comment&id=<?= $c['id'] ?>">Edit</a>
          <form class="inline" action="index.php?action=delete_comment" method="post" onsubmit="return confirm('Delete comment?');">
            <input type="hidden" name="id" value="<?= $c['id'] ?>" />
            <button class="btn small danger" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
