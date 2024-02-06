<?
use Bugs\Models\Solutions\Badhost\Comments\{
	Log\Base as Outer,
	Base as BadHost
};
use SQL\Query as Query;
use Log\Base as Logger;

require_once __DIR__ . '/../../../configs/settings.php';

if (!defined('NOT_CHANGE_COMMENT_HOST')) define('NOT_CHANGE_COMMENT_HOST', false);

$mainLogger = Logger::getMainInstance();
Logger::setMainInstance($logger = new Logger(__DIR__ . '/log'));
$logger->addRightsForClassNames(
    Query::class,
    Outer::class
);

$badHost = new BadHost;
$outer = new Outer($badHost);
if ($outer->getFullCommentCount()) {
	$outer->sendToLoggerDataForZero()
		  ->sendToLoggerDataForMy()
		  ->sendToLoggerDataForPartner()
		  ->sendToLoggerAllCommentCount();

	if (NOT_CHANGE_COMMENT_HOST) Query::activateSending(false);

	$badHost->updateZeroContentComments()->updateMyTechnicComments()->updatePartnerComments();

	Query::activateSending();

} else {
	$logger->deleteFilePath();
}
Logger::setMainInstance($mainLogger);