<?php

namespace BitolaCo\OMTimes;

use DateTime;
use DateInterval;

class Show
{

    var $name;
    var $title;
    var $feedUrl;
    var $feed;
    var $items;
    var $live = 0;
    var $podcast;
    var $audioUrl;
    var $cover;
    var $stream = 'http://page.cloudradionetwork.com/omtimes/stream.php?port=9100';
    var $featuredCause = '';
    var $promoVideo = '';
    var $attrs;

    public function __construct($name, Array $attrs = null, $feedUrl = '')
    {

        $this->name = $name;
        $this->feedUrl = $feedUrl ? : 'http://podcast.omtimes.com/feed/';
        $this->feed = new Feed($this->feedUrl);
        $this->items = $this->feed->FindByCategory($this->name);
        $this->attrs = $attrs ? : [
            'video' => false,
            'cause' => false,
            'cover' => false,
            'podcast' => false,
        ];
        $this->getCover();
        $this->isLive();
        $this->getLatestPodcast();
        $this->getPromoVideo();
        $this->getFeaturedCause();

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
     * @return integer An id for a post of this show, used to pull post meta from the DB.
     */
    public function getPostId() {
        $post = get_page_by_title($this->name, 'OBJECT', 'shows');
        return $post ? $post->ID : null;
    }


    public function getScheduleDayNumbers()
    {
        return get_post_meta($this->getPostId(), '_show_schedule_days', true);
    }

    public function getScheduleHour() {

        // Show runs today. Now compare time. Format used is 3:00pm, etc.
        $hour = strtolower(get_post_meta($this->getPostId(), '_show_schedule_time', true));
        $pm = substr_count($hour, 'pm');
        $hour = (int) array_shift(explode(':', $hour));
        if($pm) {
            if($hour !== 12) {
                $hour += 12;
            }
        }

        return $hour;

    }

    public function isPlayingOnDay($date = null) {

        if (! $date || is_a($date, 'DateTime')) {
            $date = new DateTime();
        }

        foreach (explode(',', $this->getScheduleDayNumbers()) as $day) {
            if ($date->format('w') == $day) {
                return true;
            }
        }

        return false;

    }

    public function isPlayingToday() {
        return $this->isPlayingOnDay(new DateTime());
    }

    /**
     * @return bool Whether the show is currently playing live.
     */
    public function isLive()
    {
        return $this->isPlayingOn(new DateTime());
    }

    public function isPlayingOn(DateTime $time) {

        $playing = false;

        if($this->isPlayingOnDay($time)) {

            $hour = $this->getScheduleHour();

            $showTime = new DateTime(sprintf(
                '%sT%s:0:00 GMT-5:00',
                $time->format('Y-m-d'),
                $hour
            ));

            // Compare now with the time of the show, and see if they're the same.
            if($time->format('G') == $showTime->format('G')) {
                $playing = true;
            }

        }

        return $playing;

    }

    public function isNext()
    {

        $nextHour = new DateTime();
        $nextHour->add(new DateInterval('P1H'));

        return $this->isPlayingOn($nextHour);

    }

    /**
     * @return null|Array The latest podcast info.
     */
    public function getLatestPodcast()
    {

        if (! empty($this->attrs['podcast'])) {
            return $this->podcast = $this->attrs['podcast'];
        }

        $this->podcast = empty($this->items) ? null : $this->items[0];
        if ($this->podcast) {
            $this->audioUrl = $this->podcast->audioUrl;
            $this->cover = $this->podcast->image ? : $this->cover;
            $this->title = $this->podcast->title ? : $this->name;
        }

        return $this->podcast;
    }

    public function getCover() {

        if(! empty($this->attrs['cover'])) {
            return $this->cover = $this->attrs['cover'];
        }

        $id = $this->getPostId();
        if ($id) {
            $image_url = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'full', true);
            return $this->cover = $image_url[0];
        }

        return $this->cover = 'http://omtimes.com/iom/wp-content/uploads/2015/01/omtimes-radio-260x70-without-bg.png';

    }

    public function getFeaturedCause() {

        if(! empty($this->attrs['cause'])) {
            return $this->featuredCause = $this->attrs['cause'];
        }

        return '';

    }

    public function getPromoVideo() {

        if(! empty($this->attrs['promo'])) {
            return $this->promoVideo = $this->attrs['promo'];
        }

        $video = null;
        switch($this->name) {
            case 'Immersion into Source':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/04/Immersion-Into-Source_OMTimes-Radio.mp4';
                break;
            case 'Between Heaven and Earth';
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/02/Between-Heaven-and-Earth_OMTimes-Radio.mp4';
                break;
            case 'The Elliot Jolesch Hour':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/02/Elliot-Jolesch-Hour_OMTimes-Radio.mp4';
                break;
            case 'The O Spot':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/05/O-Spot_OMTimes-Radio.mp4';
                break;
            case 'The Dr Kevin Show':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/05/Dr-Kevin-Show_OMTimes-Radio.mp4';
                break;
            case 'The Irreverent Therapists':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/02/Irreverent-Therapists_OMTimes-Radio.mp4';
                break;
            case 'Joy of Business':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/01/Joy-of-Business_OMTimes.mp4';
                break;
            case 'Circle of Hearts Radio':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/01/Circle-of-Hearts-Radio_OMTimes-Radio.mp4';
                break;
            case 'Radio Nahmaste':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/01/Radio-Nahmaste_OMTimes-Radio.mp4';
                break;
            case 'Entanglement Radio':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/01/Entanglement-Radio_OMTimes-Radio.mp4';
                break;
            case 'Sacred Business Success':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/01/Sacred-Business-Success_OMTimes-Radio.mp4';
                break;
            case 'Live with Lisa Phoenix':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/03/Live-with-Lisa-Phoenix_OMTimes-Radio.mp4';
                break;
            case 'Equilarium FM':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/04/Equilarium-FM_OMTimes-Radio.mp4';
                break;
            case 'Co-Creating NOW':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/01/Co-Creating-Now_OMTimes-Radio.mp4';
                break;
            case 'I AM Wisdom':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/01/I-AM-Wisdom_OMTimes-Radio.mp4';
                break;
            case 'Intuitive Transformations':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/01/Intuitive-Transformations_OMTimes-Radio.mp4';
                break;
            case 'New Consciousness Review':
                $video = 'http://omtimes.com/iom/wp-content/uploads/2015/04/New-Consciousness-Review_OMTimes-Radio.mp4';
                break;
        }

        return $this->promoVideo = $video;

    }




}