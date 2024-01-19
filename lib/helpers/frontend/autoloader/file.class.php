<?
namespace Frontend\AutoLoader;

use Types\FileName;
use Types\FilePathValue;

/**
 * Класс для загрузки файла через ссылку или по указанному пути через API Битрикса,
 * причем освобождая разработчика от необходимости помнить/искать какой метод надо
 * вызывать для подключения скрипта или css-стилей.
 * Использование, либо
 *      File::initViaValue(<ссылка или путь относительно ядра портала и имя файла с расширением>);
 * либо
 *      (new File(<имя файла с расширением и необязательным путем/ссылкой>))
 *          ->initViaTemplates(<список шаблонов путей для поиска файла или просто список ссылок>);
 * либо
 *      (new File(<имя файла с расширением и необязательным путем/ссылкой>))
 *          ->initViaTemplate(<шаблон пути для поиска файла или просто ссылка>);
 * 
 * где
 *      <шаблон пути для поиска...> - это шаблон либо обычного пути, относительно корня портала
 *      либо шаблон ссылки, причем у ссылки проверяется только то, начинается ли ее значение с
 *      с http(s), а не реальное существование данных по ней. В шаблоне можно использовать
 *      специальные слова
 *          [extention] - заменяется на расширение файла;
 *          [module_id] - заменяется на идентификтор модуля.
 */
class File
{
    protected $fileName;
    protected $hash = '';
    protected $resultValue = null;
    protected static $includedHash = [];

    /**
     * Инициализация объекта, передавать можно только название файла, из него будет
     * получена информация о расширении файла, что потом повлияет на то, как подключить
     * файл
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->fileName = new FileName($name);
    }

    /**
     * Показывает есть ли у файла расширение
     *
     * @return boolean
     */
    public function getFileName():FileName
    {
        return $this->fileName;
    }

    /**
     * Подключает файл, чье имя передано в 
     *
     * @param array $templates
     * @return self
     */
    public function initViaTemplates(array $templates): self
    {
        foreach ($templates as $templatePath) {
            if ($this->initViaTemplate($templatePath)->isInited())
                break;
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $templatePath
     * @return self
     */
    public function initViaTemplate(string $templatePath): self
    {
        $this->initViaFilePathValue($this->getFilePathValueViaTemplate($templatePath));
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $templatePath
     * @return FilePathValue
     */
    public function getFilePathValueViaTemplate(string $templatePath): FilePathValue
    {
        $pathFullName = static::prepareTemplateValue(
                            trim($templatePath, '\\/'),
                            [
                                '[extention]' => $this->fileName->getExtentionValue()
                            ]
                        );
        return new FilePathValue($pathFullName . '/' . $this->fileName->getFullValue());
    }

    /**
     * Undocumented function
     *
     * @param string $templateValue
     * @param array $specialValues
     * @return string
     */
    protected static function prepareTemplateValue(string $templateValue, array $specialValues): string
    {
        return strtr($templateValue, $specialValues);
    }
    
    /**
     * Undocumented function
     *
     * @param FilePathValue $filePath
     * @return self
     */
    public function initViaFilePathValue(FilePathValue $filePath): self
    {
        global $APPLICATION;

        $this->hash = '';
        $hash = $filePath->getHash();
        if (!$hash) return $this;

        if (empty(self::$includedHash[$hash])) {
            ob_start();
            switch ($this->fileName->getExtentionValue()) {
                case 'js':?>
<script src="<?=static::getURLStart() . $filePath->getResult()?>?<?=URL_SCRIPT_FINISH?>"></script><?
                    break;
                
                case 'css':?>
<link rel="stylesheet" href="<?=static::getURLStart() . $filePath->getResult()?>?<?=URL_SCRIPT_FINISH?>"><?
                    break;
            }
            $value = trim(ob_get_clean());
            if (empty($value)) return $this;

            self::$includedHash[$hash] = $value;

        } else {
            $value = self::$includedHash[$hash];
        }
        $this->hash = $hash;
        $this->resultValue = $value;

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function getURLStart()
    {
        static $urlStart = false;
        if (!$urlStart) $urlStart = '//' . $_SERVER['HTTP_HOST'] . rtrim(APPPATH, '/');

        return $urlStart;
    }

    /**
     * Undocumented function
     *
     * @return string|null
     */
    public function getResultValue(): ?string
    {
        return $this->resultValue;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function isInited(): bool
    {
        return !empty($this->hash);
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Undocumented function
     *
     * @param string $filePath
     * @return self
     */
    public static function initViaValue(string $filePath): self
    {
        return (new static($filePath))->initViaFilePathValue(new FilePathValue($filePath));
    }
}