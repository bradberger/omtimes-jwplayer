<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
 * Plugin Name: OMTimes JWPlayer Plugin
 * Author: Brad Berger
 * Author URI: https://bradb.net
 * License: Proprietary
 * Description: OMTimes JWPlayer plugin, for displaying current shows via a shortcode.
 */

if(! defined('__DIR__')) {
    define('__DIR__', dirname('__FILE__'));
}

require_once __DIR__ . '/vendor/autoload.php';

use BitolaCo\OMTimes\Show;
use BitolaCo\OMTimes\Schedule;

Twig_Autoloader::register();

function jwplayer_get_env() {
    return php_sapi_name() == "cli" || substr_count($_SERVER['SERVER_NAME'], 'localhost') || substr_count($_SERVER['SERVER_NAME'], 'bitola') ? 'dev' : 'dist';
}

function jwplayer_get_twig_instance() {

    return new Twig_Environment(
        new Twig_Loader_Filesystem(__DIR__ . '/templates'),
        array(
            'cache' => jwplayer_get_env() === 'dist' ? __DIR__ . '/tmp/cache' : false,
        )
    );

}

function load_jwplayer_scripts() {
    wp_register_script('bootstrap', '//cdn.jsdelivr.net/bootstrap/3.3.4/js/bootstrap.min.js', ['jquery'], '3.3.4', false);
    wp_register_script('jwplayer', '//cdn.jsdelivr.net/jwplayer/6.7/jwplayer.js', [], '6.7', false);
    wp_register_script('jwplayer-html5', '//cdn.jsdelivr.net/jwplayer/6.7/jwplayer.html5.js', ['jwplayer'], '6.7', false);
    wp_register_script('jwplayer-custom', plugins_url('/js/app.js', __FILE__), ['jquery', 'bootstrap', 'jwplayer', 'jwplayer-html5'], '0.1.1', false);
    wp_enqueue_script('jwplayer-custom');
}

function load_jwplayer_stylesheets() {
    wp_register_style('bootstrap', '//cdn.jsdelivr.net/fontawesome/4.3.0/css/font-awesome.min.css');
    wp_register_style('font-awesome', '//cdn.jsdelivr.net/bootstrap/3.3.4/css/bootstrap.min.css');
    wp_register_style('jwplayer-styles', plugin_dir_url(__FILE__) .  '/css/style.css', ['bootstrap', 'font-awesome'], '0.1');
    wp_enqueue_style('jwplayer-styles');
}

function jwplayer_get_show_name() {

    $category = get_the_category();
    if(empty($category[0]) || empty($category[0]->name)) {
        $show = get_the_title();
    } else {
        $show = $category[0]->name;
    }

    return $show;

}

function jwplayer_shortcode($a) {

    $twig = jwplayer_get_twig_instance();
    $attrs = shortcode_atts(array(
        'show' => jwplayer_get_show_name(),
        'video' => false,
        'cause' => false,
        'mode' => 'single',
    ), $a);

    if ($attrs['mode'] === 'single') {

        // Get a list of shows.
        $shows = [];
        $list = explode(',', $attrs['show']);
        foreach($list as &$name) {
            if(! empty($name)) {
                $shows[] = new Show(trim($name), $attrs);
            }
        }

        if(empty($shows)) {
            return '<!-- No shows found. JWPlayer not initialized -->';
        }

        $template = $twig->loadTemplate('single-player.twig');
        return $template->render([
            'shows' => $shows
        ]);

    }

    // Main player
    $schedule = new Schedule();
    $template = $twig->loadTemplate('main-player.twig');
    return $template->render([
        'channels' => $schedule->channels,
        'next' => $schedule->next,
        'current' => $schedule->current
    ]);


}

add_action('wp_enqueue_scripts', 'load_jwplayer_scripts');
add_action('wp_enqueue_scripts', 'load_jwplayer_stylesheets');

add_shortcode('jwplayer', 'jwplayer_shortcode');