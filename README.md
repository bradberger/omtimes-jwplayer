## Usage

### Without name:

```
[jwplayer]
```

### With name:
```
[jwplayer show='Name of show']
```

### Main player
```
[jwplayer mode='main']
```

## Showschedule Plugin Error

OMTimes Notes:

Error in showschedule template `wp-content/plugins/showschedule/templates/single-shows.php`.
Need to replace `get_the_content()` with `apply_filters( 'the_content', get_the_content())`.

```html
<div class="desc">
    <?php echo apply_filters( 'the_content', get_the_content()); ?>
</div>
```

## Development Notes

```sql
UPDATE wp_postmeta SET meta_value = '' WHERE meta_key = '_yoast_wpseo_redirect';
```

## Blank Page

The blank page template `templates/page-blank.php` needs to be installed in the current theme/child theme.

## Know Issues

- There are, per the specs, multiple channel, but these don't seem to be correct.
The music channel isn't music, but is the same as the regular channel. 

- Shows that are pulled via the showschedule plugin (required for "Next Up") have channels
but those channels don't seem to correspond to the streaming channels at the time being.

## Updated Files

- `wp-content/plugins/showschedule/admin/admin-functions.php`
- `wp-content/plugins/showschedule/admin/admin-functions.php`
- `wp-content/plugins/showschedule/templates/single-shows.php`