<?php
$cardImage = $item['photo_path'] ?: 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80';
?>
<article class="review-card">
  <a class="card-image-link" href="?page=review&id=<?= (int) $item['id'] ?>"><img class="card-image" src="<?= e($cardImage) ?>" alt="<?= e($item['dish_name']) ?> from <?= e($item['restaurant_name']) ?>" loading="lazy"><span class="image-rating"><span>★</span> <?= (int) $item['rating'] ?>.0</span></a>
  <div class="card-content">
    <div class="restaurant-meta"><?= e($item['restaurant_name']) ?> <span>·</span> <?= e($item['cuisine']) ?></div>
    <h3><a href="?page=review&id=<?= (int) $item['id'] ?>"><?= e($item['dish_name']) ?></a></h3>
    <p class="card-quote">“<?= e(mb_strimwidth($item['body'], 0, 115, '…')) ?>”</p>
    <div class="card-bottom"><span class="review-author"><span class="author-avatar"><?= e(strtoupper(substr($item['username'] ?? 'F', 0, 1))) ?></span><?= e($item['username'] ?? 'food_friend') ?></span><span class="helpful-count">♡ <?= (int) $item['helpful_count'] ?> helpful</span></div>
  </div>
</article>
