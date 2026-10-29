<?php
$featuredReviews = array_slice($reviews, 0, 3);
$heroImage = 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=1400&q=85';
?>
<section class="hero wrap">
  <div class="hero-copy">
    <div class="eyebrow"><span class="eyebrow-line"></span> The good-food community</div>
    <h1>Find something<br><span class="serif-word">tasty</span> for you.</h1>
    <p class="hero-intro">Real people. Real dishes. Reviews you can actually trust. Discover your next favorite, one honest bite at a time.</p>
    <form class="hero-search" action="" method="get">
      <input type="hidden" name="page" value="discover">
      <span class="search-icon" aria-hidden="true">⌕</span>
      <input type="search" name="q" placeholder="Try a dish, restaurant, or craving…" aria-label="Search dishes and restaurants">
      <button class="button button-dark" type="submit">Find a dish <span aria-hidden="true">↗</span></button>
    </form>
    <div class="hero-proof"><div class="proof-avatars"><span>J</span><span>M</span><span>A</span><span>+</span></div><p><strong>Thoughtful reviews.</strong> Better decisions.</p></div>
  </div>
  <div class="hero-visual">
    <img src="<?= e($heroImage) ?>" alt="A spread of freshly prepared dishes on a restaurant table">
    <div class="image-label"><span class="label-star">✳</span><span>Good food, no guesswork</span></div>
    <div class="floating-note"><span class="note-icon">✦</span><span><strong>Made by food lovers</strong><small>Honest takes, always.</small></span></div>
  </div>
  <span class="hero-decoration" aria-hidden="true">✳</span>
</section>

<section class="value-strip" id="about">
  <div class="wrap value-grid">
    <article class="value-item"><span class="value-icon icon-peach">✳</span><div><h3>Honest food reviews</h3><p>Real experiences, thoughtfully shared. Know what to expect before you order.</p></div></article>
    <article class="value-item"><span class="value-icon icon-yellow">⌕</span><div><h3>Discover great dishes</h3><p>Find the hidden gems and standout plates locals can’t stop talking about.</p></div></article>
    <article class="value-item"><span class="value-icon icon-green">♡</span><div><h3>Taste before you try</h3><p>Community votes bring the most helpful, detailed reviews to the top.</p></div></article>
  </div>
</section>

<section class="section wrap">
  <div class="section-heading">
    <div><div class="eyebrow"><span class="eyebrow-line"></span> From the community</div><h2>Reviews worth<br class="mobile-break"> <span class="serif-word">a taste.</span></h2></div>
    <a class="arrow-link" href="?page=discover">Explore all reviews <span>↗</span></a>
  </div>
  <?php if ($featuredReviews): ?>
    <div class="review-grid">
      <?php foreach ($featuredReviews as $item): ?>
        <?php require __DIR__ . '/review-card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state"><span>✳</span><h3>Your next favorite is waiting.</h3><p>Be the first to share a dish you love.</p><a class="button button-dark" href="?page=auth&mode=signup">Join and write a review ↗</a></div>
  <?php endif; ?>
  <div class="section-heading restaurant-heading"><div><div class="eyebrow"><span class="eyebrow-line"></span> Rated by real people</div><h2>Neighborhood <span class="serif-word">favorites.</span></h2></div><a class="arrow-link" href="?page=discover">Browse all spots <span>↗</span></a></div>
  <div class="restaurant-grid">
    <?php foreach (array_slice($restaurantScores, 0, 4) as $spot): ?>
      <a class="restaurant-tile" href="?page=discover&q=<?= urlencode($spot['name']) ?>"><span class="restaurant-tile-icon">⌖</span><span class="restaurant-tile-copy"><strong><?= e($spot['name']) ?></strong><small><?= e($spot['cuisine']) ?> · <?= e($spot['neighborhood']) ?></small></span><span class="restaurant-score"><strong>★ <?= number_format((float) $spot['weighted_rating'], 1) ?></strong><small><?= (int) $spot['review_count'] ?> <?= (int) $spot['review_count'] === 1 ? 'review' : 'reviews' ?></small></span></a>
    <?php endforeach; ?>
  </div>
  <div class="community-cta"><div><span class="eyebrow light-eyebrow"><span class="eyebrow-line"></span> Your seat is saved</span><h2>Eat well. Share well.</h2><p>Help someone find their next favorite dish.</p></div><a class="button button-light" href="<?= $user ? '?page=submit' : '?page=auth&mode=signup' ?>">Write a review <span>↗</span></a><span class="cta-spark" aria-hidden="true">✳</span></div>
</section>
