<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    try {
        switch ($action) {
            case 'register':
                $email = trim((string) ($_POST['email'] ?? ''));
                $username = trim((string) ($_POST['username'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $confirmation = (string) ($_POST['password_confirmation'] ?? '');
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($username) < 3 || strlen($username) > 40 || strlen($password) < 8 || $password !== $confirmation) {
                    throw new RuntimeException('Use a valid email, a username of 3–40 characters, and matching passwords with at least 8 characters.');
                }
                $statement = $pdo->prepare('INSERT INTO users (email, username, password_hash) VALUES (?, ?, ?)');
                $statement->execute([$email, $username, password_hash($password, PASSWORD_DEFAULT)]);
                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => (int) $pdo->lastInsertId(), 'username' => $username];
                flash('Your account is ready. Welcome to Restospect!');
                redirect('?page=dashboard');

            case 'login':
                $statement = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE email = ? OR username = ? LIMIT 1');
                $login = trim((string) ($_POST['login'] ?? ''));
                $statement->execute([$login, $login]);
                $account = $statement->fetch();
                if (!$account || !password_verify((string) ($_POST['password'] ?? ''), $account['password_hash'])) {
                    throw new RuntimeException('That email/username and password combination did not match.');
                }
                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => (int) $account['id'], 'username' => $account['username'], 'role' => $account['role']];
                flash('Welcome back, ' . $account['username'] . '.');
                redirect('?page=dashboard');

            case 'logout':
                unset($_SESSION['user']);
                session_regenerate_id(true);
                flash('You are signed out.');
                redirect('./');

            case 'review':
            case 'update_review':
                if (!current_user()) {
                    throw new RuntimeException('Sign in before sharing a review.');
                }
                $restaurantId = filter_input(INPUT_POST, 'restaurant_id', FILTER_VALIDATE_INT);
                $dish = trim((string) ($_POST['dish_name'] ?? ''));
                $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
                $body = trim((string) ($_POST['body'] ?? ''));
                if (!$restaurantId || strlen($dish) < 2 || strlen($dish) > 100 || !$rating || $rating < 1 || $rating > 5 || strlen($body) < 20 || strlen($body) > 500) {
                    throw new RuntimeException('Add a dish, choose a rating, and write 20–500 characters about your experience.');
                }
                $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
                $existingPhoto = null;
                if ($action === 'update_review') {
                    $existingStatement = $pdo->prepare('SELECT id, photo_path FROM reviews WHERE id = ? AND user_id = ?');
                    $existingStatement->execute([$reviewId, current_user()['id']]);
                    $existingReview = $existingStatement->fetch();
                    if (!$existingReview) {
                        throw new RuntimeException('That review could not be found in your account.');
                    }
                    $existingPhoto = $existingReview['photo_path'];
                }
                $photoPath = null;
                if (!empty($_FILES['photo']['name'])) {
                    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK || $_FILES['photo']['size'] > 5 * 1024 * 1024) {
                        throw new RuntimeException('Photos must be JPG or PNG files under 5 MB.');
                    }
                    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['photo']['tmp_name']);
                    $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png'][$mime] ?? null;
                    if (!$extension) {
                        throw new RuntimeException('Photos must be JPG or PNG files under 5 MB.');
                    }
                    $uploadDirectory = __DIR__ . '/uploads';
                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0755, true);
                    }
                    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
                    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDirectory . '/' . $filename)) {
                        throw new RuntimeException('The photo could not be uploaded. Please try again.');
                    }
                    $photoPath = 'uploads/' . $filename;
                } elseif ($action === 'update_review') {
                    $photoPath = $existingPhoto;
                }
                if ($action === 'update_review') {
                    $statement = $pdo->prepare('UPDATE reviews SET restaurant_id = ?, dish_name = ?, rating = ?, body = ?, photo_path = ? WHERE id = ? AND user_id = ?');
                    $statement->execute([$restaurantId, $dish, $rating, $body, $photoPath, $reviewId, current_user()['id']]);
                    flash('Your review was updated. Thanks for keeping food recommendations honest!');
                    redirect('?page=review&id=' . $reviewId);
                }
                $statement = $pdo->prepare('INSERT INTO reviews (user_id, restaurant_id, display_name, dish_name, rating, body, photo_path) VALUES (?, ?, NULL, ?, ?, ?, ?)');
                $statement->execute([current_user()['id'], $restaurantId, $dish, $rating, $body, $photoPath]);
                flash('Your review is live. Thanks for keeping food recommendations honest!');
                redirect('?page=review&id=' . $pdo->lastInsertId());

            case 'vote':
                if (!current_user()) {
                    throw new RuntimeException('Sign in to mark a review helpful.');
                }
                $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
                $vote = filter_input(INPUT_POST, 'vote', FILTER_VALIDATE_INT);
                $review = $reviewId ? get_review($pdo, $reviewId) : null;
                if (!$review || !in_array($vote, [1, -1], true)) {
                    throw new RuntimeException('That review or vote could not be found.');
                }
                if ((int) $review['user_id'] === (int) current_user()['id']) {
                    throw new RuntimeException('You cannot vote on your own review.');
                }
                $statement = $pdo->prepare('INSERT INTO review_votes (review_id, user_id, vote) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE vote = VALUES(vote)');
                $statement->execute([$reviewId, current_user()['id'], $vote]);
                flash('Thanks for helping the community find the most useful reviews.');
                redirect('?page=review&id=' . $reviewId);

            case 'delete_review':
                if (!current_user()) {
                    throw new RuntimeException('Sign in to manage your reviews.');
                }
                $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
                $statement = $pdo->prepare('DELETE FROM reviews WHERE id = ? AND user_id = ?');
                $statement->execute([$reviewId, current_user()['id']]);
                flash($statement->rowCount() ? 'Your review was deleted.' : 'That review could not be deleted.', $statement->rowCount() ? 'success' : 'error');
                redirect('?page=dashboard');

            case 'create_restaurant':
            case 'delete_restaurant':
                if (!current_user() || (current_user()['role'] ?? 'member') !== 'admin') {
                    throw new RuntimeException('Only a restaurant manager can manage restaurant listings.');
                }
                if ($action === 'create_restaurant') {
                    $name = trim((string) ($_POST['name'] ?? ''));
                    $restaurantCuisine = trim((string) ($_POST['cuisine'] ?? ''));
                    $neighborhood = trim((string) ($_POST['neighborhood'] ?? ''));
                    $address = trim((string) ($_POST['address'] ?? ''));
                    if (strlen($name) < 2 || strlen($name) > 150 || strlen($restaurantCuisine) < 2 || strlen($restaurantCuisine) > 80 || strlen($neighborhood) < 2 || strlen($neighborhood) > 100 || strlen($address) > 255) {
                        throw new RuntimeException('Enter a restaurant name, cuisine, and neighborhood using the indicated field limits.');
                    }
                    $statement = $pdo->prepare('INSERT INTO restaurants (name, cuisine, neighborhood, address) VALUES (?, ?, ?, ?)');
                    $statement->execute([$name, $restaurantCuisine, $neighborhood, $address ?: null]);
                    flash('The restaurant has been added to the directory.');
                    redirect('?page=dashboard');
                }
                $restaurantId = filter_input(INPUT_POST, 'restaurant_id', FILTER_VALIDATE_INT);
                $statement = $pdo->prepare('DELETE FROM restaurants WHERE id = ?');
                $statement->execute([$restaurantId]);
                flash($statement->rowCount() ? 'The restaurant and its reviews were removed.' : 'That restaurant could not be found.', $statement->rowCount() ? 'success' : 'error');
                redirect('?page=dashboard');

            default:
                throw new RuntimeException('That action is not available.');
        }
    } catch (RuntimeException $exception) {
        flash($exception->getMessage(), 'error');
        $fallback = match ($action) {
            'register', 'login' => '?page=auth&mode=' . ($action === 'register' ? 'signup' : 'login'),
            'review', 'update_review' => '?page=submit',
            'vote' => '?page=review&id=' . (int) ($_POST['review_id'] ?? 0),
            'delete_review' => '?page=dashboard',
            'create_restaurant', 'delete_restaurant' => '?page=dashboard',
            default => './',
        };
        redirect($fallback);
    } catch (PDOException $exception) {
        $message = $exception->getCode() === '23000'
            ? 'That email, username, or restaurant entry already exists.'
            : 'Something went wrong while saving. Please try again.';
        flash($message, 'error');
        $fallback = match ($action) {
            'review', 'update_review' => '?page=submit' . (!empty($_POST['review_id']) ? '&id=' . (int) $_POST['review_id'] : ''),
            'vote' => '?page=review&id=' . (int) ($_POST['review_id'] ?? 0),
            'delete_review' => '?page=dashboard',
            'create_restaurant', 'delete_restaurant' => '?page=dashboard',
            default => '?page=auth&mode=signup',
        };
        redirect($fallback);
    }
}

$page = (string) ($_GET['page'] ?? 'home');
$allowedPages = ['home', 'discover', 'submit', 'review', 'dashboard', 'auth'];
if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}
if (in_array($page, ['submit', 'dashboard'], true) && !current_user()) {
    flash('Sign in to access your personal dashboard and submit reviews.', 'error');
    redirect('?page=auth&mode=login');
}

$flashMessage = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$user = current_user();
$restaurants = $pdo->query('SELECT id, name, cuisine, neighborhood FROM restaurants ORDER BY name')->fetchAll();
$search = trim((string) ($_GET['q'] ?? ''));
$cuisine = trim((string) ($_GET['cuisine'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'helpful');
$orderBy = match ($sort) {
    'newest' => 'r.created_at DESC',
    'rating' => 'r.rating DESC, helpful_count DESC',
    default => 'helpful_count DESC, r.created_at DESC',
};
$sql = 'SELECT r.*, COALESCE(u.username, r.display_name, \'food_friend\') AS username, d.name AS restaurant_name, d.cuisine, d.neighborhood,
               (SELECT COUNT(*) FROM review_votes v WHERE v.review_id = r.id AND v.vote = 1) AS helpful_count,
               (SELECT COUNT(*) FROM review_votes v WHERE v.review_id = r.id AND v.vote = -1) AS not_helpful_count
        FROM reviews r JOIN restaurants d ON d.id = r.restaurant_id LEFT JOIN users u ON u.id = r.user_id
                WHERE (:search_enabled = 0 OR r.dish_name LIKE :dish_search OR d.name LIKE :restaurant_search OR r.body LIKE :body_search)
                    AND (:cuisine_enabled = 0 OR d.cuisine = :selected_cuisine)
        ORDER BY ' . $orderBy . ' LIMIT 12';
$statement = $pdo->prepare($sql);
$searchPattern = '%' . $search . '%';
$statement->execute([
    'search_enabled' => $search === '' ? 0 : 1,
    'dish_search' => $searchPattern,
    'restaurant_search' => $searchPattern,
    'body_search' => $searchPattern,
    'cuisine_enabled' => $cuisine === '' ? 0 : 1,
    'selected_cuisine' => $cuisine,
]);
$reviews = $statement->fetchAll();
$restaurantScores = $pdo->query(
    'SELECT d.id, d.name, d.cuisine, d.neighborhood,
            COUNT(r.id) AS review_count,
            COALESCE(ROUND(SUM(r.rating * GREATEST(0.25, 1 + (COALESCE(v.helpful_count, 0) * 0.2) - (COALESCE(v.not_helpful_count, 0) * 0.15)))
                / NULLIF(SUM(GREATEST(0.25, 1 + (COALESCE(v.helpful_count, 0) * 0.2) - (COALESCE(v.not_helpful_count, 0) * 0.15))), 0), 1), 0) AS weighted_rating
     FROM restaurants d
     LEFT JOIN reviews r ON r.restaurant_id = d.id
     LEFT JOIN (SELECT review_id, SUM(vote = 1) AS helpful_count, SUM(vote = -1) AS not_helpful_count FROM review_votes GROUP BY review_id) v ON v.review_id = r.id
     GROUP BY d.id, d.name, d.cuisine, d.neighborhood
     ORDER BY weighted_rating DESC, review_count DESC'
)->fetchAll();

$review = null;
if ($page === 'review') {
    $review = get_review($pdo, (int) ($_GET['id'] ?? 0));
    if (!$review) {
        http_response_code(404);
        $page = 'not-found';
    }
}
$editingReview = null;
if ($page === 'submit' && isset($_GET['id'])) {
    $statement = $pdo->prepare('SELECT * FROM reviews WHERE id = ? AND user_id = ?');
    $statement->execute([(int) $_GET['id'], $user['id']]);
    $editingReview = $statement->fetch() ?: null;
    if (!$editingReview) {
        flash('That review was not found in your account.', 'error');
        redirect('?page=dashboard');
    }
}

$stats = ['reviews' => 0, 'restaurants' => 0, 'rating' => '0.0', 'helpful' => 0];
if ($user) {
    $statement = $pdo->prepare('SELECT COUNT(*) AS reviews, COUNT(DISTINCT restaurant_id) AS restaurants, COALESCE(AVG(rating), 0) AS rating FROM reviews WHERE user_id = ?');
    $statement->execute([$user['id']]);
    $stats = array_merge($stats, $statement->fetch());
    $statement = $pdo->prepare('SELECT COUNT(*) FROM review_votes v JOIN reviews r ON r.id = v.review_id WHERE r.user_id = ? AND v.vote = 1');
    $statement->execute([$user['id']]);
    $stats['helpful'] = (int) $statement->fetchColumn();
    $statement = $pdo->prepare('SELECT r.*, d.name AS restaurant_name, (SELECT COUNT(*) FROM review_votes v WHERE v.review_id = r.id AND v.vote = 1) AS helpful_count FROM reviews r JOIN restaurants d ON d.id = r.restaurant_id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 10');
    $statement->execute([$user['id']]);
    $myReviews = $statement->fetchAll();
    if (($user['role'] ?? 'member') === 'admin') {
        $managedRestaurants = $pdo->query('SELECT id, name, cuisine, neighborhood FROM restaurants ORDER BY name')->fetchAll();
    }
}

$view = __DIR__ . '/views/' . ($page === 'not-found' ? 'not-found' : $page) . '.php';
require __DIR__ . '/views/layout.php';
