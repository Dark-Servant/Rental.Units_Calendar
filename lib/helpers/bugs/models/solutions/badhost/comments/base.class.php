<?
namespace Bugs\Models\Solutions\BadHost\Comments;

use SQL\FileByLastFolderIniting;

class Base
{
    use FileByLastFolderIniting;

    protected $zeroContentCommentIDs = false;
    protected $myTechnicContentCommentIDs = [];
    protected $partnerContentCommentIDs = [];

    public function initMyTechnicData(): self
    {
        $this->myTechnicContentCommentIDs = [];
        if (!$this->sqlCode->isInited()) return $this;
        if ($this->zeroContentCommentIDs === false) $this->initZeroContentCommentData();

        $result = $this->sqlCode->queryMyTechics([':IDS:' => $this->zeroContentCommentIDs]);
        while ($comment = $result->fetch()) {
            $this->removeFromZeroContentCommentThisID($comment['id']);
            $this->myTechnicContentCommentIDs[$comment['cid']][] = $comment['id'];
        }
        return $this;
    }

    public function getMyTechnicContentCommentIDs(): array
    {
        return $this->myTechnicContentCommentIDs;
    }

    public function initPartnerData(): self
    {
        $this->partnerContentCommentIDs = [];
        foreach ($this->getPartnerCommentData() as $partnerID => $comments) {
            $this->removeFromZeroContentCommentThisIDs(
                    (new PartnerContent($this->sqlCode->queryPartnerTechincContents([':PARTNER_ID:' => $partnerID])))
                        ->takeAwayOwnDataFromComments($comments)
                        ->addTechnicContentCommentsToList($this->partnerContentCommentIDs)
                        ->getCommentIDs()
                );
        }
        return $this;
    }

    public function getPartnerContentCommentIDs(): array
    {
        return $this->partnerContentCommentIDs;
    }

    protected function getPartnerCommentData(): array
    {
        if (!$this->sqlCode->isInited()) return [];
        if ($this->zeroContentCommentIDs === false) $this->initZeroContentCommentData();
        
        $partnerComments = [];
        $result = $this->sqlCode->queryPartnerTechnics([':IDS:' => $this->zeroContentCommentIDs]);
        while ($comment = $result->fetch()) {
            $partnerComments[$comment['partner_id']][] = [
                'id' => $comment['id'], 
                'content_date' => $comment['content_date']
            ];
        }
        return $partnerComments;
    }

    protected function removeFromZeroContentCommentThisIDs(array $IDs): self
    {
        foreach ($IDs as $ID) {
            $this->removeFromZeroContentCommentThisID($ID);
        }
        return $this;
    }

    protected function removeFromZeroContentCommentThisID(int $ID): self
    {
        $zeroCommentPosition = array_search($ID, $this->zeroContentCommentIDs);
        if ($zeroCommentPosition !== false) array_splice($this->zeroContentCommentIDs, $zeroCommentPosition, 1);
        return $this;
    }

    public function initZeroContentCommentData(): self
    {
        $this->zeroContentCommentIDs = [];
        $result = $this->sqlCode->queryContentBadInterval();        
        while ($comment = $result->fetch()) {
            $this->zeroContentCommentIDs[] = $comment['id'];
        }
        return $this;
    }

    public function getZeroContentCommentIDs(): array
    {
        return $this->zeroContentCommentIDs ?: [];
    }

    public function updateZeroContentComments(): self
    {
        if (empty($this->zeroContentCommentIDs)) return $this;
        
        $this->sqlCode->queryUpdateZeroContentComment([':IDS:' => $this->zeroContentCommentIDs]);
        return $this;
    }

    public function updateMyTechnicComments(): self
    {
        if (empty($this->myTechnicContentCommentIDs)) return $this;
        
        foreach ($this->myTechnicContentCommentIDs as $contentID => $commentIDs) {
            $this->sqlCode->queryUpdateMyTechnicComment([
                        ':CONTENT_ID:' => $contentID,
                        ':IDS:' => $commentIDs
                    ]);
        }
        return $this;
    }

    public function updatePartnerComments(): self
    {
        if (empty($this->partnerContentCommentIDs)) return $this;
        
        foreach ($this->partnerContentCommentIDs as $technicID => $contentCommentIDs) {
            foreach ($contentCommentIDs as $contentID => $commentIDs) {
                $this->sqlCode->queryUpdatePartnerComment([
                            ':CONTENT_ID:' => $contentID,
                            ':TECHNIC_ID:' => $technicID,
                            ':IDS:' => $commentIDs
                        ]);
            }
        }
        return $this;
    }
}