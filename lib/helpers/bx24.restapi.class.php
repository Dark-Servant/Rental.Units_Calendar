<?
class BX24RestAPI
{
    private $authorizationData;
    private $answers;
    private $lastMethodName;
    private $logName;

    function __construct(array $authorizationData, string $logName = '')
    {
        global $langValues;

        if (
            empty($authorizationData['domain']) || empty($authorizationData['access_token'])
            || empty($authorizationData['refresh_token'])
        ) throw new Exception($langValues['ERROR_EMPTY_PARAMS']);

        $this->authorizationData = $authorizationData;
        $this->logName = trim($logName);
        if (!empty($this->logName) && file_exists($this->logName))
            @unlink($this->logName);
    }

    /**
     * Возвращает правильное название метода для REST API по переданному CamelCase-названию.
     * Например, "DiskStorageGetlist" станет "disk.storage.getlist"
     * 
     * @param  string $methodName - CamelCase-название метода
     * @return string
     */
    protected static function correctMethodName(string $methodName)
    {
        return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1.$2', $methodName));
    }

    /**
     * Делает запрос по REST API для конкретного метода с указанием параметров
     * 
     * @param  string $methodName - название метода REST API
     * @param  array  $params - параметры метода
     * @return array|null
     */
    protected function callRESTAPIMethod(string $methodName, array $params)
    {
        $curlUnit = curl_init();

        $url = 'https://' . $this->authorizationData['domain'] . '/rest/' . $methodName;
        curl_setopt($curlUnit, CURLOPT_URL, $url);
        curl_setopt($curlUnit, CURLOPT_POST, 1);
        curl_setopt($curlUnit, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlUnit, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlUnit, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt(
            $curlUnit, CURLOPT_POSTFIELDS,
            http_build_query($params + ['auth' => $this->authorizationData['access_token']])
        );
        $result = curl_exec($curlUnit);
        curl_close($curlUnit);

        return $result ? json_decode($result, true) : [];
    }

    /**
     * Обработчие несуществующих методов класса. Обрабатывает только методы,
     * вызванные по шаблону call<CamelCase-название метода REST API>
     * 
     * @param string $methodName - название метода
     * @param array $params -  параметры, переданные методу
     * @return array|null
     * @throws
     */
    function __call(string $methodName, array $params)
    {
        global $langValues;

        if (!preg_match('/^call(\w+)$/', $methodName, $methodParts))
             throw new Exception($langValues['ERROR_BAD_RESTAPI_METHOD_NAME']);

        $this->lastMethodName = self::correctMethodName($methodParts[1]);
        $realParams = current($params) ?: [];
        $this->answers[$this->lastMethodName] = [
            'parameters' => $realParams,
            'result' => $this->callRESTAPIMethod($this->lastMethodName, $realParams)
        ];

        if ($this->logName)
            file_put_contents(
                $this->logName,
                $this->lastMethodName . PHP_EOL . 
                print_r($this->answers[$this->lastMethodName], true) . PHP_EOL . PHP_EOL,
                FILE_APPEND
            );
        return $this->answers[$this->lastMethodName]['result'];
    }

    /**
     * Возвращает ответ последнего вызванного метода
     * 
     * @return array|null
     */
    function getLastAnswer()
    {
        return $this->answers[$this->lastMethodName];
    }
}