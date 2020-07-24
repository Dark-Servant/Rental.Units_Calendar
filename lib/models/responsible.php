<?

class Responsible extends InfoserviceModel
{
    static $has_many = [
        ['comments'],
        ['contents']
    ];
};