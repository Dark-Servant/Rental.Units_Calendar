<?

function getTechnicData(Technic $unit): array
{
    if ($unit->is_my) {
        if (empty($data['TECHNICS'][$unit->id]))
                return [
                    'TECHNIC' . $unit->id => [
                        'NAME' => $unit->name . ' (' . $unit->state_number . ')'
                    ]
                ];

    } elseif ($unit->partner_id) {
        return [
            'PARTNER' . $unit->partner_id => [
                'PARTNER_NAME' => $unit->partner->name,
                'NAME' => $unit->name
            ]
        ];
    }
    return [];
}

function getContentData(Content $unit = null): array
{
    if (empty($unit)) return [];

    return [
        $unit->id => [
            'NAME' => $unit->customer->name,
            'INTERVAL' => sprintf(
                                '%s - %s',
                                $unit->begin_date->format(Day::FORMAT),
                                $unit->finish_date->format(Day::FORMAT)
                            )
        ] + getTechnicData($unit->technic)
    ];
}

function createDataForZero(array $IDs): array
{
    if (empty($IDs)) return [];

    $data = [];
    $comments = Comment::all(['conditions' => Comment::getWithAddedConditions([], ['id' => $IDs])]);
    foreach ($comments as $comment) {
        $technicData = getTechnicData($comment->technic);
        if (empty($technicData)) continue;
 
        $data += $technicData;
        $unitData = &$data[array_key_first($technicData)];

        $unitData['COMMENTS'][$comment->id] = [
            'VALUE' => $comment->value,
            'DATE' => $comment->content_date->format(Day::FORMAT),
            'PARENT_CONTENT' => current(getContentData($comment->content))
        ];
    }
    return $data;
}

function createDataForMy(array $IDs): array
{
    $data = [];
    foreach ($IDs as $contentID => $commentIDs) {
        if (empty($data[$contentID]))
            $data[$contentID] = current(getContentData(Content::find_by_id($contentID)));
        
        $data[$contentID]['COMMENTS'] = createDataForZero($commentIDs);
    }
    return $data;
}

function createDataForPartner(array $IDs): array
{
    $data = [];
    foreach ($IDs as $contentID => $technicCommentIDs) {
        if (empty($data[$contentID]))
            $data[$contentID] = current(getContentData(Content::find_by_id($contentID)));
        
        foreach ($technicCommentIDs as $technicID => $commentIDs) {
            $technicData = getTechnicData(Technic::find_by_id($technicID));
            $code = array_key_first($technicData);
            if (!is_array($data[$contentID]['TO_TECHNIC'])) $data[$contentID]['TO_TECHNIC'] = [];
            $data[$contentID]['TO_TECHNIC'][$code] = $technicData[$code] + ['COMMENTS' => createDataForZero($commentIDs)];
        }
    }
    return $data;
}