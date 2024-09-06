# wp-cron-change-url

Overwrite `wp-cron` URL with custom URL on `cron_request`.  
Use this to fix mismatched host names and ports when using Docker etc.

## Usage

Setup on WP Admin > Tools > Change Cron URL

```
Example:
  home_url: http://localhost:8080
  custom_url: http://host.docker.internal:8080
```
