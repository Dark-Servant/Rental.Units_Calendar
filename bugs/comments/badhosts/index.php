<?
$setting = require __DIR__ . '/../../../configs/settings.php';

use Bugs\Comments\BadHost;

require_once __DIR__ . '/outtestdata.php';


$badHost = new BadHost;
$badHost->initMyTechnicData()->initPartnerData();

echo '*********** ZERO COMMENTS:' . PHP_EOL;
print_r(createDataForZero($badHost->getZeroContentCommentIDs()));

echo '*********** MY TECHNIC DATA:' . PHP_EOL;
print_r(createDataForMy($badHost->getMyTechnicContentCommentIDs()));

echo '*********** PARTNER DATA:' . PHP_EOL;
print_r(createDataForPartner($badHost->getPartnerContentCommentIDs()));

$commentCount = count($badHost->getZeroContentCommentIDs());
foreach ($badHost->getMyTechnicContentCommentIDs() as $commentIDs) {
	$commentCount += count($commentIDs);
}
foreach ($badHost->getPartnerContentCommentIDs() as $technicCommentIDs) {
	foreach ($technicCommentIDs as $commentIDs) {
	        $commentCount += count($commentIDs);
	}
}
echo 'COMMENT COUNT: ' . $commentCount . PHP_EOL;

//$badHost->updateZeroContentComments()->updateMyTechnicComments()->updatePartnerComments();
