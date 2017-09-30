<?php

/**
 * Общий класс для работы с миграциями.
 */
class Migrate
{
    /**
     * Параметры для подключения к базе данных.
     */
    protected $host = 'localhost';
    protected $user = 'phpmigrations';
    protected $password = 'phpmigrations';
    protected $dbName = 'phpmigrations';
    protected $port = 3306;
    protected $tableNameForMigration = 'migration';
    protected $dirMigrationUp = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.'up'.DIRECTORY_SEPARATOR;
    protected $dirMigrationDown = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.'down'.DIRECTORY_SEPARATOR;

    /** @var array История применения/отката миграций */
    protected $history = [];

    /**
     * Метод возвращает историю применения/отката миграций.
     *
     * @return array
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * Метод очищает историю применения/отката миграций.
     *
     * @return bool
     */
    public function clearHistory()
    {
        $this->history = [];
        return true;
    }



    //Singleton
    /** @var null|self */
    public static $app = null;
    public function __construct(){}
    function __wakeup(){}
    function __clone(){}

    /**
     * Метод возвращает экземпляр приложения.
     * Паттерн Singleton.
     *
     * @return Migrate|null
     */
    public static function run()
    {
        if(self::$app === null)
        {
            self::$app = new self;
        }
        return self::$app;
    }


    /**
     * Метод подключения к базе данных.
     *
     * @return mysqli Соединение с базой данных
     * @throws Exception Ошибка при подключении к MySQL
     */
    protected function connectDB()
    {
        $connectDb = new mysqli($this->host, $this->user, $this->password, $this->dbName, $this->port);
        if(!$connectDb){
            throw new Exception('Не удалось подключиться к СУБД MySQL');
        } else {
            $query = $connectDb->query('set names utf8');
            if (!$query){
                throw new Exception('Не удалось установить кодировку utf8');
            }
                return $connectDb;
        }
    }

    public function getAllFilesSqlInDirUp()
    {
        return glob($this->dirMigrationUp. '*.sql');
    }

    public function getAllFilesSqlNoYetUp()
    {
        return $this->getFiles($this->connectDB());
    }

    /**
     * Метод возвращает списик файлов, которые необходимо применить к базе данных
     *
     * @param mysqli $connectDb
     * @return array
     */
    protected function getFiles(mysqli $connectDb){
        //Получаем список всех sql файлов
        $sqlFiles = $this->getAllFilesSqlInDirUp();

        //Проверяем, есть ли таблица $this->tableNameForMigration
        //Если ее нет, то БД чистая
        $queryShow = sprintf('show tables from `%s` like "%s"', $this->dbName, $this->tableNameForMigration);
        $row = $connectDb->query($queryShow);
        $isFirstMigration = !$row->num_rows;

        //Чистая база данных, если да, то выбираем все файлы *sql
        if($isFirstMigration){
            return $sqlFiles;
        }

        //Находим все существующие миграции
        $selectMigrationFiles = [];
        $querySelect = sprintf('select `filename` from `%s`', $this->tableNameForMigration);
        $rows = $connectDb->query($querySelect)->fetch_all();

        //Помещаем данные в массив
        foreach ($rows as $row)
        {
            array_push($selectMigrationFiles, $this->dirMigrationUp . $row[0]);
        }

        //Сравниваем массивы и возвращаем тот список файлов, которого еще нет в базе данных
        //в таблице $this->tableNameForMigration
        return array_diff($sqlFiles, $selectMigrationFiles);
    }

    /**
     * Метод применения миграций.
     * Добавляет запись в таблицу миграций и применяет файл миграций из папки up.
     *
     * @param mysqli $connectDb
     * @param $file
     */
    protected function migrateUp(mysqli $connectDb, $file){
        //Формируем команду для выполнения SQL из внешнего файла
        $commandShell = sprintf('mysql -u%s -p%s -h %s -D %s < %s', $this->user, $this->password, $this->host, $this->dbName, $file);
        shell_exec($commandShell);

        //Получаем имя файла для сохранения в таблице $this->tableNameForMigration
        $fileName = basename($file);
        //Запрос для сохранения имени файла
        $queryInsert = sprintf('insert into `%s` (`filename`) VALUES ("%s")', $this->tableNameForMigration, $fileName);
        $connectDb->query($queryInsert);
    }

    /**
     * Метод отменяет примененные миграции для переданного файла.
     * Рабочая папка down.
     *
     * @param mysqli $connectDb
     * @param $file
     */
    protected function migrateDown(mysqli $connectDb, $file){
        array_push($this->history, 'Начало отмены миграции ' . basename($file));
        //Формируем команду для выполнения SQL из внешнего файла
        $commandShell = sprintf('mysql -u%s -p%s -h %s -D %s < %s', $this->user, $this->password, $this->host, $this->dbName, $file);
        shell_exec($commandShell);

        //Получаем имя файла для сохранения в таблице $this->tableNameForMigration
        $fileName = basename($file);
        //Запрос для удаления имени файла
        $queryDelete = sprintf('DELETE FROM `%s` WHERE filename = "%s"', $this->tableNameForMigration, $fileName);
        $connectDb->query($queryDelete);
    }

    /**
     * Метод запускает механизм применения миграций.
     *
     * @param int $migrateCount Количество файлов миграций, которое должно быть применено.
     */
    public function upCommand($migrateCount)
    {
        $this->clearHistory(); //Очищаем историю

        if(is_null($migrateCount) || !is_numeric($migrateCount)){
            $migrateCount = 10; //10 миграций применяется за 1 раз, если не задано значение
        }

        $connectDb = $this->connectDB();
        $migrateFiles = $this->getFiles($connectDb);

        //Есть ли у нас файлы для применения к базе данных
        if(empty($migrateFiles))
        {
            array_push($this->history, 'Отсутствуют новые миграции для применения');
        } else {
            array_push($this->history, 'Начало применения миграций...');

            foreach ($migrateFiles as $file)
            {
                if($migrateCount === 0) {
                    break;
                }
                $this->migrateUp($connectDb, $file);
                $migrateCount--;
                array_push($this->history, 'Применение миграции ' . basename($file));

            }
            array_push($this->history, 'Все миграции применены');
        }
    }

    /**
     * Метод запускает механизм отмены миграций.
     *
     * @param int $migrateCount Количество файлов миграций, которое должно быть отменено.
     */
    public function downCommand($migrateCount)
    {
        $this->clearHistory(); //Очищаем историю

        $connectDb = $this->connectDB();

        //Получаем список всех примененных миграций
        $querySelect = sprintf('select `filename` from `%s`', $this->tableNameForMigration);
        $rows = $connectDb->query($querySelect)->fetch_all();

        if(empty($rows)){
            array_push($this->history, 'Отсутствуют миграции для отмены');
            return;}

        array_push($this->history, 'Начало отмены миграций...');

        if(is_null($migrateCount) || $migrateCount === 1){
            $lastFilename = array_pop($rows)[0];
            $this->migrateDown($connectDb, $this->dirMigrationDown . $lastFilename);
        } else {
            while ($lastFilename = array_pop($rows)[0])
            {
                if($migrateCount === 0){
                    break;
                }
                $this->migrateDown($connectDb, $this->dirMigrationDown . $lastFilename);
                $migrateCount--;
            }
        }
        array_push($this->history, 'Все миграции отменены');
    }
}