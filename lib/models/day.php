<?

class Day extends ActiveRecord\Model
{
    static $has_many = [
        ['contents']
    ];
};