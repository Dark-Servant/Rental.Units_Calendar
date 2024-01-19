<?
namespace Frontend\AutoLoader;

/**
 * Undocumented class
 */
class Base
{
    protected $files = [];
    protected $result = [];

    /**
     * Undocumented function
     *
     * @param array $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * Undocumented function
     *
     * @return self
     */
    public function prepareFiles(): self
    {
        $path = new Path(static::getBaseTemplates());
        foreach ($this->files as $categoryCode => $files) {
            if (!is_array($files)) $files = [$files];

            $this->prepareFilesViaTemplates($files, $path->getFilteredViaCode($categoryCode));
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    protected function getBaseTemplates(): array
    {
        return Path::getBaseTemplates();
    }

    /**
     * Undocumented function
     *
     * @param array $files
     * @param array $templates
     * @return self
     */
    public function prepareFilesViaTemplates(array $files, array $templates): self
    {
        foreach ($files as $file) {
            $includingFile = new File($file);
            if (!$includingFile->initViaTemplates($templates)->isInited()) continue;

            $this->result[$includingFile->getHash()] = $includingFile->getResultValue();
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function __toString()
    {
        return implode(PHP_EOL, $this->getResult());
    }
}