# Gravity Form Locator
![](https://travis-ci.org/tarecord/gravity-forms-locator.svg?branch=master)

A Gravity Form add-on that shows a list of forms with the page or post they are published on.

### Note: This Plugin has been archived and a new version has been started: https://github.com/tarecord/locations-add-on-for-gravity-forms

## Description

The Gravity Form Locator plugin aims to solve a big hole in the design of Gravity Forms' backend interface.

**Problem:** Currently there is no way to know which page or post a particular form is on.

This plugin solves that problem by adding a "Form Locations" page within the Gravity Forms menu so that all forms that have been added to pages or posts are visible on one screen. Links will take you to the form editor or the post editor so there is one convenient place to manage your forms.

Additionally, when editing a form in the backend, there is a "Locations" tab so you can view all the pages or posts where that form is currently used. It even includes forms located in drafts or private pages.

## Installation

Install Gravity Form Locator via the plugin directory, or upload the files manually to your server and follow the on-screen instructions.

## Testing

1. Install PHP Unit via Composer
```sh
~ composer install
```
2. Set up local database (Here's [a quick walkthrough to install MySQL on mac](https://tableplus.com/blog/2018/11/how-to-download-mysql-mac.html#3-using-homebrew-service-to-download))
3. Install the WordPress tests
```sh
~ ./bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]
```
4. Run the tests
```sh
~ ./vendor/bin/phpunit
```
