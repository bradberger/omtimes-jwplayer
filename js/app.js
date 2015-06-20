jwplayer.key="14dXUGTMq4IQsfCFzyBSYPHprN3UtuIse9mDvEprD4c=";

(function($) {

    $.urlParam = function(name) {

        var results = new RegExp("[\?&]" + name + "=([^&#]*)").exec(window.location.href);
        return results === null ? null : results[1] || 0;

    };

    $(function() {

        // Prevent errors if the ga() function doesn't exist (think AdBlock, etc.)
        var sendEvent = function(send, event, category, action, label, value) {

            label = label || null;
            value = value || null;

            if ("undefined" === typeof ga) {
                var _gaq = _gaq || [];
                _gaq.push(["_trackEvent", category, action, label, value]);
            } else {
                ga(send, event, category, action, label, value);
            }

        };

        var setupPlayer = function(file, image, title, autoplay, type) {

            // Just in case, at least broadcast something live.
            file = file || "http://page.cloudradionetwork.com/omtimes/stream.php?port=8610";

            var opts = {
                file: file,
                image: image,
                width: "100%",
                title: title | "Show",
                aspectratio: "15:8",
                autostart: autoplay || false,
                provider: "audio",
                primary: 'html5',
                modes: [
                    { type: "html5" },
                    { type: "flash", src: "//cdn.jsdelivr.net/jwplayer/6.7/jwplayer.flash.swf" }
                ]
            };

            // Streams from a PHP server confuse JWPlayer - it doesn't know the proper
            // mimetype, so we need to help it out a bit here.
            if(type) {
                opts.type = type;
            } else if (file.match(/\.php/) || file.match(/:[0-9]+$/)) {
                opts.type = "audio/mpeg";
            }

            jwplayer("mainPlayer").setup(opts);

        };

        var loadChannel = function(name, title, summary, cover, live, stream, podcast, autoplay, type) {

            var url = live ? stream : podcast;
            setupPlayer(url, cover, title, autoplay || false, type);

            $(".channel-name").html(name);
            $(".channel-title").html(title);
            $(".channel-summary").html(summary);

        };

        $(".channel-live-btn").click(function(e) {

            e.preventDefault();

            var ele = $(this);
            var name = ele.data("name");
            var title = ele.data("title");
            var cover = ele.data("cover");
            var podcast = ele.data("podcast");
            var stream = ele.data("stream");
            var summary = ele.data("summary");

            loadChannel(name, title, summary, cover, true, stream, podcast, true);

            sendEvent("send", "event", name, "play", "live", stream);

        });

        $(".channel-btn").click(function(e) {

            e.preventDefault();

            var ele = $(this);
            var name = ele.data("name");
            var title = ele.data("title");
            var cover = ele.data("cover");
            var live = ele.data("live");
            var podcast = ele.data("podcast");
            var stream = ele.data("stream");
            var summary = ele.data("summary");
            var type = ele.data("type") || false;

            $(".channel-btn").removeClass("active");
            ele.addClass("active");

            loadChannel(name, title, summary, cover, live, stream, podcast, true, type);

            sendEvent("send", "event", name, "play", "podcast", podcast);

        });

        $(".mobile-player-link").click(function(e) {

            e.preventDefault();
            window.open("http://omtimes.com/mobile");

            sendEvent("send", "event", name, "play", "mobile", "http://omtimes.com/mobile");

        });

        var playPromo = function(e) {

            e.preventDefault();

            var ele = $(this);
            var video = ele.data('video');
            var image = ele.data('image');
            var title = ele.data('title');

            setupPlayer(video, image, title, true);

            sendEvent("send", "event", name, "play", "promo", video);

        };

        $(".show-promo-link").click(playPromo);
        $(".up-next-link").click(playPromo);

        $(".featured-cause-link").click(function(e) {

            e.preventDefault();

            var ele = $(this);
            var cause = ele.data('cause');
            var image = ele.data('image');
            var title = ele.data('title');

            setupPlayer(cause, image, title, true);

            sendEvent("send", "event", name, "play", "cause", cause);

        });

        $(".popup-player-link").click(function(e) {

            e.preventDefault();

            var w = screen.width < 800 ? screen.width * .95 : 800;
            var h = screen.height < 600 ? screen.height * .95 : 600;
            var left = (screen.width/2)-(w/2);
            var top = (screen.height/2)-(h/2);

            window.open(
                '/player/',
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

                sendEvent("send", "event", name, "load", live ? "live" : "podcast", live ? stream : podcast);

            }
        };

        // Handle single shows and the main page differently.
        var show = $(".player-container").data("single-show");
        if (show) {
            loadSingleShow();
        } else {
            // Load whatever is playing right now.
            var btns = $(".channel-btn");
            if (btns.length) {

                var ele = $(btns[0]);
                var name = ele.data("name");
                var title = ele.data("title");
                var cover = ele.data("cover");
                var podcast = ele.data("podcast");
                var stream = ele.data("stream");
                var summary = ele.data("summary");
                var type = ele.data("type") || false;

                loadChannel(name, title, summary, cover, true, stream, podcast, false, type);

            }

        }

        // Reload on the next hour to get fresh schedules.
        setTimeout(function() {
            window.location.reload();
        }, 3600000 - ((new Date) % 3600000));

    });

})(jQuery);