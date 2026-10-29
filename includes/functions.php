<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page, and try again.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function weighted_rating(float $average, int $helpful, int $notHelpful): float
{
    $weight = max(0.25, 1 + ($helpful * 0.2) - ($notHelpful * 0.15));
    return $average * $weight;
}

function get_review(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare(
        'SELECT r.*, COALESCE(u.username, r.display_name, \'food_friend\') AS username, d.name AS restaurant_name, d.cuisine, d.neighborhood,
                (SELECT COUNT(*) FROM review_votes v WHERE v.review_id = r.id AND v.vote = 1) AS helpful_count,
                (SELECT COUNT(*) FROM review_votes v WHERE v.review_id = r.id AND v.vote = -1) AS not_helpful_count
         FROM reviews r
         JOIN restaurants d ON d.id = r.restaurant_id
         LEFT JOIN users u ON u.id = r.user_id
         WHERE r.id = ?'
    );
    $statement->execute([$id]);
    $review = $statement->fetch();

    return $review ?: null;
}
