# Teleport

Teleport is a packaging toolkit for MODX Revolution.

You can use Teleport to Extract and Inject custom transport packages that are defined by a configurable Extract template known as a `tpl`. These transport packages can contain anything from an entire snapshot of a MODX site to one specific file or database record from the site.


## Requirements

In order to use Teleport, your environment must at least meet the following requirements:

* PHP >= 5.3.3
* MODX Revolution >= 2.1 (MySQL)

You must also be able to run PHP using the CLI SAPI.

At the current time, Teleport only supports MySQL deployments of MODX Revolution.

Usage on Linux environments with the PHP posix extension can take advantage of advanced user-switching features.

Teleport strives to be a multi-platform tool, and currently works equally well in Linux and OS X environments. Windows support is unknown at this time; Windows contributors wanted.


## Installation

There are several methods for installing Teleport. Using either method described below, make sure you are running teleport as the same user PHP runs as when executed by the web server. Failure to do so can leave your site files with incorrect file ownership, preventing the MODX application from running properly on the web.

### Download and Install Phar

Create a directory for teleport to live and work in and cd to that directory, e.g.

`mkdir teleport/ && cd teleport/`

Download the latest [`teleport.phar`](http://modx.s3.amazonaws.com/releases/teleport/teleport.phar "teleport.phar") executable.

Create a Profile of a MODX site:

`php teleport.phar --action=Profile --name="MyMODXSite" --code=mymodxsite --core_path=/path/to/mysite/modx/core/ --config_key=config`

Extract a Snapshot from the MODX site you just profiled:

`php teleport.phar --action=Extract --profile=profile/mymodxsite.profile.json --tpl=phar://teleport.phar/tpl/develop.tpl.json`


### Install via Archive and Composer

Create a directory for teleport to live and work in and cd to that directory, e.g.

`mkdir ~/teleport/ && cd ~/teleport/`

Download the latest [release of teleport](https://github.com/modxcms/teleport/releases "Teleport releases") or a [zip of master](https://github.com/modxcms/teleport/archive/master.zip "zip of master branch") from GitHub and extract it into the directory you created.

Run composer install to get the dependencies.

`composer install`

Create a Profile of a MODX site:

`bin/teleport --action=Profile --name="MyMODXSite" --code=mymodxsite --core_path=/path/to/mysite/modx/core/ --config_key=config`

Extract a Snapshot from the MODX site you just profiled:

`bin/teleport --action=Extract --profile=profile/mymodxsite.profile.json --tpl=tpl/develop.tpl.json`


### Install via Git and Composer (for contributors)

Git clone the teleport repository into a directory for teleport to live and work and cd to that directory.

`git clone https://github.com/modxcms/teleport.git teleport/ && cd teleport/`

Run composer install to get the dependencies.

`composer install`

Create a Profile of a MODX site:

`bin/teleport --action=Profile --name="MyMODXSite" --code=mymodxsite --core_path=/path/to/mysite/modx/core/ --config_key=config`

Extract a Snapshot from the MODX site you just profiled:

`bin/teleport --action=Extract --profile=profile/mymodxsite.profile.json --tpl=tpl/develop.tpl.json`


### Teleport in your path

With any of the installation methods you can create an executable symlink called teleport pointing to bin/teleport, or directly to the teleport.phar. You can then simply type `teleport` instead of `bin/teleport` or `php teleport.phar` to execute the teleport application.

## Usage

In all of the usage examples that follow, call teleport based on how you have installed the application. For example, if you have created an executable symlink to the teleport.phar, substitute `teleport` for `php teleport.phar` in the sample commands. These examples assume you have installed teleport.phar.

Before using Teleport with a MODX site, you will need to create a Teleport Profile from the installed site.

### Create a MODX Site Profile

You can create a Teleport Profile of an existing MODX site using the following command:

    php teleport.phar --action=Profile --name="MySite" --code=mysite --core_path=/path/to/mysite/modx/core/ --config_key=config

The resulting file would be located at profile/mysite.profile.json and could then be used for Extract or Inject commands to be run against the site represented in the profile.

### Extract a Snapshot of a MODX Site

You can Extract a Teleport snapshot from a MODX site using the following command:

    php teleport.phar --action=Extract --profile=profile/mysite.profile.json --tpl=phar://teleport.phar/tpl/develop.tpl.json

The snapshot will be located in the workspace/ directory if it is created successfully.

You can also Extract a Teleport snapshot and push it to any valid stream target using the following command:

    php teleport.phar --action=Extract --profile=profile/mysite.profile.json --tpl=phar://teleport.phar/tpl/develop.tpl.json --target=s3://mybucket/snapshots/ --push

In either case, the absolute path to the snapshot is returned by the process as the final output. You can use this as the path for an Inject source.

_NOTE: The workspace copy is removed after it is pushed unless you pass --preserveWorkspace to the CLI command_

### Inject a Snapshot into a MODX Site

You can Inject a Teleport snapshot from any valid stream source into a MODX site using the following command:

    php teleport.phar --action=Inject --profile=profile/mysite.profile.json --source=workspace/mysite_develop-120315.1106.30-2.2.1-dev.transport.zip

_NOTE: If the source is not within the workspace/ directory a copy will be pulled to that location and then removed after the Inject completes unless --preserveWorkspace is passed_

#### How Inject Manipulates Snapshots

To prevent some data from corrupting a target MODX deployment when it is injected, the Inject action takes the following measures:

* Before Injection
    * modSystemSetting vehicles with the following keys are removed from the manifest:
        * session_cookie_domain
        * session_cookie_path
        * new_file_permissions
        * new_folder_permissions
* After Injection
    * modSystemSetting settings_version is set to the actual target version.
    * modSystemSetting session_cookie_domain is set to empty.
    * modSystemSetting session_cookie_path is set to MODX_BASE_PATH.

### UserCreate

You can create a user in a profiled MODX site using the following command:

    php teleport.phar --action=UserCreate --profile=profile/mysite.profile.json --username=superuser --password=password --sudo --active --fullname="Test User" --email=testuser@example.com

_NOTE: This uses the security/user/create processor from the site in the specified profile to create a user, and the action accepts any properties the processor does._


## License

Teleport is Copyright (c) MODX, LLC

For the full copyright and license information, please view the [LICENSE](./LICENSE "LICENSE") file that was distributed with this source code.
