<?
namespace Bugs\Models\Log;

class Content extends Base
{
    public function getData(): array
    {
        if (empty($this->unit)) return [];
    
        return [
            $this->unit->id => [
                'SORT' => $this->unit->sort,
                'CUSTOMER_NAME' => $this->unit->customer->name,
                'DEAL_URL' => $this->unit->deal_url,
                'INTERVAL' => sprintf(
                                    '%s - %s',
                                    $this->unit->begin_date->format(\Day::FORMAT),
                                    $this->unit->finish_date->format(\Day::FORMAT)
                                )
            ] + (new Technic($this->unit->technic))->getData()
        ];
    }
}