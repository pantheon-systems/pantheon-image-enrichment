# Pantheon Image Enrichment #
[![Unofficial](https://img.shields.io/badge/Pantheon-Unofficial-yellow?logo=pantheon&color=FFDC28)](https://pantheon.io/docs/oss-support-levels#unofficial)

**Contributors:** getpantheon, danielbachhuber  
**Tags:** pantheon, images  
**Requires at least:** 4.7  
**Tested up to:** 5.0  
**Stable tag:** 0.1.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Generate default alt text, block unsafe uploads, and enrich images using the Google Cloud Vision API.

## Description ##

[![Travis CI](https://travis-ci.org/pantheon-systems/pantheon-image-enrichment.svg?branch=master)](https://travis-ci.org/pantheon-systems/pantheon-image-enrichment)

Supercharge your WordPress Media Library with a variety of enhancements via the Google Cloud Vision API:

* **Generate default alt text for uploaded images without it.** Uses the landmark or logo if one is detected in the image, otherwise generates a comma-separated list of descriptive labels.
* **Block uploading of images deemed unfit for Google Safe Search.** Default behavior is to reject any images deemed likely or very likely to be adult, spoof, medical, violence, or racy.
* **Incorporate quadrant crop hints into the image crop process.** Based on the machine-identified point of focus, automatically defines `array( 'left', 'top' )`, etc. for any image sizes with `'crop' => true`.

Go forth and make awesome! And, once you've built something great, [send us feature requests (or bug reports)](https://github.com/pantheon-systems/pantheon-image-enrichment/issues).

## Installation ##

To install Pantheon Image Enrichment, follow these steps:

1. Install the plugin from WordPress.org using the WordPress dashboard.
2. Activate the plugin.

To install Pantheon Image Enrichment in one line with WP-CLI:

    wp plugin install pantheon-image-enrichment --activate

## WP-CLI Commands ##

This plugin implements a variety of [WP-CLI](https://wp-cli.org) commands. All commands are grouped into the `wp pantheon image` namespace.

    NAME
    
      wp pantheon image
    
    DESCRIPTION
    
      Generates default alt text and more.
    
    SYNOPSIS
    
      wp pantheon image <command>
    
    SUBCOMMANDS
    
      generate-alt-text      Generate alt text for attachments.

## Implementation Details ##

If you're curious about how Pantheon Image Enrichment works, let's take a high-level walkthrough.

The `Hooks` class defines all of the integration points with WordPress:

* `filter_wp_handle_upload_prefilter` - At the very beginning of the upload process, this prefetches all Google Cloud Vision data we might want to use. Based on the fetched data, also rejects upload of unsafe images.
* `action_add_attachment` - Generates alt text if none exists when attachment object is saved to the WordPress database.
* `filter_intermediate_image_sizes_advanced` - Introduces quadrant-based crop hints (e.g. `array( 'left', 'center' )`) when generating cropped versions of the attachment.

Under the hood, the `Hooks` class calls the `Enrich` class for specific enrichment behaviors:

* `generate_alt_text_if_none_exists` - Generates alt text for an attachment if none exists.
* `generate_alt_text_if_missing_or_previously_enriched` - Generates alt text for an attachment if none exists or it was previously enriched.
* `generate_alt_text_always` - Always generates alt text, even if some exists already.
* `get_likely_safe_search_violations` - Gets any `LIKELY` or `VERY_LIKELY` Google Safe Search violations.
* `get_quadrant_crop_suggestions` - Gets quadrant-based crop suggestions for a given image.

Lastly, the `GCV` class is the workhorse that provides an interface to the Google Cloud Vision API.

## Changelog ##

### 0.1.0 (June 25th, 2018) ###
* Initial release.
