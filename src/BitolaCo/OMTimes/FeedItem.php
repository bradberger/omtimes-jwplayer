<?php

namespace BitolaCo\OMTimes;

class FeedItem
{
    var $link;
    var $title;
    var $subtitle;
    var $pubDate;
    var $category;
    var $image;
    var $audioUrl;
    var $audioType;
    var $duration;
    var $summary;
    var $showDate;

    public function __construct($link = '', $title = '', $summary = '', $pubDate = null, $category = '', $image = '', $audioUrl = '', $audioType = '', $duration = null) {

        $title = explode('-', $title);

        $this->link = $link;
        $this->show = trim(array_shift($title));
        $this->title = empty($title[1]) ? $this->show : trim($title[1]);
        $this->summary = $summary;
        $this->pubDate = $pubDate ? strtotime($pubDate) : null;
        $this->category = $category;
        $this->image = $image;
        $this->audioUrl = $audioUrl;
        $this->audioType = $audioType;
        $this->duration = $duration;

        $dateArr = explode(' - ', $this->title);
        $this->showDate = strtotime(end($dateArr));

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

}