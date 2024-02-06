<?

class ChosenTechnic extends Models\InfoserviceBase
{
    static $belongs_to = [
        ['user', 'foreign_key' => 'user_id', 'class_name' => 'Responsible'],
        ['technic', 'foreign_key' => 'entity_id'],
        ['partner', 'foreign_key' => 'entity_id'],
    ];

    const FIELD_EXISTENCE_CONDITIONS = [
        'technic' => ['is_partner' => "0"],
        'partner' => ['is_partner' => "1"],
    ];
};