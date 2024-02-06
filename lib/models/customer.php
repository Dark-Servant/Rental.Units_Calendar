<?

class Customer extends Models\InfoserviceBase
{
    static $has_many = [
        ['contents']
    ];

    const CHILD_NAMES_FOR_DELETING = ['contents'];
};