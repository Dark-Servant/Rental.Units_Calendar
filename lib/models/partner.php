<?

class Partner extends InfoserviceModel
{
    static $has_many = [
        ['technics'],
        ['userchoice', 'foreign_key' => 'entity_id', 'class_name' => 'ChosenTechnic', 'conditions' => 'is_partner = "1"']
    ];
};