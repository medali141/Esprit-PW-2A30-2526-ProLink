<?php
// Posts fragment used for AJAX refresh
// expects $posts and $commentsByPost
?>
<div class="posts">
    <?php if (empty($posts)): ?>
      <p>Aucun post pour l'instant.</p>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
      <article class="post-card">
        <header>
          <h3><?= htmlspecialchars($post['title']) ?></h3>
          <div class="meta">par <?= htmlspecialchars($post['author']) ?> — <?= htmlspecialchars($post['createdAt']) ?></div>
        </header>
        <div class="content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
        <div class="post-actions">
          <button class="react-btn" data-post-id="<?= $post['id'] ?>" data-type="like">Like (<span class="reactions-count" data-post-id="<?= $post['id'] ?>"><?= $post['reactions_count'] ?></span>)</button>
          <button class="repost-btn" data-post-id="<?= $post['id'] ?>">Repost (<span class="reposts-count" data-post-id="<?= $post['id'] ?>"><?= $post['reposts_count'] ?></span>)</button>
          <a href="index.php?action=update_post&id=<?= $post['id'] ?>" class="btn small">Edit</a>
          <form class="inline" action="index.php?action=delete_post" method="post" onsubmit="return confirm('Supprimer ce post ?');">
            <input type="hidden" name="id" value="<?= $post['id'] ?>">
            <button class="btn small danger" type="submit">Delete</button>
          </form>
        </div>

        <div class="comments">
          <h4>Commentaires</h4>
          <?php $cList = $commentsByPost[$post['id']] ?? []; ?>
          <?php if (empty($cList)): ?>
            <p class="muted">Aucun commentaire.</p>
          <?php else: ?>
            <?php foreach ($cList as $c): ?>
              <div class="comment">
                <div class="comment-meta"><?= htmlspecialchars($c['author']) ?> — <?= htmlspecialchars($c['createdAt']) ?></div>
                <div class="comment-body"><?= nl2br(htmlspecialchars($c['content'])) ?></div>
                <div class="comment-actions">
                  <button class="react-comment-btn" data-comment-id="<?= $c['id'] ?>" data-type="like">Like (<span class="reactions-count-comment" data-comment-id="<?= $c['id'] ?>"><?= $c['reactions_count'] ?? 0 ?></span>)</button>
                  <button class="repost-comment-btn" data-comment-id="<?= $c['id'] ?>">Repost (<span class="reposts-count-comment" data-comment-id="<?= $c['id'] ?>"><?= $c['reposts_count'] ?? 0 ?></span>)</button>
                  <a href="index.php?action=update_comment&id=<?= $c['id'] ?>" class="btn small">Edit</a>
                  <form class="inline" action="index.php?action=delete_comment" method="post" onsubmit="return confirm('Supprimer ce commentaire ?');">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn small danger" type="submit">Delete</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <form class="comment-form" action="index.php?action=create_comment" method="post">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <textarea name="content" placeholder="Ajouter un commentaire..."></textarea>
            <button class="btn" type="submit">Commenter</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
