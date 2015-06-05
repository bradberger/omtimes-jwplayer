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

    public function __construct($name, $feedUrl = '')
    {

        $this->name = $name;
        $this->feedUrl = $feedUrl ? : 'http://podcast.omtimes.com/feed/';
        $this->feed = new Feed($this->feedUrl);
        $this->items = $this->feed->FindByCategory($this->name);
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
        $this->podcast = empty($this->items) ? null : $this->items[0];

        if ($this->podcast) {
            $this->audioUrl = $this->podcast->audioUrl;
            $this->cover = $this->podcast->image ? : $this->cover;
            $this->title = $this->podcast->title ? : $this->name;
        }

        return $this->podcast;
    }

    public function getCover() {

        $img = null;
        switch($this->name) {
            case 'Joy of Business':
                $img = 'http://radio.omtimes.com/wp-content/uploads/2014/12/Simone-Milasas-SP.png';
                break;
            case 'Eros Evolution':
                $img = 'http://radio.omtimes.com/wp-content/uploads/2014/11/Martha-Lee-sp-rev-A.png';
                break;
            case 'Circle of Hearts Radio':
                $img = 'http://radio.omtimes.com/wp-content/uploads/2015/01/Linda-Frisch-SHOW-PAGE-750x400.png';
                break;
            case 'Radio Nahmaste':
                $img = 'http://radio.omtimes.com/wp-content/uploads/2015/01/Sarah-Nash-sp.png';
                break;
            case 'Entanglement Radio':
                $img = 'http://radio.omtimes.com/wp-content/uploads/2015/01/angela-levesque-SP-1-750x400.jpg';
                break;
            case 'Laying on of Hands Healing':
                $img = '';
                break;
            case 'Sacred Business Success':
                $img = 'http://radio.omtimes.com/wp-content/uploads/2014/12/OT-SHOW-PAGE-Michelle-Barr-750x400.png';
                break;
            case 'Conscious Parenting':
                $img = 'http://radio.omtimes.com/wp-content/uploads/2015/01/Timothy-Stuetz-SP.png';
                break;
            case 'Co-Creating NOW':
                $img = 'http://radio.omtimes.com/wp-content/uploads/2015/01/Monika-Goyal-sp.png';
                break;
            case 'I AM Wisdom':
                $img = 'http://radio.omtimes.com/wp-content/uploads/2014/11/Katrina-Cavanough-SHOW-page-750x400.png';
                break;
            case 'Intuitive Transformations':
                $img = 'http://radio.omtimes.com/wp-content/uploads/2014/12/OT-SHOW-PAGE-Sylvia-Henderson.png';
                break;
            default:
                $id = $this->getPostId();
                if ($id) {
                    $image_url = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'full', true);
                    $img = $image_url[0];
                }
                break;
        }

        return $this->cover = $img;

    }

    public function getFeaturedCause() {

        $cause = null;

        switch($this->name) {
            case 'Joy of Business':
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2014/12/Simone-Milasas-SP.png';
                break;
            case 'Eros Evolution':
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2014/11/Martha-Lee-sp-rev-A.png';
                break;
            case 'Circle of Hearts Radio':
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2015/01/Linda-Frisch-SHOW-PAGE-750x400.png';
                break;
            case 'Radio Nahmaste':
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2015/01/Sarah-Nash-sp.png';
                break;
            case 'Entanglement Radio':
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2015/01/angela-levesque-SP-1-750x400.jpg';
                break;
            case 'Laying on of Hands Healing':
                $cause = '';
                break;
            case 'Sacred Business Success':
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2014/12/OT-SHOW-PAGE-Michelle-Barr-750x400.png';
                break;
            case 'Conscious Parenting':
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2015/01/Timothy-Stuetz-SP.png';
                break;
            case 'Co-Creating NOW':
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2015/01/Monika-Goyal-sp.png';
                break;
            case 'I AM Wisdom':
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2014/11/Katrina-Cavanough-SHOW-page-750x400.png';
                break;
            case 'Intuitive Transformations':
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2014/12/OT-SHOW-PAGE-Sylvia-Henderson.png';
                break;
            default:
                $cause = 'http://radio.omtimes.com/wp-content/uploads/2015/01/iom-fm.png';
                break;
        }

        return $this->featuredCause = $cause;

    }

    public function getPromoVideo() {

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