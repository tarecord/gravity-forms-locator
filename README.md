# Gravity Form Locator
![](https://travis-ci.org/tarecord/gravity-forms-locator.svg?branch=master)

A Gravity Form add-on that shows a list of forms with the page or post they are published on.

## Description

The Gravity Form Locator plugin aims to solve a big hole in the design of Gravity Forms' backend interface.

**Problem:** Currently there is no way to know which page or post a particular form is on.

This plugin solves that problem by adding a "Form Locations" page within the Gravity Forms menu so that all forms that have been added to pages or posts are visible on one screen. Links will take you to the form editor or the post editor so there is one convenient place to manage your forms.

Additionally, when editing a form in the backend, there is a "Locations" tab so you can view all the pages or posts where that form is currently used. It even includes forms located in drafts or private pages.

## Installation

Install Gravity Form Locator via the plugin directory, or upload the files manually to your server and follow the on-screen instructions.

## Testing

[Human Made has a docker container](https://github.com/humanmade/plugin-tester) for speeding up testing. Use it while within this project's root directory:
```
docker run --rm -v "$PWD:/code" humanmade/plugin-tester
```
