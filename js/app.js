jwplayer.key="14dXUGTMq4IQsfCFzyBSYPHprN3UtuIse9mDvEprD4c=";

(function($) {

    $.urlParam = function(name) {

        var results = new RegExp("[\?&]" + name + "=([^&#]*)").exec(window.location.href);
        return results === null ? null : results[1] || 0;

    };

    $(function() {

        var setupPlayer = function(file, image, title, autoplay) {

            // http://page.cloudradionetwork.com/omtimes/stream.php?port=8610
            var opts = {
                file: file,
                image: image,
                width: "100%",
                title: title | "Show",
                aspectratio: "15:8",
                autostart: autoplay || false,
                provider: "audio",
                modes: [
                    { type: "html5" },
                    { type: "flash", src: "//cdn.jsdelivr.net/jwplayer/6.7/jwplayer.flash.swf" }
                ]
            };

            // Streams from a PHP server confuse JWPlayer - it doesn't know the proper
            // mimetype, so we need to help it out a bit here.
            if (file.match(/\.php/)) {
                opts.type = "audio/mpeg";
            }

            jwplayer("mainPlayer").setup(opts);

        };

        var loadChannel = function(name, title, summary, cover, live, stream, podcast, autoplay) {

            setupPlayer(live ? stream : podcast, cover, title, autoplay || false);

            $(".channel-name").html(name);
            $(".channel-summary").html(summary);

            if(live) {
                $(".channel-title").html("On air now");
            } else {
                $(".channel-title").html(title.split(' - ')[1]);
            }

        };

        $(".channel-live-btn").click(function() {
            $(".channel-name").html("Listen Live");
            $(".channel-title").html("");
            setupPlayer("http://page.cloudradionetwork.com/omtimes/stream.php?port=9100", "/uploads/myPoster.jpg");
        });

        $(".channel-btn").click(function(e) {

            var ele = $(this);
            var name = ele.data("name");
            var title = ele.data("title");
            var cover = ele.data("cover");
            var live = ele.data("live");
            var podcast = ele.data("podcast");
            var stream = ele.data("stream");
            var summary = ele.data("summary");

            loadChannel(name, title, summary, cover, live, stream, podcast, true);
            e.preventDefault();

        });

        $(".mobile-player-link").click(function(e) {

            e.preventDefault();
            window.open("http://omtimes.com/mobile");

        });

        $(".show-promo-link").click(function(e) {

            e.preventDefault();

            var ele = $(this);
            var video = ele.data('video');
            var image = ele.data('image');
            var title = ele.data('title');

            setupPlayer(video, image, title, true);

        });

        $(".popup-player-link").click(function(e) {

            e.preventDefault();

            var w = screen.width < 800 ? screen.width * .95 : 800;
            var h = screen.height < 600 ? screen.height * .95 : 600;
            var left = (screen.width/2)-(w/2);
            var top = (screen.height/2)-(h/2);

            window.open(
                window.location.href,
                'jwplayer-standalone',
                [
                    'resizable=no', 'scrollbars=no', 'width=' + w, 'height=' + h,
                    'directories=no', 'status=no', 'menubar=no', 'scrollbars=no',
                    'resizable=no', 'copyhistory=no', 'top=' + top, 'left=' + left
                ].join(', ')
            );

        });

        var loadSingleShow = function() {
            var meta = $(".single-show-meta");
            if (meta.length) {

                var name = meta.data("name");
                var title = meta.data("title");
                var cover = meta.data("cover");
                var live = meta.data("live");
                var podcast = meta.data("podcast");
                var stream = meta.data("stream");

                setupPlayer(live ? stream : podcast, cover);

                if (live) {
                    $(".channel-live-btn").show();
                } else {
                    $(".channel-live-btn").hide();
                }

            }
        };

        // Handle single shows and the main page differently.
        if ($(".player-container").data("single-show")) {
            loadSingleShow();
        } else {
            // Load whatever is playing right now.
            setupPlayer("http://page.cloudradionetwork.com/omtimes/stream.php?port=9100", "/uploads/myPoster.jpg");
        }


    });

})(jQuery);