<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Форма регистрации</title>
    <style>
        /* Стиль для подсвечивания ошибок */
        .field-error { border: 2px solid red; }
        .error-msg { color: red; font-size: 0.8em; display: block; }
        .success-msg { color: green; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>
<?php if (!empty($_SESSION['login'])): ?>
    <div style="text-align: right; margin-bottom: 20px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
        Вы вошли как: <strong><?php echo $_SESSION['login']; ?></strong> | 
        <a href="login.php?do=logout" style="color: #dc3545; text-decoration: none; font-weight: bold;">Выйти</a>
    </div>
<?php endif; ?>
    <div class="form" id="form-container">
        
        <?php
        // Вывод сообщений (успех или общие ошибки)
        if (!empty($messages)) {
            foreach ($messages as $msg) {
                echo $msg;
            }
        }
        ?>

        <form action="index.php" method="POST" id="contactForm">
            <div class="form-group">
                <label for="fullName" class="required">ФИО</label>
                <input type="text" id="fullName" name="fullName" 
                    placeholder="Введите ваше полное имя"
                    class="<?php echo $errors['fullName'] ? 'field-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($values['fullName']); ?>">
            </div>

            <div class="form-group">
                <label for="email" class="required">Email</label>
                <input type="email" id="email" name="email" 
                    placeholder="example@domain.com"
                    class="<?php echo $errors['email'] ? 'field-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($values['email']); ?>">
            </div>

            <div class="form-group">
    <label for="number">Телефон</label>
    <input type="tel" id="number" name="number" 
        placeholder="+7 (XXX) XXX-XX-XX"
        value="<?php echo htmlspecialchars($values['number']); ?>">
</div>

            <div class="form-group">
                <label for="bdate">Дата рождения</label>
                <input type="date" id="bdate" name="bdate" 
                    value="<?php echo htmlspecialchars($values['bdate']); ?>">
            </div>

            <div class="form-group">
                <label>Пол</label>
                <div class="<?php echo $errors['gender'] ? 'field-error' : ''; ?>">
                    <input type="radio" id="gender-male" name="gender" value="male" 
                        <?php echo $values['gender'] == 'male' ? 'checked' : ''; ?>>
                    <label for="gender-male">Мужской</label>
                    
                    <input type="radio" id="gender-female" name="gender" value="female" 
                        <?php echo $values['gender'] == 'female' ? 'checked' : ''; ?>>
                    <label for="gender-female">Женский</label>
                </div>
            </div>

            <div class="form-group">
                <label for="languages">Любимые языки:</label>
                <select id="languages" name="languages[]" multiple="multiple" size="5"
                    class="<?php echo $errors['languages'] ? 'field-error' : ''; ?>">
                    <?php
                    $langs = [
                        '1a0caebb-268b-11f1-a59b-bc241103b411' => 'Pascal',
                        '1a0cb9c9-268b-11f1-a59b-bc241103b411"' => 'C',
                        '1a0cbde6-268b-11f1-a59b-bc241103b411' => 'C++',
                        '1a0cbf43-268b-11f1-a59b-bc241103b411' => 'JavaScript',
                        '1a0cc059-268b-11f1-a59b-bc241103b411' => 'PHP',
                        '1a0cc194-268b-11f1-a59b-bc241103b411' => 'Python',
                        '1a0cc290-268b-11f1-a59b-bc241103b411' => 'Java',
                        '1a0cc367-268b-11f1-a59b-bc241103b411' => 'Haskell'
                    ];
                    foreach ($langs as $key => $label) {
                        $selected = in_array($key, $values['languages']) ? 'selected' : '';
                        echo "<option value=\"$key\" $selected>$label</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="bio" class="required">Биография</label>
                <textarea id="bio" name="bio" 
                    class="<?php echo $errors['bio'] ? 'field-error' : ''; ?>"><?php echo htmlspecialchars($values['bio']); ?></textarea>
            </div>

            <div class="checkbox-container">
                <input type="checkbox" id="privacy" name="privacy" value="ok"
                    <?php echo !empty($values['privacy']) ? 'checked' : ''; ?>>
                <label for="privacy">С контрактом ознакомлен.</label>
            </div>

            <button type="submit" class="form_btn">Сохранить</button>
        </form>
    </div>
</body>
</html>
