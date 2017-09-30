<?php
require_once ('../classes/Migrate.php');
define('ERROR_CODE', 1);

echo 'Консольное приложение для работы с файлами миграций.'.PHP_EOL;

$action = null;
$migrateCount = null;

if($argc === 2) {
    $action = !empty($argv[1]) ? $argv[1] : null;
} else if ($argc === 3){
    $action = !empty($argv[1]) ? $argv[1] : null;
    $migrateCount = (!empty($argv[2]) && is_numeric($argv[2])) ? (int) $argv[2] : null;
} else{
    echo 'Введите через пробел имя команды.'.PHP_EOL;
    echo 'Примеры доступных команд:'.PHP_EOL;
    echo 'php index.php up - применение всех миграций;'.PHP_EOL;
    echo 'php index.php down - отмена миграций.'.PHP_EOL;
    echo 'php index.php up 3 - применение 3-х ближайших миграций.'.PHP_EOL;
    echo 'php index.php down 3 - отмена 3-х последних миграций.'.PHP_EOL;
    exit(ERROR_CODE);
}

Migrate::run(); //Создаем экземпляр приложения


switch ($action)
{
    case 'up':
        Migrate::$app->upCommand($migrateCount); //Применяем миграции
        break;
    case 'down':
        Migrate::$app->downCommand($migrateCount); //Отменяем миграции
        break;
    default:
        echo 'Ошибка ввода. Команда не найдена.'.PHP_EOL;
        exit(ERROR_CODE);
}

$history = Migrate::$app->getHistory();
if(!empty($history)){
    foreach ($history as $value)
    {
        echo $value.PHP_EOL;
    }
}

