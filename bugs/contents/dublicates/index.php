<?
use Bugs\Models\Solutions\Dublicates\Data\{
    Responsibles\Base as DublicateResponsible,
    Customers\Base as DublicateCustomer,
    Contents\Base as DublicateContent
};
use Bugs\Models\Solutions\Dublicates\Log\Base as Outer;
use SQL\Query;
use SQL\Models\Relative\Belonging;
use Log\Base as Logger;

require __DIR__ . '/../../../configs/settings.php';

if (!defined('NOT_CHANGE_DUBLICATES')) define('NOT_CHANGE_DUBLICATES', false);

$mainLogger = Logger::getMainInstance();

Logger::setMainInstance($logger = new Logger(__DIR__ . '/log'));
$logger->addRightsForClassNames(
    Query::class,
    Belonging::class,
    Outer::class
);

$outer = (new Outer)->setOtherOriginalIDs(require __DIR__ . '/replace_original_ids.php')
            ->addUnit(new DublicateResponsible)
            ->addUnit(new DublicateCustomer)
            ->addUnit(new DublicateContent)
;

if ($outer->getCountOfGroups()) {
    if (NOT_CHANGE_DUBLICATES) Query::activateSending(false);

    $outer->sendToLog()->replaceDublicates();
    
    Query::activateSending();

} else {
	$logger->deleteFilePath();
}
Logger::setMainInstance($mainLogger);