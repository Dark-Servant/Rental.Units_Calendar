<?
namespace Bugs\Models\Log;

class Responsible extends Base
{
    public function getData(): array
    {
        if (empty($this->unit)) return [];
    
        return [
            $this->unit->id => [
                'EXTERNAL_ID' => $this->unit->external_id,
                'NAME' => $this->unit->name
            ]
        ];
    }
}