<?
class BX24RestAPI
{
    private $authorizationData;
    private $answers;
    private $lastMethodName;
    private $logName;
    private $url = 'https://';
    private $defaultParams = [];

    function __construct(array $authorizationData, string $logName = '')
    {
        global $langValues;

        if (
            empty($authorizationData['domain'])
            || (
                empty($authorizationData['access_token'])
                && (
                    empty($authorizationData['webhook_token'])
                    || empty($authorizationData['webhook_userid'])
                )
            )
        ) throw new Exception($langValues['ERROR_EMPTY_PARAMS']);

        $this->logName = trim($logName);
        if (!empty($this->logName) && file_exists($this->logName))
            @unlink($this->logName);

        $this->authorizationData = $authorizationData;
        $this->url .= $authorizationData['domain'] . '/rest/';
        if (empty($authorizationData['access_token'])) {
            $this->url .= $authorizationData['webhook_userid'] . '/'
                        . $authorizationData['webhook_token'] . '/';

        } else {
            $this->defaultParams = ['auth' => $authorizationData['access_token']];
        }
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
        curl_setopt($curlUnit, CURLOPT_URL, $this->url . $methodName);
        curl_setopt($curlUnit, CURLOPT_POST, 1);
        curl_setopt($curlUnit, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlUnit, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlUnit, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt(
            $curlUnit, CURLOPT_POSTFIELDS,
            http_build_query($this->defaultParams + $params)
        );
        $result = curl_exec($curlUnit);
        curl_close($curlUnit);

        return $result ? json_decode($result, true) : [];
    }

    /**
     * Получает ответ от ранее указанного метода REST API, запоминает ответ, название метода и
     * его параметры, которые переданы, отправляет в лог результат, если он был указан, и возвращает
     * этот результат
     * 
     * @param array $params - параметры, переданные методу
     * @return array|null
     */
    protected function getMethodResult(array $params)
    {
        $this->answers[$this->lastMethodName] = [
            'parameters' => $params,
            'result' => $this->callRESTAPIMethod($this->lastMethodName, $params)
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
     * Обработчие несуществующих методов класса. Обрабатывает только методы,
     * вызванные по шаблону call<CamelCase-название метода REST API>
     * 
     * @param string $methodName - название метода
     * @param array $params - параметры, переданные методу
     * @return array|null
     * @throws
     */
    function __call(string $methodName, array $params)
    {
        global $langValues;

        if (!preg_match('/^call(\w+)$/', $methodName, $methodParts))
             throw new Exception($langValues['ERROR_BAD_RESTAPI_METHOD_NAME']);

        $this->lastMethodName = self::correctMethodName($methodParts[1]);
        return $this->getMethodResult(current($params) ?: []);
    }

    /**
     * У методов, которые возвращают часть данных, хотя согласно фильтру таких данных
     * больше, в ответе отмечается, что есть еще, и чтобы не отправлять новый запрос с
     * другими параметрами и тем же фильтром, чтобы получить следуюшие данные, можно
     * просто вызвать этот метод
     * 
     * @return array|null
     */
    function next(bool $reCreateLog = false)
    {
        if (
            empty($this->lastMethodName)
            || empty($this->answers[$this->lastMethodName]['result']['next'])
        ) return null;

        if ($reCreateLog && !empty($this->logName) && file_exists($this->logName))
            @unlink($this->logName);

        return $this->getMethodResult(
                            ['start' => $this->answers[$this->lastMethodName]['result']['next']]
                            + $this->answers[$this->lastMethodName]['parameters']
                        );
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