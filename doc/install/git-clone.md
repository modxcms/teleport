# Teleport Source Installation via _Git Clone_

Contributors will want to use this method to install Teleport so they can easily submit pull requests to the project.

## Create a Working Directory for the Application

First, create a directory where you will run the Teleport application. Teleport will create subdirectories for it's work when being used, and it is best if you create it's own directory where it can live in isolation. A typical location might be `~/teleport/`.

## Fork modxcms/teleport

Go to modxcms/teleport and click the Fork button.

_NOTE: This is only required if you plan on submitting pull requests to the Teleport application._

## Clone

Clone the modxcms/teleport repository into your Teleport working directory:

    git clone git@github.com:modxcms/teleport.git ~/teleport/

or your fork:

    git clone git@github.com:username/teleport.git ~/teleport/

## Add upstream

If you forked the repository in order to contribute, you will want to add the official modxcms/teleport repository as a remote:

    git remote add upstream git@github.com:modxcms/teleport.git

## Install Dependencies with Composer

You will need to install the dependencies for using and optionally for developing Teleport using [Composer](http://getcomposer.org/).

Within the root of your cloned repository's working directory:

    composer install --dev

## Running Teleport from Source

The documentation assumes you have installed the phar distribution of Teleport. Since you have chosen to install from source, when you see `php teleport.phar` in the documentation examples you should substitute `bin/teleport` to run the application instead.

## Get Started

Get started using Teleport by [generating a Profile](../use/profile.md) of a local MODX installation.
