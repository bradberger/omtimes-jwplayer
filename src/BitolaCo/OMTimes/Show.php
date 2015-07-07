<?php

namespace BitolaCo\OMTimes;

use DateTime;
use DateInterval;
use DateTimeZone;

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
    var $channel;
    var $stream;
    var $featuredCause = '';
    var $promoVideo = '';
    var $attrs;
    var $host;

    public function __construct($name, Array $attrs = null, $feedUrl = '')
    {

        $this->name = $name;
        $this->feedUrl = $feedUrl ?: 'http://podcast.omtimes.com/feed/';
        $this->feed = new Feed($this->feedUrl);
        $this->items = $this->feed->FindByCategory($this->name);
        $this->attrs = $attrs ?: array(
            'video' => false,
            'cause' => false,
            'cover' => false,
            'podcast' => false,
            'channel' => false,
            'stream' => 'http://page.cloudradionetwork.com/omtimes/stream.php?port=8610',
        );

        $this->getHost();
        $this->getStream();
        $this->getCover();
        $this->isLive();
        $this->getLatestPodcast();
        $this->getPromoVideo();
        $this->getFeaturedCause();

    }

    public function getHost() {

        if($this->host) {
            return $this->host;
        }

        $this->host = array();

        if ($assigned_hosts = get_post_meta($this->getPostId(), '_show_schedule_host', true)) {
            $hosts = explode(',', $assigned_hosts);
            foreach ($hosts as $v) {
                $this->host[] =  get_post_field('post_title', $v);
            }
        }

        return $this->host;

    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return '';
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    /**
     * @return integer An id for a post of this show, used to pull post meta from the DB.
     */
    public function getPostId()
    {
        $post = get_page_by_title($this->name, 'OBJECT', 'shows');
        return $post ? $post->ID : null;
    }

    public function getStream()
    {
        return $this->stream = $this->attrs['stream'] ?
            : 'http://page.cloudradionetwork.com/omtimes/stream.php?port=9100';
    }

    public function getChannel()
    {

        if (!empty($attrs['channel'])) {
            return $this->channel = $attrs['channel'];
        }

        if (class_exists('Schedule_Post_types')) {
            return $this->channel = get_the_term_list(
                $this->getPostId(), Schedule_Post_types::$channels, '', ', ', ''
            );
        }

        return '';

    }


    public function getSchedule()
    {

        $schedule = array();
        for ($i = 0; $i < 7; $i++) {
            $schedule[(string)$i] = array();
        }

        $days = explode(",", get_post_meta($this->getPostId(), '_show_schedule_days', true));
        $hour = $this->getScheduleHour();
        foreach ($days as $i => $day) {

            // This is because our the genius behind the other
            // plugin decided Sunday was day 7...
            $day = (int)$day;
            if ((int)$day === 7) {
                $day = 0;
            } else {
                $day -= 1;
            }

            $schedule[(string)$day][(string)$hour] = true;
        }

        $repeat = get_post_meta($this->getPostId(), 'repeat', true);
        if(! empty($repeat)) {
            foreach ($repeat as $listing) {
                $schedule[$listing['day']][self::getHourFromString($listing['time'])] = true;
            }
        }

        return $schedule;

    }

    public static function getHourFromString($hour)
    {

        $pm = substr_count($hour, 'pm');
        $hour = explode(':', $hour);
        $hour = (int) array_shift($hour);
        if ($pm) {
            if ($hour !== 12) {
                $hour += 12;
            }
        } else {
            if ($hour === 12) {
                $hour = 0;
            }
        }

        return $hour;

    }

    public function getScheduleHour()
    {

        // Show runs today. Now compare time. Format used is 3:00pm, etc.
        $hour = strtolower(get_post_meta($this->getPostId(), '_show_schedule_time', true));
        return self::getHourFromString($hour);

    }

    public function isPlayingOnDay($date = null)
    {

        if (!$date || is_a($date, 'DateTime')) {
            $date = new DateTime();
        }

        $day = (string) $date->format('w');
        $schedule = $this->getSchedule();
        return !empty($schedule[$day]);

    }

    public function isPlayingToday()
    {
        return $this->isPlayingOnDay(new DateTime());
    }

    /**
     * @return bool Whether the show is currently playing live.
     */
    public function isLive()
    {
        return $this->live = $this->isPlayingOn(new DateTime());
    }

    public function isPlayingOn(DateTime $time)
    {

        // The show schedule is in Eastern time, so note that here.
        $time->setTimezone(new DateTimeZone('America/New_York'));

        // Uncomment below to arbitrarily trigger a response.
        // $time = new DateTime();
        // $time->setDate(2015, 6, 7);
        // $time->setTime(14, 0, 0);

        $schedule = $this->getSchedule();
        $day = $time->format('w');
        $hour = (string)$time->format('G');
        $playing = !empty($schedule[$day][$hour]);

        return $playing;

    }

    public function isNext()
    {

        $nextHour = new DateTime("+1 hours");

        return $this->isPlayingOn($nextHour);

    }

    /**
     * @return null|Array The latest podcast info.
     */
    public function getLatestPodcast()
    {

        if (!empty($this->attrs['podcast'])) {
            return $this->podcast = $this->attrs['podcast'];
        }

        $this->podcast = empty($this->items) ? null : $this->items[0];
        foreach($this->items as $item) {
            if($item->audioUrl && ! $this->audioUrl) {
                $this->audioUrl = $item->audioUrl;
            }
            if($item->cover && ! $this->cover) {
                $this->cover = $item->image;
            }
            if($item->title && ! $this->title) {
                $this->title = $item->title;
            }
        }

        if(empty($this->title)) {
            $this->title = $this->name;
        }

        return $this->podcast;
    }

    public function getCover()
    {

        if (!empty($this->attrs['cover'])) {
            return $this->cover = $this->attrs['cover'];
        }

        $id = $this->getPostId();
        if ($id) {
            $image_url = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'full', true);
            return $this->cover = $image_url[0];
        }

        return $this->cover = 'http://omtimes.com/iom/wp-content/uploads/2015/01/omtimes-radio-260x70-without-bg.png';

    }

    public function getFeaturedCause()
    {

        if (!empty($this->attrs['cause'])) {
            return $this->featuredCause = $this->attrs['cause'];
        }

        return '';

    }

    public function getPromoVideo()
    {

        if (!empty($this->attrs['promo'])) {
            return $this->promoVideo = $this->attrs['promo'];
        }

        switch ($this->name) {
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
            default:
                $video = null;
                break;
        }

        return $this->promoVideo = $video;

    }

}