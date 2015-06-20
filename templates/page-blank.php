<?php
/**
Template Name: Blank page for JWPlayer pop-up
 */
?>
<html>
    <head>
        <!-- Meta Tags -->
        <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <!-- Title -->
        <title><?php wp_title(''); ?></title>
        <link rel="profile" href="http://gmpg.org/xfn/11" />

        <!-- RSS & Pingbacks -->
        <link rel="alternate" type="application/rss+xml" title="<?php bloginfo( 'name' ); ?> RSS Feed" href="<?php  bloginfo( 'rss2_url' ); ?>" />
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
        <?php wp_head(); ?>


        <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/js/html5shiv.js" type="text/javascript"></script>
        <![endif]-->
        <style>body, .page-header { margin-top: 0 !important; }</style>
    </head>

    <body>
        <?php while (have_posts()) : the_post(); ?>
        <div id="page-content">
            <?php echo apply_filters('the_content', get_the_content());
            endwhile; ?>
        </div>
    </body>
</html>