<?
namespace Bugs\Models\Solutions\BadHost\Comments;

use Bugs\SQLFile;

class PartnerContent
{
    protected $query;
    protected $techicContentComments = [];
    protected $commentIDs = [];
    
    protected $debugging = false;

    public function __construct(\PDOStatement $query)
    {
        $this->query = $query;
    }

    public function takeAwayOwnDataFromComments(array&$comments): self
    {
        $this->techicContentComments = [];
        $this->commentIDs = [];
        while ($content = $this->query->fetch()) {
            $this->takeAwayDataFromCommentsViaContent($comments, $content);
        }
        return $this;
    }

    protected function takeAwayDataFromCommentsViaContent(array&$comments, array $content): self
    {
        $newComments = [];
        foreach ($comments as $comment) {
            if (
                ($content['begin_date'] > $comment['content_date'])
                || ($content['finish_date'] < $comment['content_date'])
            ) {
                $newComments[] = $comment;

            } else {
                $this->techicContentComments[$content['technic_id']][$content['id']][] = $comment['id'];
                $this->commentIDs[] = $comment['id'];
            }
        }
        $comments = $newComments;
        return $this;
    }

    public function addTechnicContentCommentsToList(array&$result): self
    {
        foreach ($this->techicContentComments as $techicID => $contentComments) {
            foreach ($contentComments as $contentID => $commentIDs) {
                if (!is_array($result[$techicID][$contentID]))
                    $result[$techicID][$contentID] = [];
                
                array_push(
                    $result[$techicID][$contentID],
                    ...array_filter(
                            $commentIDs,
                            function($commentID) use($result, $techicID, $contentID) {
                                return !in_array($commentID, $result[$techicID][$contentID]);
                            }
                        )
                );
            }
        }
        return $this;
    }

    public function getTechnicContentComments(): array
    {
        return $this->techicContentComments;
    }

    public function getCommentIDs(): array
    {
        return $this->commentIDs;
    }
}