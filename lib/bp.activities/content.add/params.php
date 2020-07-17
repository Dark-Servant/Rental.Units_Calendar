<?
return [
    'NAME' => [
        'ru' => $langValues['BPA_CONTENT_ADD_TITLE']
    ],
    'DESCRIPTION' => [
        'ru' => $langValues['BPA_CONTENT_ADD_DESCRIPTION']
    ],
    'PROPERTIES' => [
        'TECHNIC_ID' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_TECHNIC_ID']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_TECHNIC_ID']],
            'Type' => 'int',  // Число, в БД тип int
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'SPECIFICATION_ID' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_SPECIFICATION_ID']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_SPECIFICATION_ID']],
            'Type' => 'int',  // Число, в БД тип int
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'BEGIN_DATE' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_BEGIN_DATE']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_BEGIN_DATE']],
            'Type' => 'date',  // Дата, в БД тип date
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'FINISH_DATE' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_FINISH_DATE']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_FINISH_DATE']],
            'Type' => 'date',  // Дата, в БД тип date
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'DEAL_URL' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_DEAL_URL']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_DEAL_URL']],
            'Type' => 'string',  // Строка, в БД тип int
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'RESPONSIBLE_NAME' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_RESPONSIBLE_NAME']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_RESPONSIBLE_NAME']],
            'Type' => 'string',  // Строка, в БД тип int
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'CUSTOMER_NAME' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_CUSTOMER_NAME']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_CUSTOMER_NAME']],
            'Type' => 'string',  // Строка, в БД тип int
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'WORK_ADDRESS' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_WORK_ADDRESS']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_WORK_ADDRESS']],
            'Type' => 'string',  // Строка, в БД тип varchar
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'STATUS' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_STATUS']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_STATUS']],
            'Type' => 'string',  // Строка, в БД тип int
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'IS_CLOSED' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_IS_CLOSED']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_IS_CLOSED']],
            'Type' => 'bool',  // Да/Нет, в БД тип int
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
    ]
];