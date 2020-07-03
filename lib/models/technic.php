<?

class Technic extends ActiveRecord\Model
{
    static $has_many = [
        ['contents']
    ];
};