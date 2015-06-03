<?php

namespace BitolaCo\OMTimes;

use SimpleXMLElement;

class Feed
{

    var $feed;
    var $items;
    var $cache;

    public function __construct($feedUrl) {
        $this->cache = array();
        $this->feed = $feedUrl;
        $this->parse();
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

    private static function _getValueFromElement(SimpleXMLElement $obj) {

        if(empty($_SESSION['feeds'])) {
            $_SESSION['feeds'] = array();
        }

        $item = (array) $obj;
        return count($item) ? array_shift($item) : '';

    }

    public function loadRssResult() {

        if(! empty($_SESSION['feeds'][$this->feed])) {
            return $_SESSION['feeds'][$this->feed];
        }

        $rss = simplexml_load_file($this->feed);
        $this->cacheRssResult($rss);

        return $rss;

    }

    public function cacheRssResult($rss) {
        $_SESSION['feeds'][$this->feed] = $rss;
    }

    public function parse() {

        $rss = $this->loadRssResult();
        foreach($rss->channel->item as $item) {
            $itunes = $item->children('http://www.itunes.com/dtds/podcast-1.0.dtd');
            $this->items[] = new FeedItem(
                self::_getValueFromElement($item->link),
                self::_getValueFromElement($item->title),
                self::_getValueFromElement($itunes->summary),
                self::_getValueFromElement($item->pubDate),
                self::_getValueFromElement($item->category),
                self::_getValueFromElement($itunes->image),
                self::_getValueFromElement($item->enclosure['url']),
                self::_getValueFromElement($item->enclosure['type']),
                self::_getValueFromElement($itunes->duration)
            );
        }

        return $this->items;

    }

    public function FindByCategory($category) {

        if(empty($this->items)) {
            return [];
        }

        $category = strtolower($category);
        $matches = [];
        foreach ($this->items as $item) {
            if(strtolower($item->category) === $category) {
                $matches[] = $item;
            }
        }

        usort($matches, function($a, $b){
            return $a->pubDate-$b->pubDate;
        });

        return $matches;

    }

    public function getAllShows()
    {

        $keys = [];
        foreach ($this->items as $item) {
            $keys[$item->category] = true;
        }
        return array_keys($keys);

    }

}