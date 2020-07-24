<?

class ChosenTechnics extends InfoserviceModel
{
    static $belongs_to = [
        ['user', 'foreign_key' => 'user_id', 'class_name' => 'Responsible'],
    ];
};