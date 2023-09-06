<?
$technic = Technic::find_by_external_id($this->values['TECHNIC_ID']);
if (!$technic) throw new Exception(strtr(
                    $langValues['ERROR_PARENT_TECHNIC_OF_CONTENT'],
                    ['#ID#' => $this->values['TECHNIC_ID']]
                ));

$content = Content::find_by_specification_id($this->values['SPECIFICATION_ID']);
if (!$content) $content = new Content();

$content->specification_id = $this->values['SPECIFICATION_ID'];
$content->technic_id = $technic->id;
$content->begin_date = $this->values['BEGIN_DATE'];
$content->finish_date = $this->values['FINISH_DATE'];
$content->deal_url = $this->values['DEAL_URL'];

$responsible = Responsible::find_by_name($this->values['RESPONSIBLE_NAME']);
if (!$responsible) {
    $responsible = new Responsible(['name' => $this->values['RESPONSIBLE_NAME']]);
    $responsible->save();
}

$customer = Customer::find_by_name($this->values['CUSTOMER_NAME']);
if (!$customer) {
    $customer = new Customer(['name' => $this->values['CUSTOMER_NAME']]);
    $customer->save();
}

$content->responsible_id = $responsible->id;
$content->customer_id = $customer->id;
$content->work_address = $this->values['WORK_ADDRESS'];
$content->status = $this->values['STATUS'];
$content->is_closed = $this->values['IS_CLOSED'];
$content->saveAsUnique();

