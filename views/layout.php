<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f6f7f2">
  <meta name="description" content="Honest food reviews from people who actually tried the dish. Discover your next favorite at Restospect.">
  <title><?= e(ucfirst($page)) ?> | Restospect</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/styles.css">
  <link rel="stylesheet" href="assets/admin-mobile.css">
</head>
<body>
  <header class="topbar">
    <a class="brand" href="./" aria-label="Restospect home"><span class="brand-mark">r<span>.</span></span><span>restospect</span></a>
    <nav class="main-nav" aria-label="Main navigation">
      <a class="<?= $page === 'home' ? 'active' : '' ?>" href="./">Home</a>
      <a class="<?= $page === 'discover' ? 'active' : '' ?>" href="?page=discover">Discover</a>
      <a href="./#about">About us</a>
    </nav>
    <div class="top-actions">
      <?php if ($user): ?>
        <a class="avatar-link" href="?page=dashboard"><span class="avatar-small"><?= e(strtoupper(substr($user['username'], 0, 1))) ?></span><span><?= e($user['username']) ?></span></a>
        <form action="" method="post" class="inline-form">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="logout">
          <button class="text-button" type="submit">Log out</button>
        </form>
      <?php else: ?>
        <a class="signin-link" href="?page=auth&mode=login">Sign in</a>
        <a class="button button-dark button-small" href="?page=auth&mode=signup">Join the community <span aria-hidden="true">↗</span></a>
      <?php endif; ?>
    </div>
  </header>

  <main>
    <?php if ($flashMessage): ?>
      <div class="flash flash-<?= e($flashMessage['type']) ?>" role="status"><?= e($flashMessage['message']) ?></div>
    <?php endif; ?>
    <?php require $view; ?>
  </main>

  <footer class="site-footer">
    <a class="brand brand-footer" href="./"><span class="brand-mark">r<span>.</span></span><span>restospect</span></a>
    <p>Good food deserves an honest review.</p>
    <span>© <?= date('Y') ?> Restospect</span>
  </footer>
  <script src="assets/app.js" defer></script>
</body>
</html>
