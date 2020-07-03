<?

class Content extends ActiveRecord\Model
{
    static $belongs_to = [
        ['day'],
        ['technic'],
        ['responsible', 'foreign_key' => 'responsible_id', 'class_name' => 'User'],
        ['customer', 'foreign_key' => 'customer_id', 'class_name' => 'User'],
    ];
};