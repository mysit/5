<?php
header('Content-Type: text/html; charset=UTF-8');

$user = 'u82196';
$pass = '4736526';
$db_name = 'u82196';
$host = 'localhost';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();

    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        setcookie('login', '', 100000);
        setcookie('pass', '', 100000);
        $messages[] = '<div class="success-msg">Спасибо, результаты сохранены.</div>';
        if (!empty($_COOKIE['pass'])) {
            $messages[] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
            и паролем <strong>%s</strong> для изменения данных.',
            strip_tags($_COOKIE['login']),
            strip_tags($_COOKIE['pass']));
        }
    }

    $errors = array();
    $fields = ['fullName', 'email', 'gender', 'languages', 'bio', 'privacy'];
    foreach ($fields as $f) {
        $errors[$f] = !empty($_COOKIE[$f . '_error']);
    }

    if ($errors['fullName']) {
        setcookie('fullName_error', '', 100000);
        $messages[] = '<div class="error-msg">Имя заполнено неверно или пустое.</div>';
    }
    if ($errors['email']) {
        setcookie('email_error', '', 100000);
        $messages[] = '<div class="error-msg">Email указан некорректно.</div>';
    }
    if ($errors['languages']) {
        setcookie('languages_error', '', 100000);
        $messages[] = '<div class="error-msg">Выберите хотя бы один язык программирования.</div>';
    }
    if ($errors['bio']) {
        setcookie('bio_error', '', 100000);
        $messages[] = '<div class="error-msg">Расскажите что-нибудь о себе в биографии.</div>';
    }

    $values = array();
    $all_fields = ['fullName', 'email', 'phone', 'bdate', 'gender', 'bio', 'privacy'];
    foreach ($all_fields as $f) {
        $values[$f] = empty($_COOKIE[$f . '_value']) ? '' : $_COOKIE[$f . '_value'];
    }
    $values['languages'] = empty($_COOKIE['languages_value']) ? [] : explode(',', $_COOKIE['languages_value']);

    // --- ИСПРАВЛЕННЫЙ БЛОК GET ---
    if (empty($errors) && !empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
        try {
            $db = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $stmt = $db->prepare("SELECT * FROM application WHERE login = ?");
            $stmt->execute([$_SESSION['login']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $values['fullName'] = htmlspecialchars($row['name']);
                $values['email']    = htmlspecialchars($row['email']);
                $values['phone']    = htmlspecialchars($row['phone']);
                $values['bdate']    = htmlspecialchars($row['bday']);
                $values['gender']   = htmlspecialchars($row['sex']);
                $values['bio']      = htmlspecialchars($row['bio']);
                
                $stmt_langs = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
                $stmt_langs->execute([$row['id']]);
                $values['languages'] = $stmt_langs->fetchAll(PDO::FETCH_COLUMN);
            }
        } catch (PDOException $e) {
            print('Ошибка: ' . $e->getMessage());
            exit();
        }
        // printf перенесли внутрь IF, чтобы не ругался на пустую сессию
        printf('Вход с логином %s, uid %d', $_SESSION['login'], $_SESSION['uid']);
    }
    // Конец блока GET
    include('form.php');
} 
else {
    // --- МЕТОД POST ---
    $errors = FALSE;

    if (empty($_POST['fullName'])) {
        setcookie('fullName_error', '1', time() + 24 * 3600);
        $errors = TRUE;
    }
    setcookie('fullName_value', $_POST['fullName'], time() + 30 * 24 * 3600);

    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 3600);
        $errors = TRUE;
    }
    setcookie('email_value', $_POST['email'], time() + 30 * 24 * 3600);

    if (empty($_POST['bio'])) {
        setcookie('bio_error', '1', time() + 24 * 3600);
        $errors = TRUE;
    }
    setcookie('bio_value', $_POST['bio'], time() + 30 * 24 * 3600);

    if (empty($_POST['languages'])) {
        setcookie('languages_error', '1', time() + 24 * 3600);
        $errors = TRUE;
    } else {
        setcookie('languages_value', implode(',', $_POST['languages']), time() + 30 * 24 * 3600);
    }

    setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 3600);
    setcookie('bdate_value', $_POST['bdate'], time() + 30 * 24 * 3600);
    setcookie('gender_value', $_POST['gender'], time() + 30 * 24 * 3600);
    setcookie('privacy_value', $_POST['privacy'], time() + 30 * 24 * 3600);

    if ($errors) {
        header('Location: index.php');
        exit();
    }
    else {
        setcookie('fullName_error', '', 100000);
        setcookie('email_error', '', 100000);
        setcookie('languages_error', '', 100000);
        setcookie('bio_error', '', 100000);

        try {
            $db = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
                $login = $_SESSION['login'];
                // Исправлено: таблица application (было applications)
                $stmt = $db->prepare("SELECT id FROM application WHERE login = ?");
                $stmt->execute([$login]);
                $user_id = $stmt->fetchColumn();

                if($user_id){
                    $sql = "UPDATE application SET
                            name = ?,
                            email = ?,
                            phone = ?,
                            bday = ?,
                            sex = ?,
                            bio = ?
                            WHERE id = ?"; // Исправлено: добавлена точка с запятой

                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        $_POST['fullName'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['bdate'],
                        $_POST['gender'],
                        $_POST['bio'],
                        $user_id
                    ]);

                    $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = ?");
                    $stmt->execute([$user_id]);

                    $stmt_l = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
                    foreach ($_POST['languages'] as $lang_id) {
                        $stmt_l->execute([$user_id, $lang_id]);
                    }
                }
            }
            else {
                // НОВЫЙ ПОЛЬЗОВАТЕЛЬ
                $login = uniqid('user');
                $pass_plain = rand(1000, 9999);
                $pass_hash = md5($pass_plain);

                setcookie('login', $login, time() + 30 * 24 * 3600);
                setcookie('pass', $pass_plain, time() + 30 * 24 * 3600);

                // Исправлено: добавлены login и password в INSERT
                $stmt = $db->prepare("INSERT INTO application (name, email, phone, bday, sex, bio, login, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['fullName'], $_POST['email'], $_POST['phone'], $_POST['bdate'], $_POST['gender'], $_POST['bio'], $login, $pass_hash]);

                $id = $db->lastInsertId();

                $stmt_l = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
                foreach ($_POST['languages'] as $l) {
                    $stmt_l->execute([$id, $l]);
                }
            }

            setcookie('save', '1');
            header('Location: ./');
        } catch (PDOException $e) {
            die("Ошибка БД: " . $e->getMessage());
        }
    }
}
