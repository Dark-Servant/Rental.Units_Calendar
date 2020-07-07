<?

class User extends ActiveRecord\Model
{
    static $has_many = [
        ['comments'],
        ['responsible_contents', 'foreign_key' => 'responsible_id', 'class_name' => 'Content'],
        ['customer_contents', 'foreign_key' => 'customer_id', 'class_name' => 'Content'],
    ];
};