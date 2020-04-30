<?php

namespace Wappointment\Messages;

trait HasTagsToReplace
{

    public function replaceTags()
    {
        foreach ($this->replacing as $property) {
            if (!empty($this->$property)) {
                $this->$property = (new TagsReplacement($this->client, $this->appointment))->replace($this->$property);
            }
        }
    }
}
