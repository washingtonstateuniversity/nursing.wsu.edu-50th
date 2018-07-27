# WSU College of Nursing 50th Anniversary Theme

[![Build Status](https://travis-ci.org/washingtonstateuniversity/nursing.wsu.edu-50th.svg?branch=master)](https://travis-ci.org/washingtonstateuniversity/nursing.wsu.edu-50th)

A WSU Spine child theme for the College of Nursing 50th Anniversary site.

## Theme features

### WSUWP Cards shortcode

This theme includes the `[wsuwp_cards]` shortcode. At its most basic, it displays the ten most recent posts published on the site, but it can be customized through the following attributes:

* `count` controls the number of posts to display;
* `category` allows the display of posts from a specific category based on the slug provided;
* `orderby` accepts either `title` or `rand` to display posts respectively by title or in random order;
* `effects` accepts comma-separated values for adding scroll-based effects to the cards:
  * `fix-images` fixes the position of each card's feature image once it reaches the top of the viewport, then restores regular scrolling behavior once the bottom of the image is in the viewport;
  * `fade-images` decreases the opacity of each card's feature image as it is scrolled up out of the viewport.
