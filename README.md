# Pantheon Image Enrichment #
**Contributors:** getpantheon, danielbachhuber  
**Tags:** pantheon, images  
**Requires at least:** 4.7  
**Tested up to:** 4.9  
**Stable tag:** 0.0.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Generate default alt text, block unsafe uploads, and enrich images using the Google Cloud Vision API.

## Description ##

[![Travis CI](https://travis-ci.org/danielbachhuber/pantheon-image-enrichment.svg?branch=master)](https://travis-ci.org/danielbachhuber/pantheon-image-enrichment)

Supercharge your WordPress Media Library with a variety of enhancements via the Google Cloud Vision API:

* Generate default alt text
* Block unsafe uploads

Go forth and make awesome! And, once you've built something great, [send us feature requests (or bug reports)](https://github.com/danielbachhuber/pantheon-image-enrichment/issues).

## Installation ##

To install Pantheon Image Enrichment, follow these steps:

1. Install the plugin from WordPress.org using the WordPress dashboard.
2. Activate the plugin.

To install Pantheon Image Enrichment in one line with WP-CLI:

    wp plugin install pantheon-image-enrichment --activate

## WP-CLI Commands ##

This plugin implements a variety of [WP-CLI](https://wp-cli.org) commands. All commands are grouped into the `wp pie` namespace.

    NAME
    
      wp pie
    
    DESCRIPTION
    
      Manage Pantheon Image Enrichment.
    
    SYNOPSIS
    
      wp pie <command>
    
    SUBCOMMANDS
    
      generate-alt-text      Generate alt text for attachments.

## Changelog ##

### 0.1.0 (???? ??, 2018) ###
* Initial release.
