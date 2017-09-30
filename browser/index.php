<?php
require_once('../classes/Migrate.php');

Migrate::run();

/** Обработка формы применения миграций */
if (isset($_POST['migrate-count']) && isset($_POST['migrate-up'])) {
    $count = (!empty($_POST['migrate-count'])) ? (int)$_POST['migrate-count'] : null;
    Migrate::$app->upCommand($count);
}

/** Обработка формы отмены миграций */
if (isset($_POST['migrate-count']) && isset($_POST['migrate-down'])) {
    $count = (!empty($_POST['migrate-count'])) ? (int)$_POST['migrate-count'] : null;
    Migrate::$app->downCommand($count);
}

$filesNoYetUp = Migrate::$app->getAllFilesSqlNoYetUp();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Работа с миграциями | клиент для браузера</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
<section class="migrate-all">
    <h2>Общее количество файлов миграций</h2>
    <div class="migrate-all-container">
        <?php if (!empty(Migrate::$app->getAllFilesSqlInDirUp())): ?>
            <ul class="migrate-all-list">
                <?php foreach (Migrate::$app->getAllFilesSqlInDirUp() as $item): ?>
                    <?php
                    //Поиск в массиве файла
                    $isYetUp = array_search($item, $filesNoYetUp) === false;
                    ?>

                    <li class="<?= ($isYetUp) ? 'color-green' : 'color-red'; ?>"><?= $item ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Отсутствуют файлы с миграциями</p>
        <?php endif; ?>
    </div>
</section>

<section class="migrate-up">
    <h2>Блок применения миграций</h2>
    <div class="migrate-up-container">
        <form action="#" method="post">
            <label>
                Введите количество миграций, которое должно быть применено:
                <input class="migrate-input" name="migrate-count" type="number" value="1" step="1" min="1" max="100">
            </label>
            <input type="submit" name="migrate-up" value="Применить миграции">
        </form>
        <?php if (!empty(Migrate::$app->getHistory()) && isset($_POST['migrate-up'])): ?>
            <hr>
            <h3>История</h3>
            <ul class="migrate-history-list">
                <?php foreach (Migrate::$app->getHistory() as $item): ?>
                    <li><?= $item ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

<section class="migrate-down">
    <h2>Блок отмены миграций</h2>
    <div class="migrate-down-container">
        <form action="#" method="post">
            <label>
                Введите количество миграций, которое должно быть отменено:
                <input class="migrate-input" name="migrate-count" type="number" value="1" step="1" min="1" max="100">
            </label>
            <input type="submit" name="migrate-down" value="Отменить миграции">
        </form>
        <?php if (!empty(Migrate::$app->getHistory()) && isset($_POST['migrate-down'])): ?>
            <hr>
            <h3>История</h3>
            <ul class="migrate-history-list">
                <?php foreach (Migrate::$app->getHistory() as $item): ?>
                    <li><?= $item ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
</body>
</html>