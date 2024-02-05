<?
namespace Bugs\Comments\BadHost;

use Bugs\SQLFile;

class PartnerContent
{
    protected $query;
    protected $contentTechnicComments = [];
    protected $commentIDs = [];
    
    protected $debugging = false;

    public function __construct(\PDOStatement $query)
    {
        $this->query = $query;
    }

    public function takeAwayOwnDataFromComments(array&$comments): self
    {
        $this->contentTechnicComments = [];
        $this->commentIDs = [];
        while ($content = $this->query->fetch()) {
            $this->takeAwayDataFromCommentsViaContent($comments, $content);
        }
        return $this;
    }

    protected function takeAwayDataFromCommentsViaContent(array&$comments, array $content)
    {
        $newComments = [];
        foreach ($comments as $comment) {
            if (
                ($content['begin_date'] > $comment['content_date'])
                || ($content['finish_date'] < $comment['content_date'])
            ) {
                $newComments[] = $comment;

            } else {
                $this->contentTechnicComments[$content['id']][$content['technic_id']][] = $comment['id'];
                $this->commentIDs[] = $comment['id'];
            }
        }
        $comments = $newComments;
    }

    public function getContentTechnicComments(): array
    {
        return $this->contentTechnicComments;
    }

    public function getCommentIDs(): array
    {
        return $this->commentIDs;
    }
}