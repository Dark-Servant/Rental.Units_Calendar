<?

class Comment extends ActiveRecord\Model
{
    static $belongs_to = [
        ['user'], ['content'],
    ];
};