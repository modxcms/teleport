# Profile

Before using Teleport with a MODX site, you will need to create a Teleport Profile from the installed site.

## Generate a MODX Site Profile

You can automatically generate a Teleport Profile of an existing MODX site using the following command:

    php teleport.phar --action=Profile --name="MySite" --code=mysite --core_path=/path/to/mysite/modx/core/ --config_key=config

The resulting profile will be located at profile/mysite.profile.json and can then be used for Extract, Inject, or other Teleport Actions to target the site represented in the profile.


## The Profile Action

### Required Arguments

* `--name='string'` - A name for the profile.
* `--core_path=path` - The `MODX_CORE_PATH` of the MODX install to generate the profile from.

### Optional Arguments

* `--code=string` - A simple name for the profile. If not provided, a filtered version of the `name` argument is used.
* `--config_key` - The `MODX_CONFIG_KEY` of the MODX install if different than the default `config` value. This is __required__ if the value is not `config` for the MODX install being targeted.


## Sample Profile

This is a sample Teleport Profile of a MODX site with all the required properties:

    {
        "name": "Revo-2.2.x",
        "code": "revo_22x",
        "properties": {
            "modx": {
                "core_path": "\/home\/user\/www\/revo-2.2.x\/core\/",
                "config_key": "config",
                "context_mgr_path": "\/home\/user\/www\/revo-2.2.x\/manager\/",
                "context_mgr_url": "\/revo-2.2.x\/manager\/",
                "context_connectors_path": "\/home\/user\/www\/revo-2.2.x\/connectors\/",
                "context_connectors_url": "\/revo-2.2.x\/connectors\/",
                "context_web_path": "\/home\/user\/www\/revo-2.2.x\/",
                "context_web_url": "\/revo-2.2.x\/"
            }
        }
    }

