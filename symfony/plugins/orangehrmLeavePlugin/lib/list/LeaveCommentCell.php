<?php

class LeaveCommentCell extends Cell {
    
    public function __toString() {
        
        $name = $this->getPropertyValue('namePattern');
        $id = $this->getPropertyValue('namePattern');
        
        $imageHTML = tag('img', array(
            'src' => public_path('../../themes/orange/icons/callout-left.png'),
            'title' => 'Click here to edit',
            'alt' => 'Edit',
            'class' => 'callout dialogInvoker',
        ));

        $placeholderGetters = $this->getPropertyValue('placeholderGetters');
        $id = $this->generateAttributeValue($placeholderGetters, $this->getPropertyValue('idPattern'));
        $name = $this->generateAttributeValue($placeholderGetters, $this->getPropertyValue('namePattern'));        

        $comments = $this->getValue();
        $commentExtract = '';
        $allComments = '';
                
        // show last comment only
        if (count($comments) > 0) {
            
            foreach ($comments as $comment) {
                $created = new DateTime($comment->getCreated());
                $createdAt = set_datepicker_date_format($created->format('Y-m-d')) . ' ' . $created->format('H:i');
                
                $formatComment = $createdAt . ' ' . $comment->getCreatedByName() . "\n\n" .
                        $comment->getComments();
                $allComments = $formatComment . "\n\n" . $allComments;
            }
            $lastComment = $comments->getLast();
            $commentExtract = $this->trimComment($lastComment->getComments());
        }
        
        $commentContainerHTML = content_tag('span', $commentExtract, array(
            'id' => $this->generateAttributeValue($placeholderGetters, 'commentContainer-{id}'),
        ));
        
        $hiddenFieldHTML = tag('input', array(
            'type' => 'hidden',
            'id' => $id,
            'name' => $name,
            'value' => $allComments,
        ));        

        $commentHTML = content_tag('span', $commentContainerHTML . $imageHTML . $hiddenFieldHTML, array(
            'class' => 'commentContainerLong',
        ));

        if ($this->isHiddenOnCallback()) {
            return '&nbsp;';
        }
        
        return $commentHTML . $this->getHiddenFieldHTML();
    }
    
    protected function trimComment($comment) {
        if (strlen($comment) > 30) {
            $comment = substr($comment, 0, 30) . '...';
        }
        return $comment;
    }
}


