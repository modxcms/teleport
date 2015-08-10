# Teleport

Teleport is an extensible scripting toolkit for working with one or more local MODX Revolution installations.

Teleport currently functions primarily as a packaging toolkit which extends the MODX Transport APIs and provides commands for extracting and injecting customizable snapshots of MODX deployments. But it can be extended easily to perform an infinite variety of actions related to MODX.


## Requirements

In order to use Teleport, your environment must at least meet the following requirements:

* PHP >= 5.4
* MODX Revolution >= 2.1 (MySQL)

You must also be able to run PHP using the CLI SAPI.

NOTE: At the current time, various Teleport Extract tpls only support MySQL deployments of MODX Revolution.

Usage on Linux environments with the PHP posix extension can take advantage of advanced user-switching features.

Teleport strives to be a multi-platform tool, and currently works equally well in Linux and OS X environments. Windows support is unknown at this time; Windows contributors wanted.


## Installation

There are several methods for installing Teleport. The easiest way to get started is by installing the Teleport Phar distribution.

_IMPORTANT: Using any of the installation methods, make sure you are running Teleport as the same user PHP runs as when executed by the web server. Failure to do so can corrupt your MODX site by injecting and/or caching files with incorrect file ownership._

### Download and Install Phar

Create a working directory for Teleport and cd to that directory, e.g.

    mkdir ~/teleport/ && cd ~/teleport/

Download the latest [`teleport.phar`](http://modx.s3.amazonaws.com/releases/teleport/teleport.phar "teleport.phar") distribution of Teleport into your Teleport working directory.

Create a Profile of a MODX site:

    php teleport.phar --action=Profile --name="MyMODXSite" --code=mymodxsite --core_path=/path/to/mysite/modx/core/ --config_key=config

Extract a Snapshot from the MODX site you just profiled:

    php teleport.phar --action=Extract --profile=profile/mymodxsite.profile.json --tpl=phar://teleport.phar/tpl/develop.tpl.json


### Other Installation Methods

Alternatively, you can install Teleport using the source and [Composer](http://getcomposer.org/). Learn more about using [git clone](doc/install/git-clone.md) or a [release archive](doc/install/releases.md).

_IMPORTANT: If you want to use the Teleport HTTP Server you cannot use the Phar distribution. You MUST use one of the other installation methods._

### Teleport in your PATH

With any of the installation methods you can create an executable symlink called teleport pointing to bin/teleport, or directly to the teleport.phar. You can then simply type `teleport` instead of `bin/teleport` or `php teleport.phar` to execute the teleport application.


## Basic Usage

In all of the usage examples that follow, call teleport based on how you have installed the application. For example, if you installed from source, substitute `bin/teleport` for `php teleport.phar`; if you have created an executable symlink to the teleport.phar, substitute `teleport` for `php teleport.phar` in the sample commands. The following examples assume you have installed the teleport.phar distribution.

_NOTE: **Before** using Teleport with a MODX site, you will need to **create a Teleport Profile** from the installed site._

### Create a MODX Site Profile

You can create a Teleport Profile of an existing MODX site using the following command:

    php teleport.phar --action=Profile --name="MySite" --code=mysite --core_path=/path/to/mysite/modx/core/ --config_key=config

The resulting file would be located at profile/mysite.profile.json and could then be used for Extract or Inject commands to be run against the site represented in the profile.

Learn more about [Teleport Profiles](doc/use/profile.md).

### Extract a Snapshot of a MODX Site

You can Extract a Teleport snapshot from a MODX site using the following command:

    php teleport.phar --action=Extract --profile=profile/mysite.profile.json --tpl=phar://teleport.phar/tpl/develop.tpl.json

The snapshot will be located in the workspace/ directory if it is created successfully.

You can also Extract a Teleport snapshot and push it to any valid stream target using the following command:

    php teleport.phar --action=Extract --profile=profile/mysite.profile.json --tpl=phar://teleport.phar/tpl/develop.tpl.json --target=s3://mybucket/snapshots/ --push

In either case, the absolute path to the snapshot is returned by the process as the final output. You can use this as the path for an Inject source.

_NOTE: The workspace copy is removed after it is pushed unless you pass --preserveWorkspace to the CLI command._

Learn more about the [Teleport Extract](doc/use/extract.md) Action.

### Inject a Snapshot into a MODX Site

You can Inject a Teleport snapshot from any valid stream source into a MODX site using the following command:

    php teleport.phar --action=Inject --profile=profile/mysite.profile.json --source=workspace/mysite_develop-120315.1106.30-2.2.1-dev.transport.zip

_NOTE: If the source is not within the workspace/ directory a copy will be pulled to that location and then removed after the Inject completes unless --preserveWorkspace is passed._

Learn more about the [Teleport Inject](doc/use/inject.md) Action.

### UserCreate

You can create a user in a profiled MODX site using the following command:

    php teleport.phar --action=UserCreate --profile=profile/mysite.profile.json --username=superuser --password=password --sudo --active --fullname="Test User" --email=testuser@example.com

_NOTE: This uses the security/user/create processor from the site in the specified profile to create a user, and the action accepts any properties the processor does._

Learn more about the [Teleport UserCreate](doc/use/user-create.md) Action.


## Get Started

Learn more about Teleport in the [documentation](doc/index.md).

## License

Teleport is Copyright (c) MODX, LLC

For the full copyright and license information, please view the [LICENSE](./LICENSE "LICENSE") file that was distributed with this source code.
