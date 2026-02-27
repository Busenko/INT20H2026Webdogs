<?php
declare(strict_types=1);

require_once __DIR__ . '/private/vendor/autoload.php';

use App\Core\Database;
use App\Security\PasswordHasher;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/private');
$dotenv->load();

$adminLogin = 'webdogs';
$adminPass  = '0123webdogs-start'; 

try {
    $database = new Database(
        $_ENV['DB_HOST'],
        $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );
    
    $pdo = $database->getConnection();

    echo "--- Оновлення пароля адміністратора ---" . PHP_EOL;

    $hashedPassword = PasswordHasher::hash($adminPass);

    $sql = "UPDATE `admins` SET password = :password WHERE login = :login";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':login'    => $adminLogin,
        ':password' => $hashedPassword
    ]);

    if ($stmt->rowCount() > 0) {
        echo "Успіх! Пароль для '{$adminLogin}' оновлено." . PHP_EOL;
    } else {
        echo "Помилка: Користувача не знайдено або пароль вже такий самий." . PHP_EOL;
    }

} catch (\Exception $e) {
    echo "КРИТИЧНА ПОМИЛКА: " . $e->getMessage() . PHP_EOL;
}