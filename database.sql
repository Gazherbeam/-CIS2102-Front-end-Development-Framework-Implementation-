CREATE DATABASE IF NOT EXISTS restospect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restospect;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    username VARCHAR(40) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('member', 'admin') NOT NULL DEFAULT 'member',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS restaurants (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    cuisine VARCHAR(80) NOT NULL,
    neighborhood VARCHAR(100) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_restaurant_name_neighborhood (name, neighborhood)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    display_name VARCHAR(40) DEFAULT NULL,
    dish_name VARCHAR(100) NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    body VARCHAR(500) NOT NULL,
    photo_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_review_restaurant FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_review_restaurant (restaurant_id),
    INDEX idx_review_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS review_votes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    review_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    vote TINYINT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_review_vote CHECK (vote IN (-1, 1)),
    CONSTRAINT fk_vote_review FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_review_vote_user (review_id, user_id),
    INDEX idx_review_vote (review_id, vote)
) ENGINE=InnoDB;

INSERT IGNORE INTO restaurants (id, name, cuisine, neighborhood, address) VALUES
    (1, 'Sunrise Corner Cafe', 'American', 'Downtown', '14 Market Street'),
    (2, 'Olive Garden', 'Italian', 'Westside', '28 Willow Avenue'),
    (3, 'Smokehouse 88', 'American', 'Riverside', '88 River Road'),
    (4, 'Juniper & Rice', 'Asian', 'Arts District', '6 Juniper Lane'),
    (5, 'Little Hearth', 'Bakery', 'Old Town', '202 Main Street'),
    (6, 'The Green Spoon', 'Vegetarian', 'North Park', '17 Garden Walk');

INSERT IGNORE INTO reviews (id, restaurant_id, display_name, dish_name, rating, body, photo_path, created_at) VALUES
    (1, 1, 'food_critic', 'Bacon & Egg Sandwich', 5, 'Simple but satisfying. The bacon was crispy, the egg was flavorful, and the toasted bread gave it a nice crunch. A lovely little breakfast worth the stop.', 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=900&q=80', '2026-09-17 09:00:00'),
    (2, 6, 'maya.eats', 'Veggie Rice Pot', 5, 'Comforting, colorful, and full of fresh vegetables. The ginger dressing brings everything together without overpowering the rice. Great lunch portion, too.', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=80', '2026-09-19 12:30:00'),
    (3, 3, 'weekend_bites', 'Smokehouse Cheeseburger', 4, 'The patty had a proper char and the house pickles cut through the rich cheese. Bring an appetite—the fries are crisp and the portions are generous.', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=900&q=80', '2026-09-21 17:45:00'),
    (4, 2, 'ninaeats', 'Sunday Tomato Rigatoni', 5, 'A slow-cooked tomato sauce with a little sweetness and plenty of fresh basil. The pasta was perfectly firm and the warm bread made it hard to leave a bite behind.', 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?auto=format&fit=crop&w=900&q=80', '2026-09-23 13:15:00'),
    (5, 4, 'savor_sam', 'Crispy Tofu Bowl', 4, 'The tofu stays crunchy under a bright sesame dressing, with enough pickled vegetables to keep every bite fresh. Ask for the chili oil on the side.', 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=80', '2026-09-25 18:20:00'),
    (6, 5, 'breadandbutter', 'Brown Butter Morning Bun', 5, 'Warm from the oven, flaky at the edges, and generously dusted with cinnamon sugar. Pair it with a coffee and arrive early before they sell out.', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=900&q=80', '2026-09-27 08:10:00'),
    (7, 1, 'tableforone', 'Buttermilk Pancakes', 4, 'Soft in the middle with crisp, buttery edges. The berry compote tastes homemade and is not too sweet. A solid weekend breakfast, though the line can be long.', 'https://images.unsplash.com/photo-1528207776546-365bb710ee93?auto=format&fit=crop&w=900&q=80', '2026-09-29 10:45:00'),
    (8, 3, 'local_foodie', 'Smoked Mac & Cheese', 4, 'Creamy, smoky, and topped with a crunchy crumb. It is rich enough to share, and it pairs especially well with the house barbecue sauce.', 'https://images.unsplash.com/photo-1543339494-b4cd4f7ba686?auto=format&fit=crop&w=900&q=80', '2026-10-01 19:00:00'),
    (9, 6, 'plantplate', 'Crispy Cauliflower Tacos', 5, 'The charred cauliflower has a lovely smoky edge, and the lime slaw keeps everything bright. Two tacos make a light meal; three make it just right.', 'https://images.unsplash.com/photo-1552332386-f8dd00dc2f85?auto=format&fit=crop&w=900&q=80', '2026-10-03 14:25:00');
