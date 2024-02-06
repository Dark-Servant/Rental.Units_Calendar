<?
namespace Bugs\Models\Log;

class Comment extends Base
{
    public function getData(): array
    {
        if (empty($this->unit)) return [];
    
        return [
            $this->unit->id => [
                'VALUE' => $this->unit->value,
                'DATE' => $this->unit->content_date->format(\Day::FORMAT),
                'PARENT_CONTENT' => current((new Content($this->unit->content))->getData())
            ]
        ];
    }
}