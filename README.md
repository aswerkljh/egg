# Extremely Generic (PHP) Gallery

This PHP gallery will populate subfolders with a symlinked index.php, hence the 'Many Files'.

It is appropriate to use it as a single-folder gallery, too.

DEMO [https://p.drkt.eu](https://p.drkt.eu)

## Features
- No database
- Very simple install
- Optional viewcount tracker (req database)
- Clean and readable URLs from folder structure
- Automatic thumbnail generation on page load
- Infinite vertical screen resolution support

## TESTED ON
- Debian 11
- Apache2
- PHP7.4

## Dependencies
```
apt install php7.4 php7.4-gd

# Only for video thumbnail generation
apt install ffmpeg

# Only for tracking viewcounts
apt install sqlite3 php7.4-sqlite3
```

## Installation
Please CAREFULLY read the comments in `create_symlinks_in_subdirs.sh` and understand what this script will do to your folder structure, as it can be destructive if you have spaces in your folder names.

The script will, unless you comment out those sections, RENAME folders with spaces and hyphens to have underscores instead. For example, `test folder` -> `test_folder`. This is simply because I don't want to deal with spaces in URLs and directory paths. You have been warned!

1. To install, simply put all the files into the root directory of your gallery and then run `./create_symlinks_in_subdirs.sh`
2. Optionally make a `.rootdir` file in the root directory to enable special functions only present on the root directory page `touch .rootdir`.
3. The root directory page has hardlinked images. If you want it to simply display whatever images are also in the root directory, simply comment out lines 116 through 173. This WILL cause duplicates in the viewcounter if you have duplicates in any subfolder.

## Viewcounter Setup
The viewcounter works by intercepting all image requests and redirecting them to viewcounter.php

Viewcounter.php increments the database and serves the image to the user seamlessly.

1. Install sqlite3 (see dependencies).
2. Make database `sqlite3 viewcounts.sqlite` This must be in the root directory of your gallery.
3. Open db `sqlite3 viewcounts.sqlite`.
4. In sqlite3, do:
   ```
   CREATE TABLE view_counts (
       image_url TEXT PRIMARY KEY,
       count INTEGER
   );
   ```
5. Exit out of sqlite3 `.exit`.
6. Enable Apache2 mod rewrite `sudo a2enmod rewrite`.
7. Restart Apache2 `sudo systemctl restart apache2`.
8. Create an `.htaccess` file with content:
   ```
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteBase /
       # Redirect all requests to viewcounter.php for specified file extensions
       RewriteRule \.(jpg|png|jxl|mp4|gif)$ viewcounter.php [L]
   </IfModule>
   ```

## Notes on Video Thumbnails
If you don't want video thumbnails, simply don't put any mp4 files in any directory where index.php is present and it won't ever run the relevant code.
It will however throw an error and refuse to load the page if you do and don't have FFMPEG installed.
You can comment out the `function generateVideoMp4Thumbnail` block in `index.php` if you want to be absolutely sure no video thumbnail generation is ever attempted.
