<?php

namespace BitolaCo\OMTimes;

class Schedule
{

    var $feed;
    var $shows;
    var $current;
    var $next;
    var $channels;
    var $list;

    var $defaultCover = 'http://omtimes.com/iom/wp-content/uploads/2015/01/omtimes-radio-260x70-without-bg@2x.png';
    var $defaultPromo = '';

    public function __construct(Array $channels = null)
    {

        $this->feed = new Feed('http://podcast.omtimes.com/feed/');
        $this->list = $this->getAllShows();
        $this->shows = $this->feed->getAllShows();
        $this->channels = $channels ? : array(
            array('name' => 'Talk Radio 1', 'host' => 'www.ophanim.net', 'port' => '8610', 'type' => 'audio/mpeg'),
            array('name' => 'Music Channel', 'host' => 'www.ophanim.net', 'port' => '9100', 'type' => 'audio/mpeg')
        );

        foreach ($this->channels as &$channel) {

            $channel['url'] = sprintf('http://page.cloudradionetwork.com/omtimes/stream.php?port=%s', $channel['port']);
            $channel['shoutcast'] = new Shoutcast($channel['host'], $channel['port']);
            $channel['stats'] = $channel['shoutcast']->getSevenHTML();

            // Error getting the ShoutCast info, so we need to just use the showschedule plugin instead.
            $show = null;
            foreach($this->list as &$s) {

                // First check if have the ShoutCAST info.
                if(! empty($channel['stats'])) {
                    if ($s->name === $channel['stats']->songTitle
                        || in_array($channel['stats']->songTitle, $s->host)) {
                        $show = $s;
                        $channel['stats']->songTitle = $s->name;
                    }
                }

                // If ShoutCAST didn't find anything, use the
                // showschedule plugin as backup.
                if(! $show) {
                    if($s->isLive()) {
                        $show = $s;
                    }
                }

            }

            // Set the promo videos and cover, falling back to defaults.
            $channel['promo'] = $show && $show->promoVideo ?
                $show->promoVideo : $this->defaultPromo;

            $channel['cover'] = $show && $show->cover ?
                $show->cover : $this->defaultCover;


        }

        $this->playingNow();
        $this->playingNext();

    }

    public function getAllShows()
    {

        $this->list = array();
        $shows = get_posts(array('post_type' => 'shows', 'posts_per_page' => 1000));
        foreach($shows as $show) {
            $this->list[] = new Show($show->post_title);
        }

        return $this->list;

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

    public function findChannelByName($name) {

        $name = strtolower($name);
        foreach($this->channels as &$channel) {
            if(strtolower($channel['name']) === $name) {
                return $channel;
            }
        }

        return null;

    }

    public function getCurrentStats($channel = null) {

        $channel = $channel ? $this->findChannelByName($channel) : $this->channels ? $this->channels[0] : null;
        if (! $channel) {
            return null;
        }

        return $channel['shoutcast']->getSevenHTML();

    }

    public function getCurrentListeners($channel = null) {

        $stats = $this->getCurrentStats($channel);
        return $stats ? $stats->currentListeners : null;

    }

    public function getStreamStatus($channel = null) {
        $stats = $this->getCurrentStats($channel);
        return $stats ? $stats->streamStatus : null;
    }

    public function getPeakListeners($channel = null) {
        $stats = $this->getCurrentStats($channel);
        return $stats ? $stats->peakListeners : null;
    }

    public function getMaxListeners($channel = null) {
        $stats = $this->getCurrentStats($channel);
        return $stats ? $stats->maxListeners : null;
    }

    public function getUniqueListeners($channel = null) {
        $stats = $this->getCurrentStats($channel);
        return $stats ? $stats->uniqueListeners : null;
    }

    public function getBitrate($channel = null) {
        $stats = $this->getCurrentStats($channel);
        return $stats ? $stats->bitrate : null;
    }

    public function getSongTitle($channel = null) {
        $stats = $this->getCurrentStats($channel);
        return $stats ? $stats->songTitle : null;
    }

    /**
     * @return Show|bool
     */
    public function playingNow()
    {

        $this->current = null;
        foreach($this->list as &$show) {
            $live = $show->isLive();
            if($live) {
                $this->current = $show;
            }
        }

        return $this->current;

    }

    public function playingNext()
    {

        $this->next = null;
        foreach($this->list as &$show) {
            if($show->isNext()) {
                $this->next = $show;
            }
        }

        return $this->next;

    }

    public function getChannelsJson()
    {
        return json_encode($this->channels);
    }

}