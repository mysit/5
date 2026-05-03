<?php
header('Content-Type: text/html; charset=UTF-8');

//подключение к бд
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

    // Состояние ошибок
    $errors = array();
    $fields = ['fullName', 'email', 'gender', 'languages', 'bio', 'privacy'];
    //проверяем каждое поле формы на ошибки
    foreach ($fields as $f) {
        $errors[$f] = !empty($_COOKIE[$f . '_error']);
    }

    //для обязательных полей выводим специальные ошибки
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
    

    // значения полей
    $values = array();
    $all_fields = ['fullName', 'email', 'phone', 'bdate', 'gender', 'bio', 'privacy'];
    //если среди них есть ошибочные, заполняются пустотой
    foreach ($all_fields as $f) {
        $values[$f] = empty($_COOKIE[$f . '_value']) ? '' : $_COOKIE[$f . '_value'];
    }
    
    // Языки обрабатываем отдельно (массив через запятую)
    $values['languages'] = empty($_COOKIE['languages_value']) ? [] : explode(',', $_COOKIE['languages_value']);


    // Если нет предыдущих ошибок ввода, есть кука сессии, начали сессию и
    // ранее в сессию записан факт успешного логина.
    if (empty($errors) && !empty($_COOKIE[session_name()]) &&
        session_start() && !empty($_SESSION['login'])) {

        $db = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $stmt = $db->prepare("SELECT * FROM application WHERE login = ?");
        $stmt->execute([$_SESSION['login']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // очищаем данные для вывода в HTML, чтобы избежать XSS
            $values['fullName'] = htmlspecialchars($row['name']);
            $values['email']    = htmlspecialchars($row['email']);
            $values['phone']    = htmlspecialchars($row['phone']);
            $values['bdate']    = htmlspecialchars($row['bday']);
            $values['gender']   = htmlspecialchars($row['sex']);
            $values['bio']      = htmlspecialchars($row['bio']);
            
            // загружаем языки пользователя из связанной таблицы
            $stmt_langs = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
            $stmt_langs->execute([$row['id']]);
            
            // fetchAll(PDO::FETCH_COLUMN) сразу вернет простой массив с ID языков
            $values['languages'] = $stmt_langs->fetchAll(PDO::FETCH_COLUMN);
        }
    } catch (PDOException $e) {
        // В случае ошибки БД можно вывести её (для отладки)
        print('Ошибка: ' . $e->getMessage());
        exit();
    }

        printf('Вход с логином %s, uid %d', $_SESSION['login'], $_SESSION['uid']);
    }
    include('form.php');
} 
else {
    // МЕТОД POST
    $errors = FALSE;

    // Валидация ФИО
    if (empty($_POST['fullName'])) {
        setcookie('fullName_error', '1', time() + 24 * 3600);
        $errors = TRUE;
    }
    setcookie('fullName_value', $_POST['fullName'], time() + 30 * 24 * 3600);

    // Валидация Email
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 3600);
        $errors = TRUE;
    }
    setcookie('email_value', $_POST['email'], time() + 30 * 24 * 3600);

    // Валидация Био
    if (empty($_POST['bio'])) {
        setcookie('bio_error', '1', time() + 24 * 3600);
        $errors = TRUE;
    }
    setcookie('bio_value', $_POST['bio'], time() + 30 * 24 * 3600);

    // Валидация Языков
    if (empty($_POST['languages'])) {
        setcookie('languages_error', '1', time() + 24 * 3600);
        $errors = TRUE;
    } else {
        setcookie('languages_value', implode(',', $_POST['languages']), time() + 30 * 24 * 3600);
    }

    // Сохраняем остальные (необязательные) поля
    setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 3600);
    setcookie('bdate_value', $_POST['bdate'], time() + 30 * 24 * 3600);
    setcookie('gender_value', $_POST['gender'], time() + 30 * 24 * 3600);
    setcookie('privacy_value', $_POST['privacy'], time() + 30 * 24 * 3600);

    if ($errors) {
        // При наличии ошибок перезагружаем страницу и завершаем работу скрипта.
        header('Location: index.php');
        exit();
    }
    else {
        // Удаляем Cookies с признаками ошибок.
        setcookie('fullName_error', '', 100000);
        setcookie('email_error', '', 100000);
        setcookie('languages_error', '', 100000);
        setcookie('bio_error', '', 100000);

        try {
        $db = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Проверяем: 
        // 1. Существует ли кука сессии (пользователь уже заходил).
        // 2. Успешно ли стартовала сессия.
        // 3. Есть ли в сессии пометка, что пользователь вошел (логин).
        if (!empty($_COOKIE[session_name()]) &&
            session_start() && !empty($_SESSION['login'])) {
            //берем его логин.
            $login = $_SESSION['login'];
            try{
                $stmt = $db->prepare("SELECT id FROM applications WHERE login = ?");
                $stmt->execute([$login]);
                $user_id = $stmt->fetchColumn();

                if($user_id){
                    $sql = "UPDATE application SET
                                    name = ?,
                                    email = ?,
                                    phone = ?,
                                    bday = ?,
                                    sex = ?,
                                    bio=?
                                    WHERE id = ?"

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
        }
        else {
            $login = uniqid('user');
            $pass_plain = rand(1000, 9999);
            $pass_hash = md5($pass_plain); // или password_hash

            setcookie('login', $login, time() + 30 * 24 * 3600);
            setcookie('pass', $pass_plain, time() + 30 * 24 * 3600);

            // Вставляем основную запись
            $stmt = $db->prepare("INSERT INTO application (name, email, phone, bday, sex, bio) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['fullName'], $_POST['email'], $_POST['phone'], $_POST['bdate'], $_POST['gender'], $_POST['bio']]);

            $id = $db->lastInsertId();

            // Вставляем языки
            $stmt_l = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($_POST['languages'] as $l) {
                $stmt_l->execute([$id, $l]);
            }
        }

        // Сохраняем куку с признаком успешного сохранения.
        setcookie('save', '1');

        // Делаем перенаправление.
        header('Location: ./');
    }

    

    // Если всё Ок - сохраняем в БД
    
}