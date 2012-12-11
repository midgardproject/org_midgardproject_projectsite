Midgard Project website component
=================================

This Midgard MVC component is designed for running the [Midgard Project](http://www.midgard-project.org/) website according to the new _single page for product_ approach outlined on [a midgard-dev thread](http://lists.midgard-project.org/pipermail/dev/2011-March/002883.html).

## Setup with Vagrant

You'll need a working [Vagrant](http://vagrantup.com/) installation. Make a local clone of this repository:

    $ git clone git://github.com/midgardproject/org_midgardproject_projectsite.git

Then start Vagrant:

    $ cd org_midgardproject_projectsite/setup/vagrant
    $ vagrant up

This will take a while as Vagrant will construct a new virtual machine with Ubuntu 12.10, Midgard, and Midgard MVC.

The site will be available in http://localhost:8181

### Restarting the server

The Vagrant setup of the site uses AppServer-in-PHP for serving the pages. If you make modifications to any files, you'll have to restart the server.

Make a SSH connection to the Vagrant virtual machine:

    $ vagrant ssh

And then just restart the service:

    $ sudo service midgardmvc stop
    $ sudo service midgardmvc start

## Working with the content

As this site isn't live yet, you can work on the content on your local installation. We will keep a copy of all the content in this Git repository in Midgard's serialized format.

To import the initial content, run:

    $ vagrant ssh
    $ php bin/import_content.php

To get started with editing, log in at http://localhost:8181/mgd:login

This should make the [Midgard Create user interface](http://bergie.iki.fi/blog/introducing_the_midgard_create_user_interface/) available.

To share your modifications, run:

    $ vagrant ssh
    $ php bin/export_content.php
    $ git commit -m "Some new modifications"
    $ git push
