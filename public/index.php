<?php
// Public front controller
require_once __DIR__ . '/../config/database.php';

$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? null;

if ($action) {
    $map = [
        'create_post' => __DIR__ . '/../controllers/frontoffice/post/createPostController.php',
        'read_posts' => __DIR__ . '/../controllers/frontoffice/post/readPostController.php',
        'update_post' => __DIR__ . '/../controllers/frontoffice/post/updatePostController.php',
        'delete_post' => __DIR__ . '/../controllers/frontoffice/post/deletePostController.php',
        'react_post' => __DIR__ . '/../controllers/frontoffice/post/reactPostController.php',
        'repost_post' => __DIR__ . '/../controllers/frontoffice/post/repostPostController.php',

        'create_comment' => __DIR__ . '/../controllers/frontoffice/comment/createCommentController.php',
        'update_comment' => __DIR__ . '/../controllers/frontoffice/comment/updateCommentController.php',
        'delete_comment' => __DIR__ . '/../controllers/frontoffice/comment/deleteCommentController.php',
        'react_comment' => __DIR__ . '/../controllers/frontoffice/comment/reactCommentController.php',
        'repost_comment' => __DIR__ . '/../controllers/frontoffice/comment/repostCommentController.php',
        'fetch_posts_fragment' => __DIR__ . '/../controllers/frontoffice/post/fetchPostsFragment.php',

        // backoffice
        'back_read_posts' => __DIR__ . '/../controllers/backoffice/post/readPostController.php',
        'back_update_post' => __DIR__ . '/../controllers/backoffice/post/updatePostController.php',
        'back_delete_post' => __DIR__ . '/../controllers/backoffice/post/deletePostController.php',
    ];

    if (isset($map[$action]) && file_exists($map[$action])) {
        require $map[$action];
    } else {
        header('HTTP/1.1 404 Not Found');
        echo 'Action not found';
    }
    exit;
}

switch ($page) {
    case 'forum':
        require __DIR__ . '/../controllers/frontoffice/post/readPostController.php';
        break;
    case 'profile':
      require __DIR__ . '/../controllers/frontoffice/profileController.php';
      break;
    case 'backoffice':
        require __DIR__ . '/../controllers/backoffice/post/readPostController.php';
        break;
    default:
        include __DIR__ . '/../views/layouts/header.php';
        // simple landing (connect button in header will link to forum)
        ?>
        <section class="hero container">
          <div class="hero-inner">
            <div class="hero-left">
              <h1>Bienvenue sur ProLink</h1>
              <p>Connectez-vous avec des professionnels, partagez vos projets et développez votre réseau.</p>
            </div>
            <div class="hero-right">
              <a class="btn primary" href="index.php?page=forum">Connect</a>
            </div>
          </div>
        </section>
        <?php
        include __DIR__ . '/../views/layouts/footer.php';
}
