<?
namespace Frontend\AutoLoader;

class Path
{
    const AJAX = ':ajax';
    const CLASSES = ':classes';
    const CLASSES_VUE = ':classes/vue';
    const NODEJS = ':nodejs';
    const WORKERS = ':workers';
    const WORKERS_VUE = ':workers/vue';
    const SOLUTION = ':solution/solution';
    const SOLUTION_CLASSES = ':solution/solution/classes';
    const SOLUTION_VUE = ':solution/solution/vue';
    const SOLUTION_VUE_COMPONENTS = ':solution/solution/vue/components';
    const SIMPLE = ':simple';

    protected $templates;

    /**
     * Undocumented function
     *
     * @param array $templates
     */
    public function __construct(array $templates = [])
    {
        $this->setTemplates($templates);
    }

    /**
     * Undocumented function
     *
     * @param array $templates
     * @return self
     */
    public function setTemplates(array $templates): self
    {
        $this->templates = [];
        return $this->addTemplates($templates);
    }

    /**
     * Undocumented function
     *
     * @param array $templates
     * @return self
     */
    public function addTemplates(array $templates): self
    {
        $this->templates += array_filter(
                                $templates,
                                static function($template) {
                                    return is_string($template);
                                }
                            );
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param [type] $code
     * @return array
     */
    public function getFilteredViaCode($code): array
    {
        if (!is_string($code)) return array_values($this->templates);
    
        $codeLength = strlen($code);
        return array_values(
                    array_filter(
                        $this->templates,
                        static function($unitCode) use($code, $codeLength) {
                            if (
                                !is_string($unitCode)
                                || ($codeLength > strlen($unitCode))
                            ) return false;
    
                            $firstPart = substr($unitCode, 0, $codeLength);
                            return ($code == $firstPart) && (!$unitCode[$codeLength] || ($unitCode[$codeLength] == '/'));
                        },
                        ARRAY_FILTER_USE_KEY
                    )
                );
    }

    /**
     * Возвращает список шаблонов путей, по которым надо искать файлы (*.js или *.css).
     * Результат используется методом includeFiles, можно поправить в классах, чтобы установить
     * новые шаблоны путей или дополнить список шаблонов.
     * В шаблонах можно используются макросы:
     *      - [extention]. расширение файла;
     *      - [module_id]. идентификатор модуля;
     * !!! Каждый шаблон пути в списке должен начинаться и заканчиваться с символа "/"
     *
     * @return array
     */
    public static function getBaseTemplates(): array
    {
        static $places = [];
        if (!empty($places)) return $places;

        $places = [
            static::AJAX => 'js/ajax',
            static::CLASSES => 'js/classes',
            static::CLASSES_VUE => 'js/classes/vue',
            static::NODEJS => 'js/external/node_modules',
            static::WORKERS => 'js/workers',
            static::WORKERS_VUE => 'js/workers/vue',
            static::SOLUTION => '[extention]/solutions',
            static::SOLUTION_CLASSES => '[extention]/solutions/classes',
            static::SOLUTION_VUE => '[extention]/solutions/vue',
            static::SOLUTION_VUE_COMPONENTS => '[extention]/solutions/vue/components',
            static::SIMPLE => '[extention]',
        ];

        return $places;
    }
}