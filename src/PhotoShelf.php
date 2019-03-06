<?php

/**
 * Класс, раскладывающий фотки по годам
 * Class PhotoShelf
 */
class PhotoShelf
{
    /**
     * Путь к папке с фотографиями
     * @var string
     */
    protected $directory;

    /**
     * Коллбек для вывода инфы на экран
     * @var Closure
     */
    protected $outFunction;

    /**
     * Список файлов
     * @var SplFileInfo[]
     */
    private $files = [];

    /**
     * PhotoShelf constructor.
     * @param string $directory Путь к папке с фотографиями
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Всё делает
     */
    public function go()
    {
        $this->setFilesList();
        $this->processFiles();
    }

    /**
     * Устанавливает обратный вызов для отладки
     * @param Closure $callback
     */
    public function setOutCallback(Closure $callback)
    {
        $this->outFunction = $callback;
    }

    /**
     * @param $str
     * @return bool|mixed
     */
    public function out($str)
    {
        if (!$this->outFunction) {
            echo $str . PHP_EOL;

            return false;
        }

        return $this->outFunction->__invoke($str);
    }

    /**
     * Заполняет список файлов для обработки
     */
    private function setFilesList()
    {
        $this->out('Scaning photo files...');

        $scanDirIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($scanDirIterator as $file) {
            /** @var SplFileInfo $file */
            if (strtolower($file->getExtension()) == 'jpg' || strtolower($file->getExtension()) == 'png')
                $this->addFile($file);
        }

        $this->out('Scaning photo files complete!');
    }

    /**
     * Добавляет файл в список обработки
     * @param SplFileInfo $fileName
     */
    private function addFile($fileName)
    {
        $this->files[] = $fileName;
    }

    /**
     * Обработка файлов и создание
     */
    private function processFiles()
    {
        if (empty($this->files))
            return false;

        $this->out('Moving photo files...');

        if (!empty($this->files)) {
            foreach ($this->files as $file) {
                $this->out($file);

                $exif = exif_read_data($file);

                if (!empty($exif) && isset($exif['DateTime'])) {
                    $fileDate = strtotime($exif['DateTime']);
                } else if (!empty($exif) && isset($exif['DateTaken'])) {
                    $fileDate = strtotime($exif['DateTaken']);
                } else if (!empty($exif) && isset($exif['DateTimeOriginal'])) {
                    $fileDate = strtotime($exif['DateTimeOriginal']);
                } else if (!empty($exif) && isset($exif['DateTimeDigitized'])) {
                    $fileDate = strtotime($exif['DateTimeDigitized']);
                } else if (!empty($exif) && isset($exif['DateModified'])) {
                    $fileDate = strtotime($exif['DateModified']);
                } else {
                    $fileDate = filemtime($file);
                }

                $this->out(date('Y-m-d H:i:s', $fileDate));

                if (!file_exists($this->directory . '/' . date('Y', $fileDate) . '/' . date('Y-m-d', $fileDate))) {
                    mkdir($this->directory . '/' . date('Y', $fileDate) . '/' . date('Y-m-d', $fileDate), 0777, true);
                }

                rename($file, $this->directory . '/' . date('Y', $fileDate) . '/' . date('Y-m-d', $fileDate) . '/' . basename($file));
            }
        }

        $this->out('Moving photo files complete!');

        return true;
    }
}
