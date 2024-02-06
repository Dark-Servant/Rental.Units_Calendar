<?
namespace Bugs\Models\Solutions\Badhost\Comments\Log;

use Bugs\Models\Solutions\BadHost\Comments\Base as BadHost;
use Bugs\Models\Log\{
    Comment as CommentLog,
    Content as ContentLog,
    Technic as TechnicLog
};
use Log\Base as Logger;

class Base
{
    protected $badHost = null;

    public function __construct(BadHost $badHost)
    {
        $this->badHost = $badHost;
        $this->badHost->initMyTechnicData()->initPartnerData();
    }

    public function sendToLoggerDataForZero(): self
    {
        $data = $this->getDataForZeroContentComment();
        Logger::getMainInstance()->addNextValues(
            __METHOD__,
            '*********** ZERO COMMENTS:',
            $data
        );
        return $this;
    }
    
    public function sendToLoggerDataForMy(): self
    {
        $data = $this->getDataForMyTechnicContentComment();
        Logger::getMainInstance()->addNextValues(
            __METHOD__,
            '*********** MY TECHNIC DATA:',
            $data
        );
        return $this;
    }
    
    public function sendToLoggerDataForPartner(): self
    {
        $data = $this->getDataForPartnerContentComment();
        Logger::getMainInstance()->addNextValues(
            __METHOD__,
            '*********** PARTNER DATA:',
            $data
        );
        return $this;
    }

    public function sendToLoggerAllCommentCount(): self
    {
        Logger::getMainInstance()->addNextValues(
            __METHOD__,
            'ZERO COMMENT COUNT: ' . $this->getZeroCommentCount(),
            'MY TECHNIC COUNT: ' . $this->getMyTechnicContentCommentCount(),
            'PARTNER COUNT: ' . $this->getPartnerContentCommentCount(),
            'FULL COMMENT COUNT: ' . $this->getFullCommentCount(), ''
        );
        return $this;
    }

    public function getDataForZeroContentComment(): array
    {
        return static::getDataForCommentByIDs($this->badHost->getZeroContentCommentIDs());
    }

    public function getDataForMyTechnicContentComment(): array
    {
        $IDs = $this->badHost->getMyTechnicContentCommentIDs();
        $data = [];
        foreach ($IDs as $contentID => $commentIDs) {
            if (empty($data[$contentID]))
                $data[$contentID] = current((new ContentLog(\Content::find_by_id($contentID)))->getData());
            
            $data[$contentID]['COMMENTS'] = static::getDataForCommentByIDs($commentIDs);
        }
        return $data;
    }

    public function getDataForPartnerContentComment(): array
    {
        $IDs = $this->badHost->getPartnerContentCommentIDs();
        $data = [];
        foreach ($IDs as $technicID => $contentCommentIDs) {           
            $technicLog = (new TechnicLog(\Technic::find_by_id($technicID)))->getData();
            $data += $technicLog;

            $technicID = array_key_first($technicLog);
            foreach ($contentCommentIDs as $contentID => $commentIDs) {
                $contentLog = (new ContentLog(\Content::find_by_id($contentID)))->getData();
                $data[$technicID]['COURSE'][$contentID] = $contentLog[$contentID]
                                                        + ['TECHNIC_COMMENTS' => static::getDataForCommentByIDs($commentIDs)];
            }
        }

        return $data;
    }

    public static function getDataForCommentByIDs(array $IDs): array
    {
        if (empty($IDs)) return [];
    
        $data = [];
        $comments = \Comment::all(['conditions' => \Comment::getWithAddedConditions([], ['id' => $IDs])]);
        foreach ($comments as $comment) {
            $technicData = (new TechnicLog($comment->technic))->getData();
            if (empty($technicData)) continue;
     
            $data += $technicData;
            $unitData = &$data[array_key_first($technicData)];
    
            if (!is_array($unitData['COMMENTS'])) $unitData['COMMENTS'] = [];
            $unitData['COMMENTS'] += (new CommentLog($comment))->getData();
        }
        return $data;
    }

    public function getFullCommentCount(): int
    {
        return $this->getZeroCommentCount() + $this->getMyTechnicContentCommentCount() + $this->getPartnerContentCommentCount();
    }

    public function getZeroCommentCount(): int
    {
        static $result = null;
        return $result ?? $result = count($this->badHost->getZeroContentCommentIDs());
    }

    public function getMyTechnicContentCommentCount(): int
    {
        static $result = null;
        if (isset($result)) return $result;

        $result = 0;
        foreach ($this->badHost->getMyTechnicContentCommentIDs() as $commentIDs) {
            $result += count($commentIDs);
        }
        return $result;
    }

    public function getPartnerContentCommentCount(): int
    {
        static $result = null;
        if (isset($result)) return $result;

        $result = 0;
        foreach ($this->badHost->getPartnerContentCommentIDs() as $technicCommentIDs) {
            foreach ($technicCommentIDs as $commentIDs) {
                $result += count($commentIDs);
            }
        }
        return $result;
    }
}