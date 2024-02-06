<?

class Partner extends Models\InfoserviceBase
{
    static $has_many = [
        ['technics'],
        ['userchoice', 'foreign_key' => 'entity_id', 'class_name' => 'ChosenTechnic', 'conditions' => 'is_partner = "1"']
    ];
    const CHILD_NAMES_FOR_DELETING = ['technics', 'userchoice'];
};