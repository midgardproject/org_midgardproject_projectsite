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
