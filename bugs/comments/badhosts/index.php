<?
use Bugs\Models\Solutions\Badhost\Comments\{
	Log\Base as Outer,
	Base as BadHost
};
use SQL\Query as Query;
use Log\Base as Logger;

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
	$logFiles['comments_badhosts.txt'] = [
		'title' => 'Комментарии с неправильными контентом и/или техникой',
		'path' => $logger->getFilePath()
	];

} else {
	$logger->deleteFilePath();
}
Logger::setMainInstance($mainLogger);