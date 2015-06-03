<?php

namespace BitolaCo\OMTimes;

class Schedule
{

    var $feed;
    var $shows;

    public function __construct()
    {
        $this->feed = new Feed('http://podcast.omtimes.com/feed/');
        $this->shows = $this->feed->getAllShows();
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return '';
    }

    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    /**
     * @return Show|bool
     */
    public function playingNow()
    {

        foreach($this->shows as $name) {
            $show = new Show($name);
            if($show->isLive()) {
                return $show;
            }
        }

        return null;

    }

    public function playingNext()
    {

        foreach($this->shows as $name) {
            $show = new Show($name);
            if($show->isNext()) {
                return $show;
            }
        }

        return null;

    }
}