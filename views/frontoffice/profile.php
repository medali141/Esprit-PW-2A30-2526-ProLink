<?php
// expects $myPosts, $myComments, $repostedPosts, $repostedComments
?>
<section class="container">
  <h2>Mon profil</h2>
  <div class="profile-grid">
    <div class="card">
      <h3>Mes posts</h3>
      <?php if (empty($myPosts)): ?>
        <p class="muted">Aucun post publié.</p>
      <?php else: ?>
        <?php foreach ($myPosts as $p): ?>
          <article class="post-card small">
            <h4><?= htmlspecialchars($p['title']) ?></h4>
            <div class="meta"><?= htmlspecialchars($p['createdAt']) ?> — Likes: <?= $p['reactions_count'] ?></div>
            <div class="content"><?= nl2br(htmlspecialchars($p['content'])) ?></div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="card">
      <h3>Mes commentaires</h3>
      <?php if (empty($myComments)): ?>
        <p class="muted">Aucun commentaire.</p>
      <?php else: ?>
        <?php foreach ($myComments as $c): ?>
          <div class="comment">
            <div class="comment-meta">Sur: <?= htmlspecialchars($c['post_title']) ?> — <?= htmlspecialchars($c['createdAt']) ?></div>
            <div class="comment-body"><?= nl2br(htmlspecialchars($c['content'])) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="card">
      <h3>Mes reposts (posts)</h3>
      <?php if (empty($repostedPosts)): ?>
        <p class="muted">Aucun repost de post.</p>
      <?php else: ?>
        <?php foreach ($repostedPosts as $rp): ?>
          <article class="post-card small">
            <h4><?= htmlspecialchars($rp['title']) ?></h4>
            <div class="meta">Reposté: <?= htmlspecialchars($rp['createdAt']) ?></div>
            <div class="content"><?= nl2br(htmlspecialchars($rp['content'])) ?></div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="card">
      <h3>Mes reposts (comments)</h3>
      <?php if (empty($repostedComments)): ?>
        <p class="muted">Aucun repost de commentaire.</p>
      <?php else: ?>
        <?php foreach ($repostedComments as $rc): ?>
          <div class="comment">
            <div class="comment-meta">Sur: <?= htmlspecialchars($rc['post_title']) ?> — Reposté: <?= htmlspecialchars($rc['createdAt']) ?></div>
            <div class="comment-body"><?= nl2br(htmlspecialchars($rc['content'])) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
