<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Unbounded:wght@200..900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="form" id="form-container">

    <?php
        if (!empty($messages)) {
            foreach ($messages as $msg) {
                echo $msg;
            }
        }
        ?>

        <form id="contactForm">
            <div class="form-group">
                <label for="fullName" class="required">ФИО</label>
                <input type="text" id="fullName" name="fullName"  placeholder="Введите ваше полное имя"
                    class="<?php echo $errors['fullName'] ? 'field-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($values['fullName']); ?>">>
                </div>

            <div class="form-group">
                <label for="email" class="required">Email</label>
                <input type="email" id="email" name="email"  placeholder="example@domain.com"
                    class="<?php echo $errors['email'] ? 'field-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($values['email']); ?>">>
            </div>

            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" placeholder="+7 (XXX) XXX-XX-XX"
                    value="<?php echo htmlspecialchars($values['phone']); ?>">>
            </div>

            <div class="form-group">
                <label for="bdate">Дата рождения</label>
                <input type="date" id="bdate" name="bdate" placeholder="bday"
                    value="<?php echo htmlspecialchars($values['bday'])?>">>
            </div>

            <div class="form-group">
                <label>Пол</label>
                <div>
                    <input type="radio" id="gender-male" name="gender" value="male"
                    <?php echo $values['gender'] == 'male' ? 'checked' : ''; ?>>>
                    <label for="gender-male">Мужской</label>
                    
                    <input type="radio" id="gender-female" name="gender" value="female"
                    <?php echo $values['gender'] == 'female' ? 'checked' : ''; ?>>>
                    <label for="gender-female">Женский</label>
                </div>
            </div>

        <div class="form-group">
        <label for="languages">Любимый язык программирования:</label>
        <select id="languages" name="languages[]" multiple="multiple"  size="5"
        class="<?php echo $errors['languages'] ? 'field-error' : ''; ?>">
            <?php
            $langs = [
                'pascal' => 'Pascal',
                'c' => 'C',
                'cpp' => 'C++',
                'js' => 'JavaScript',
                'php' => 'PHP',
                'python' => 'Python'
            ];
            foreach ($langs as $key => $label){
                $selected = in_array($key, $values['languages']) ? 'selected' : '';
                echo "<option value=\"$key\" $selected>$label</option>";
            }
            ?>
        </select>
    </div>

            <div class="form-group">
                <label for="bio" class="required">Биография</label>
                <textarea id="bio" name="bio"  placeholder="Опишите вас..." class="
                <?php echo $errors['bio'] ? 'field-error' : ''; ?>">
                <?php echo htmlspecialchars($values['bio']); ?>></textarea>
            </div>

            <div class="checkbox-container">
                <input type="checkbox" id="privacy" name="privacy" value = "ok"
                <?php echo !empty($values['privacy']) ? 'checked' : ''; ?>>
                <label for="privacy">
                    С контрактом ознакомлен.
                </label>
            </div>
            <button type="submit" id="submit_form" class="form_btn">Сохранить</button>
        </form>
    </div>
</body>
</html>