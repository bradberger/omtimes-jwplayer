## Usage

### Without name:

```
[jwplayer]
```

### With name:
```
[jwplayer show='Name of show']
```

### With Multiple Shows:

```
[jwplayer show='Name of show #1, Name of show #2, etc.']
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