## Pre-requisites

* PHP (version >= 5.4)
* Composer (version >= 1.0)
* SQLite3 (version >= 3.7)
* RubyGems (version >= 2.2.2)

## Installation

Note: all of the commands in the rest of this README are relative to the root of the repository. Therefore, when you've downloaded the repo, make sure you `cd major-project` to go into the top level of the repository before running any of the following commands.

I've made a handy one-line installer script which installs all dependencies and creates and populates the database. You should run that script OR the manual installation - not both.

### One-step installation

`php deploy/install.php`

You can also run `php deploy/install.php --refresh` at any time to clean and re-populate the database.

### Manual installation

* install dependencies with Composer (`composer install`)
* create production database: `sqlite3 data/production.db < data/db.sql`
* at this stage you can tweak `data/fixtures/seed.php` to populate the production database, or you can leave the database blank and fill in manually later.
* `gem install bundler`
* `bundle install`

### Maritime Collision Module

SmartResolution is an abstract ODR platform that takes "modules" of functionality. As an example, we've included a Maritime Collision module, which is [in its own repo](https://github.com/ChrisBAshton/smartresolution-module-maritime-collision), underlining the principle that the two components are kept entirely separate.

If you wish to have the maritime collision module on your installation, you'll need to run these additional commands. See [GitHub's explanation of submodules](http://git-scm.com/book/en/v2/Git-Tools-Submodules) to understand why.

```
git submodule init
git submodule update --remote
```

In future, when more modules have been developed, we'd move this behaviour to a dedicated administrator dashboard, rather than using Git submodules. Watch this space.

## Steps to run on each terminal instance

You need to export the Composer packages to your PATH:

`export PATH=./vendor/bin:$PATH`

Unfortunately, this needs to be done every time you start a new terminal session. For a more permanent solution, you'd need to edit your `~/.bash_profile` and add a line specific to where your project lives, e.g:

`export PATH=/Users/ashton/Dropbox/uni_major_project/_codebase/vendor/bin:$PATH`

Running either of these steps allows you to run tests, generate documentation, etc, as if those packages were installed globally.

## Seeing is believing

* run `./deploy/server.sh` to start the server
* go to http://127.0.0.1:8000/ in your browser
* you should now be able to register an account and log in using the forms provided

## Running tests

Run the unit tests:

`phpunit test`

Run the Cucumber tests:

`cucumber features`

...or just leave it to [Travis](https://travis-ci.org/ChrisBAshton/major-project.svg?branch=master)!

## Generate documentation

`phpdoc -d ./webapp/ -t ./webapp/docs/`

If you want the documentation step to generate class hierarchy diagrams, you'll also want to install GraphViz (Homebrew dependency):

`brew install graphviz`

## Architecture

* /data/ - contains fixture data for tests. This is also where the test and production SQLite3 databases reside (once they've been made).
* /features/ - contains my Cucumber features and Ruby step definitions.
* /modules/ - contains modules describing dispute types. Not much to see here yet, but this will one day encompass the 'Maritime Collision' dispute type.
* /test/ - contains my PHP unit tests.
* /vendor/ - automatically generated directory of dependencies, created by Composer.
* /webapp/ - contains the core ODR platform. Uses the MVCR compound design pattern.
    - /controller/ - contains the business logic for the system.
    - /model/ - contains my classes and utility functions.
    - /view/ - contains the user interface.
    - index.php - defines the environment and pulls in all dependencies.
    - routes.php - describes the routing between HTTP requests and controllers.
