<?
$partner = null;
if (($partnerId = intval($this->values['PARTNER_ID']))) {
    $partner = Partner::find_by_external_id($partnerId);
    if (!$partner) $partner = new Partner;

    $partner->external_id = $partnerId;
    $partner->name = trim(
                        preg_replace(
                            '/\s*(?:\[[^\[\]]+\])+\s*/', ' ',
                            strip_tags($this->values['PARTNER_NAME'])
                        )
                    );
    $partner->save();
}

$technic = Technic::find_by_external_id($this->values['TECHNIC_ID']);
if (!$technic) $technic = new Technic();

$technic->external_id = $this->values['TECHNIC_ID'];
$technic->name = $this->values['NAME'];
$technic->state_number = $this->values['STATE_NUMBER'];
$technic->loading_capacity = $this->values['LOADING_CAPACITY'];
$technic->partner_id = $partner ? $partner->id : 0;
$technic->is_my = $this->values['IS_MY'];
$technic->is_visibility = $this->values['VISIBILITY'];
$technic->save();
