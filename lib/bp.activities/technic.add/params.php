<?
return [
    'NAME' => [
        'ru' => $langValues['BPA_TECHNIC_ADD_TITLE']
    ],
    'DESCRIPTION' => [
        'ru' => $langValues['BPA_TECHNIC_ADD_DESCRIPTION']
    ],
    'PROPERTIES' => [
        'TECHNIC_ID' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_TECHNIC_ID']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_TECHNIC_ID']],
            'Type' => 'int',  // Число
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'NAME' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_NAME']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_NAME']],
            'Type' => 'string',  // Строка, в БД тип - varchar
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'STATE_NUMBER' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_STATE_NUMBER']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_STATE_NUMBER']],
            'Type' => 'string',  // Строка, в БД тип - varchar
            'Required' => 'N',
            'Multiple' => 'N',
        ],
        'LOADING_CAPACITY' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_LOADING_CAPACITY']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_LOADING_CAPACITY']],
            'Type' => 'int',  // Число, в БД тип - int
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'PARTNER_ID' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_PARTNER_ID']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_PARTNER_ID']],
            'Type' => 'int',  // Число, в БД тип - int
            'Required' => 'N',
            'Multiple' => 'N',
        ],
        'PARTNER_NAME' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_PARTNER_NAME']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_PARTNER_NAME']],
            'Type' => 'string',  // Строка, в БД тип - varchar
            'Required' => 'N',
            'Multiple' => 'N',
        ],
        'IS_MY' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_IS_MY']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_IS_MY']],
            'Type' => 'bool',  // Да/Нет, в БД тип - int
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
        'VISIBILITY' => [
            'Name' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_VISIBILITY']],
            'Description' => ['ru' => $langValues['BPA_CONTENT_ADD_PARAM_VISIBILITY']],
            'Type' => 'bool',  // Да/Нет, в БД тип - int
            'Required' => 'Y',
            'Multiple' => 'N',
        ],
    ]
];