# __Randomonials__

This plugin randomizes a collection of testimonials for WordPress. By default, author and comment fields are displayed using the `[randomonials]` shortcode. Additional fields can be added by manually editing the randomonials_page.php template found in the public->partials directory before activation. The template should not be edited manually once a testimonial has been added, since it will break the continuity between the data file and template.

This plugin is multisite friendly. On a multisite installation, each blog will have its own set of testimonials associated with it.

This plugin has been tested and runs on WordPress 5.2.2 and 5.2.3. Testing on earlier versions was not carried out, but we welcome collaboration with anyone willing to do those tests.

### Displaying Randomonials
Shortcode Options

>[randomonials type="page" count="0" randomize="true"]

The `type` attribute can have one of the following values:
* `page` - Displays full page of testimonials
* `single` - Display a single testimonial
* `group` - Displays a row of testimonials 
* `rotator` - Displays a rotator widget cycling testimonials

__Note:__
> As of v1.0.0. only `page` is supported for `type`.

For count, a value of 0 means show all testimonials. Any value greater than 0 will limit the number of testimonials displated to the set quantity.

Testimonials are displayed in randomized order by default. That means on each page load the testimonials will be shuffled. The shuffling can be turned off by setting `randomize` to `false`.

## __Build Instructions__
### Requirements:
* NodeJS
* npm-run-all (global)

### Tasks:
* Build and package - _npm-run-all build_
* Build plugin only - _npm-run-all plugin_
* Archive current build - _npm-run-all archive_

### Build Steps:
1. Git clone `https://github.com/dresch86/wp-randomonials`
1. `cd` into cloned directory
1. `npm install`
1. `npm-run-all build`
1. Upload zip file in the build directory to WordPress

### __Future Goals:__
- [ ] Document all functions
- [ ] Functionality for displaying single testimonial
- [ ] Functionality for rotating testimonials
- [ ] Make template editable in the admin panel
- [ ] Use a JS table renderer to sync testimonials with JSON instead of refreshing page