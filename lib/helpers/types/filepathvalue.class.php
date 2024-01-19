<?
namespace Types;

/**
 * Класс для работы с значением, где указана либо ссылка, либо путь
 * относительно корня портала
 */
class FilePathValue
{
    protected $path;
    protected $isURL = false;
    protected $hash = '';

    /**
     * Инициализация
     *
     * @param string $path - значение какого-то пути относительно корня портала или ссылка
     */
    function __construct(string $path)
    {
        $this->path = trim($path);
        $this->setType();
        $this->setHash();
    }

    /**
     * Установка чем является переданное в конструкторе значение
     *
     * @return self
     */
    protected function setType(): self
    {
        $this->isURL = preg_match('/^(https?)?\/\//i', $this->path);
        return $this;
    }

    /**
     * Установка хэша переданного в конструкторе значения. Для ССЫЛОК это хэш значения ССЫЛКИ,
     * а для ФАЙЛОВ, если значение указано вверно, это хэш содержимого
     *
     * @return self
     */
    protected function setHash(): self
    {
        if ($this->isURL) {
            $this->hash = md5($this->path);

        } else {
            $this->path = ltrim($this->path, '/\\');
            if ($this->path && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $this->path))
                $this->hash = md5_file($_SERVER['DOCUMENT_ROOT'] . '/' . $this->path);
        }
    
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->path;
    }

    /**
     * Возвращает является ли переданное в конструкторе значение ССЫЛКОЙ
     *
     * @return boolean
     */
    public function isURL(): bool
    {
        return $this->isURL;
    }

    /**
     * Возвращает хэш для переданного в конструкторе значения
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Возвращает переданное в конструкторе значение с указание в начале символа "/",
     * если это была не ССЫЛКА. Если переданное в конструкторе значение не было ни
     * ССЫЛКОЙ, ни правильным относительно корня сайта путем, то будет возвращено
     * значение NULL
     *
     * @return string
     */
    public function getResult(): ?string
    {
        if (!$this->hash) return null;
        if ($this->isURL) return $this->path;

        return '/' . $this->path;
    }
}