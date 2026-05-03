<?php
/**
 * Файл login.php: авторизация пользователя.
 */

// Устанавливаем кодировку
header('Content-Type: text/html; charset=UTF-8');

// Параметры подключения к БД (замените на свои, если они отличаются)
$db_user = 'u82196';
$db_pass = '4736526';
$db_name = 'u82196';
$db_host = 'localhost';

// Начинаем сессию
session_start();

// --- БЛОК 1: ОБРАБОТКА ВЫХОДА (LOGOUT) ---
if (isset($_GET['do']) && $_GET['do'] == 'logout') {
    // Очищаем сессию
    session_unset();
    session_destroy();
    // Удаляем куку сессии
    setcookie(session_name(), '', time() - 3600, '/');
    // Перенаправляем на страницу логина
    header('Location: login.php');
    exit();
}

// --- БЛОК 2: ЕСЛИ ПОЛЬЗОВАТЕЛЬ УЖЕ ВОШЕЛ ---
if (!empty($_SESSION['login'])) {
    // Перенаправляем на главную, чтобы не входить дважды
    header('Location: ./');
    exit();
}

// --- БЛОК 3: ОБРАБОТКА POST-ЗАПРОСА (ПОПЫТКА ВХОДА) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $pass_from_form = $_POST['pass'];
    $error = false;

    // Валидация пустых полей
    if (empty($login) || empty($pass_from_form)) {
        $error = "Введите логин и пароль";
    } else {
        try {
            $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Ищем пользователя по логину
            $stmt = $db->prepare("SELECT id, password FROM application WHERE login = ?");
            $stmt->execute([$login]);
            $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Проверяем: пользователь найден? Пароль совпадает (с проверкой md5)?
            if ($user_row && $user_row['password'] == md5($pass_from_form)) {
                // Успех! Записываем данные в сессию
                $_SESSION['login'] = $login;
                $_SESSION['uid'] = $user_row['id'];

                // Перенаправляем на главную
                header('Location: ./');
                exit();
            } else {
                $error = "Неверный логин или пароль";
            }
        } catch (PDOException $e) {
            die("Ошибка базы данных: " . $e->getMessage());
        }
    }
}

// --- БЛОК 4: ОТОБРАЖЕНИЕ ФОРМЫ (GET-ЗАПРОС) ---
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в систему</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f0f2f5; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 300px; }
        .error { color: red; margin-bottom: 1rem; font-size: 0.9rem; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        input[type="submit"] { background: #007bff; color: white; border: none; cursor: pointer; font-weight: bold; }
        input[type="submit"]:hover { background: #0056b3; }
        h2 { margin-top: 0; text-align: center; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Вход</h2>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <label>Логин:</label>
        <input name="login" placeholder="Ваш логин" required />
        
        <label>Пароль:</label>
        <input type="password" name="pass" placeholder="Ваш пароль" required />
        
        <input type="submit" value="Войти" />
    </form>
    
    <p style="text-align: center; font-size: 0.8rem;">
        <a href="./">Вернуться к форме</a>
    </p>
</div>

</body>
</html>