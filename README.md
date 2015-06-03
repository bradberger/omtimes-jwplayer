

## Questions

Buttons outside the player, or just inside?

## Schedule

Handled by the `showschedule`.
How to use it?

## Build Plugin

1. Add anywhere with short-code.
2. Pull schedule from the `showschedule` plugin.
3. Parse the rss feed at [http://podcast.omtimes.com/feed/](http://podcast.omtimes.com/feed/) to find the latest podcast episode.

## Latest Podcast

Each show as a rss feed, so should be able to parse it.
It appears that show name is the `Item.Category` value.

```
Channel > Item > Enclosure.url (Enclosure.type)
```
