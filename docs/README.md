[master_build_status]: https://travis-ci.com/VATSIM-UK/core.svg?branch=master
[master_style_ci_status]: https://github.styleci.io/repos/75443611/shield?branch=master
[code_climate_maintainability]: https://api.codeclimate.com/v1/badges/6a47acbf3b7798883e7e/maintainability
[master_codecov_status]: https://codecov.io/gh/VATSIM-UK/core/branch/master/graphs/badge.svg
[beta_status]: https://vatsim-uk.deploybot.com/badge/88313865825892/135269.png
[production_status]: https://vatsim-uk.deploybot.com/badge/88313865825892/93858.png
[alpha_status]: https://vatsim-uk.deploybot.com/badge/77558059841720/140917.png

<p align="center">
    <a href="https://www.vatsim.uk"><img src="https://www.vatsim.uk/images/branding/vatsimuk_blackblue.png" width="250px" /></a>
</p>

# About

Core is the flagship application of VATSIM UK. Originally designed to handle Single Sign-On (SSO) for our other applications, it now serves as the main hub for all member information and any new features we introduce.

# Status

|      Check      |                            Provider                           |              Status             |
|-----------------|---------------------------------------------------------------|---------------------------------|
| Build           | [TravisCI](https://travis-ci.com/VATSIM-UK/core)              | ![master_build_status]          |
| Code Style      | [StyleCI](https://github.styleci.io/repos/75443611)           | ![master_style_ci_status]       |
| Maintainability | [CodeClimate](https://codeclimate.com/github/VATSIM-UK/core)  | ![code_climate_maintainability] |
| Coverage        | [CodeCov](https://codecov.io/gh/VATSIM-UK/core/branch/master) | ![master_codecov_status]        |

# Environments

|     Env    |              URL              |        Status        |
|------------|-------------------------------|----------------------|
| Production | https://www.vatsim.uk         | ![production_status] |
| Beta       | https://beta.vatsim.uk        | ![beta_status]       |
| Alpha      | https://alpha.vatsim.uk       | ![alpha_status]      |

# Issue Tracking

Development tasks (new features, technical tasks, bugs, etc.) should be tracked and actioned using JIRA, at [https://vatsimuk.atlassian.net/browse/CORE](https://vatsimuk.atlassian.net/browse/CORE). All current issues are publicly visible, however in order to create issues, you will need to [sign up](https://vatsimuk.atlassian.net/login).

When submitting an issue, please:
* Search the issue tracker before you submit your issue, as it may already be present.
* Provide as much information as possible, to ensure others are able to understand and act upon the information you provide.

To start work on an issue, post a comment on the issue requesting it be assigned to you. Once it has been assigned, you are free to start work on it.

# Deploying Locally

To deploy core locally, we have provided a pre-configured Vagrantfile based on the (laravel/homestead)[https://laravel.com/docs/homestead] Vagrant Box. We have then abstracted away a number of additional steps required into a series of scripts within this repository.

## Requirements

You will need Vagrant installed. (More Information)[https://www.vagrantup.com/downloads.html]

## Steps
_NOTE: These steps will only work on a Linux/Unix Machine_

* Execute `./vagrant.generateEnv.sh` to generate your local Application Environment Configuration. This script uses the `.env.example` file and will prompt you for inputs as required. When finished, you will have a new `.env` file in your repository root. If you need to update it, you can do this manually or run the script again and select `y` when prompted to use your existing `.env` file.
* Execute `./vagrant.deploy.sh` to download the `laravel/homestead` box, install the `hostsupdater` vagrant plugin and start up the box. Once the box is running it will run the postInstall actions and when complete, you should be able to navigate to the `APP_URL` (`vukcore.test` by default) in your browser and see a local version of core running.
