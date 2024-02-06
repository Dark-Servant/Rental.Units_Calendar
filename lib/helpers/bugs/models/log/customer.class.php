<?
namespace Bugs\Models\Log;

class Customer extends Base
{
    public function getData(): array
    {
        if (empty($this->unit)) return [];
    
        return [
            $this->unit->id => [
                'NAME' => $this->unit->name
            ]
        ];
    }
}