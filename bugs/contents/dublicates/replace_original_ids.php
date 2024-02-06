<?
use Bugs\Models\Solutions\Dublicates\Data\{
    Responsibles\Base as DublicateResponsible,
    Customers\Base as DublicateCustomers,
    Contents\Base as DublicateContents
};

/**
 * <название класса для поиска дубликатов> => [
 *      <символьный код категории> => <ID записи, определенной как копия>
 * ]
 */

return [
    DublicateResponsible::class => [
        //
    ],

    DublicateCustomers::class => [
        //
    ],

    DublicateContents::class => [
        '150339' => '7817',
        '143381' => '7358',
        '143358' => '7350',
        '137213' => '7033',
    ],
];