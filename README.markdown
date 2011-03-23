Midgard Project website component
=================================

This Midgard MVC component is designed for running the [Midgard Project](http://www.midgard-project.org/) website according to the new _single page for product_ approach outlined on [a midgard-dev thread](http://lists.midgard-project.org/pipermail/dev/2011-March/002883.html).

## Setup

You need a working Midgard2 + Midgard MVC installation. To install this website, run:

    $ midgardmvc install https://github.com/midgardproject/org_midgardproject_projectsite/raw/master/application.yml midgard

Then to run the site, use:

    $ ./midgard/run
    
The site will be available in http://localhost:8001

Please note that the Planet feed aggregator functionality requires the cron setup as outlined in [com_meego_planet README](https://github.com/nemein/com_meego_planet/blob/master/README.markdown).

## Working with the content

As this site isn't live yet, you can work on the content on your local installation. We will keep a copy of all the content in this Git repository in Midgard's serialized format.

To import the initial content, run:

    $ cd org_midgardproject_projectsite
    $ php -c ../php.ini bin/import_content.php

To get started with editing, log in at http://localhost:8001/mgd:login

This should make the [Midgard Create user interface](http://bergie.iki.fi/blog/introducing_the_midgard_create_user_interface/) available.

To share your modifications, run:

    $ cd org_midgardproject_projectsite
    $ php -c ../php.ini bin/export_content.php
    $ git commit -m "Some new modifications"
    $ git push
