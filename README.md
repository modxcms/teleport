# Teleport

A packaging toolkit for MODX Revolution


## Requirements

In order to use Teleport, your environment must at least meet the following requirements:

* PHP >= 5.3
* MODX Revolution >= 2.1

You must also be able to run PHP using the CLI SAPI.


## Installation

There a two methods for installing Teleport.

### Download and Install Phar Archive

### Install via Composer


## Usage

Before using Teleport with a MODX site, you will need to create a Teleport Profile from the installed site.

### Profile

You can create a Teleport Profile of an existing MODX site using the following command:

    teleport --action=Profile --name="MySite" --code=mysite --core_path=/path/to/mysite/modx/core/ --config_key=config

The resulting file would be located at workspace/mysite.profile.json and could then be used for Extract or Inject commands to be run against the site represented in the profile.

### Extract

You can Extract a Teleport snapshot from a MODX site using the following command:

    teleport --action=Extract --profile=workspace/mysite.profile.json --tpl=tpl/develop.tpl.json

The snapshot will be located in the workspace/ directory if it is created successfully.

You can also Extract a Teleport snapshot and push it to any valid stream target using the following command:

    teleport --action=Extract --profile=workspace/mysite.profile.json --tpl=tpl/develop.tpl.json --target=s3://mybucket/snapshots/ --push

In either case, the absolute path to the snapshot is returned by the process as the final output. You can use this as the path for an Inject source.

_NOTE: The workspace copy is removed after it is pushed unless you pass --preserveWorkspace to the CLI command_

### Inject

You can Inject a Teleport snapshot from any valid stream source into a MODX site using the following command:

    teleport --action=Inject --profile=workspace/mysite.profile.json --source=workspace/mysite_develop-120315.1106.30-2.2.1-dev.transport.zip

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

    teleport --action=UserCreate --profile=workspace/mysite.profile.json --username=superuser --password=password --sudo --active --fullname="Test User" --email=testuser@example.com

_NOTE: This uses the security/user/create processor from the site in the specified profile to create a user, and the action accepts any properties the processor does._
