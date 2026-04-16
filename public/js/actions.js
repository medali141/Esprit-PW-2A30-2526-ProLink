document.addEventListener('DOMContentLoaded', function () {
  function attachHandlers(root) {
    root = root || document;

    // react on posts
    root.querySelectorAll('.react-btn').forEach(function (btn) {
      btn.removeEventListener('click', btn._reactHandler);
      var handler = function () {
        var postId = this.dataset.postId;
        var type = this.dataset.type || 'like';
        fetch('index.php?action=react_post', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'post_id=' + encodeURIComponent(postId) + '&type=' + encodeURIComponent(type)
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (data && data.status === 'ok') {
            var el = document.querySelector('.reactions-count[data-post-id="' + postId + '"]');
            if (el) el.textContent = data.count;
          }
        });
      };
      btn._reactHandler = handler;
      btn.addEventListener('click', handler);
    });

    // repost posts
    root.querySelectorAll('.repost-btn').forEach(function (btn) {
      btn.removeEventListener('click', btn._repostHandler);
      var handler = function () {
        var postId = this.dataset.postId;
        fetch('index.php?action=repost_post', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'post_id=' + encodeURIComponent(postId)
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (data && data.status === 'ok') {
            var el = document.querySelector('.reposts-count[data-post-id="' + postId + '"]');
            if (el) el.textContent = data.count;
          }
        });
      };
      btn._repostHandler = handler;
      btn.addEventListener('click', handler);
    });

    // react on comments
    root.querySelectorAll('.react-comment-btn').forEach(function (btn) {
      btn.removeEventListener('click', btn._reactCommentHandler);
      var handler = function () {
        var commentId = this.dataset.commentId;
        var type = this.dataset.type || 'like';
        fetch('index.php?action=react_comment', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'comment_id=' + encodeURIComponent(commentId) + '&type=' + encodeURIComponent(type)
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (data && data.status === 'ok') {
            var el = document.querySelector('.reactions-count-comment[data-comment-id="' + commentId + '"]');
            if (el) el.textContent = data.count;
          }
        });
      };
      btn._reactCommentHandler = handler;
      btn.addEventListener('click', handler);
    });

    // repost comment
    root.querySelectorAll('.repost-comment-btn').forEach(function (btn) {
      btn.removeEventListener('click', btn._repostCommentHandler);
      var handler = function () {
        var commentId = this.dataset.commentId;
        fetch('index.php?action=repost_comment', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'comment_id=' + encodeURIComponent(commentId)
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (data && data.status === 'ok') {
            var el = document.querySelector('.reposts-count-comment[data-comment-id="' + commentId + '"]');
            if (el) el.textContent = data.count;
          }
        });
      };
      btn._repostCommentHandler = handler;
      btn.addEventListener('click', handler);
    });
  }

  function fetchAndReplacePosts() {
    fetch('index.php?action=fetch_posts_fragment')
      .then(function (r) { return r.text(); })
      .then(function (html) {
        if (!html) return;
        var container = document.querySelector('.posts');
        if (!container) return;
        // replace the whole posts container
        container.outerHTML = html;
        // reattach handlers to new content
        var newContainer = document.querySelector('.posts');
        if (newContainer) attachHandlers(newContainer);
      }).catch(function (err) { console.error('refresh error', err); });
  }

  // initial attach
  attachHandlers(document);

  // poll every 5 seconds for live updates
  setInterval(fetchAndReplacePosts, 5000);
});
