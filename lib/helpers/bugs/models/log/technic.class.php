<?
namespace Bugs\Models\Log;

class Technic extends Base
{
    public function getData(): array
    {
        if ($this->unit->is_my) {
            return [
                'TECHNIC' . $this->unit->id => [
                    'NAME' => $this->unit->name . ' (' . $this->unit->state_number . ')'
                ]
            ];
    
        } elseif ($this->unit->partner_id) {
            return [
                'PARTNER' . $this->unit->partner_id => [
                    'PARTNER_NAME' => $this->unit->partner->name,
                    'NAME' => $this->unit->name
                ]
            ];
        }
        return [];
    }
}