# Teleport Source Installation via _Releases_

## Create a Working Directory for the Application

First, create a directory where you will run the Teleport application. Teleport will create subdirectories for it's work when being used, and it is best if you create it's own directory where it can live in isolation. A typical location might be `~/teleport/`.

## Download a Release of Teleport

Download a [release of Teleport](http://github.com/modxcms/teleport/releases) and extract it into your Teleport working directory.

## Install Dependencies with Composer

You will need to install the dependencies for using and optionally for developing Teleport using [Composer](http://getcomposer.org/).

Within the root of your cloned repository's working directory:

    composer install --dev

## Running Teleport from Source

The documentation assumes you have installed the phar distribution of Teleport. Since you have chosen to install from source, when you see `php teleport.phar` in the documentation examples you should substitute `bin/teleport` to run the application instead.

## Get Started

Get started using Teleport by [generating a Profile](../use/profile.md) of a local MODX installation.
