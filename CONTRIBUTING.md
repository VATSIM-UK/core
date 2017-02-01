# Welcome 

Welcome to the UK Core Web Services Repository, for all post Web Services in the UK!  Thank you for your interest in contributing to the project.  Full details and guidelines on how to ensure this project is managed well are included below.

## Contributor license agreement
By submitting code as an individual you agree that VATSIM UK can use your amendments, fixes, patches, changes, modifications, submissions and creations in the upkeep, maintenance and deployment of UK Web Services and that the ownership of your submissions transfers to VATSIM UK in their entirety.

## Helping others
Please help other UK Core WS contributors wherever you can (everybody starts somewhere).  If you require assistance (or wish to provide additional assistance) you can find our contributors in the VATSIM UK slack team.

To access Slack, you can visit https://core.vatsim-uk.co.uk and follow the registration instructions.  Once you've logged in, find the channel "WebServices"

# I want to contribute!

If you wish to contribute to the UK Core Web Services project, there's many ways in which you can help out.

## Pulling & Deploying Locally

Whilst this document will provide a _rough_ outline of the steps required to utilise the tools required for deployment, there is an assumption that you have a basic appreciation for `W/L/MAMP`, `Git`, `Composer`, `PHP` and `MySQL`.

### Setup an environment

TL;DR: You need a local stack with `PHP 5.6`, `MySQL 5.5`, `Git`, `Composer`, `PHPUnit`, `Npm` and `gulp`.  The assumption is you're using Apache.

#### W/L/MAMP

If you don't already have a local development stack then you should get one!  I'd really recommend [WampServer version 3.0](http://www.wampserver.com/en/) however it's important that you then download the [MySQL 5.5.50 release for your OS](https://sourceforge.net/projects/wampserver/files/WampServer%203/WampServer%203.0.0/Addons/Mysql/).  Unfortunately I can't advise on Mac alternatives, however you Linux users get a life of luxury when it comes to installing these things!

Setting up WampServer is really simple - follow the installer instructions.  Any problems, come and find us in the Slack channel.

#### MySQL

Create a new database within MySQL called `vuk_core` (or something appropriate) along with a user with full rights.

#### Register for MailTrap.io

[MailTrap.io](https://MailTrap.io) is a fantastic free service - it acts like an SMTP server, but it just catches all mail and displays it to you.  Really useful to avoid setting people _actual_ emails.

#### Adding things to your Environment Path (this is important and dangerous)

**Warning: Do not proceed if you're not comfortable**

To make using the software we're installing **far** easier, it'd be a good idea to add a few things to your Environment Path.

There's a really good guide on [the PHP.net website](https://secure.php.net/manual/en/faq.installation.php#faq.installation.addtopath) - if you're using WampServer then your PHP Directory will be (by default) `C:\wamp\bin\php\php5.6.16`.  Remember how to do this as there are recommendations further on of other things to add to your path.

#### Git

You can either be hardcore and download the [Git-SCM Bash Tools](https://git-scm.com/) and use the command line, or download the Git-SCM Bash Tools and install something like [SourceTree](https://www.sourcetreeapp.com/download/) on top to make your experience a little more pleasant.

Add the Git `bin` directory to your Path. **Warning, this is dangerous.**

#### Composer

You'll need to make use of Composer a lot for the UK Web Services.  Head on over to [the Composer website](https://getcomposer.org/download/) who can offer far better support for installing their software than I can.  Just remember **to install it globally**.

#### PHPUnit

We make use of UnitTesting (and it'll be expected that you do too, if you're contributing code).  Head on over to the [PHPUnit testing](https://phpunit.de/manual/current/en/installation.html) website to see how to set this up on your machine.

#### Node.js, NPM & Gulp

We utilise Gulp for our CSS, JS and asset management.  You'll need to throw NPM onto your machine.  [Dave McFarland does a great job of detailing how this should happen](http://blog.teamtreehouse.com/install-node-js-npm-windows).

_We'll warn you now, NPM can be a bit of a pain._

### Pulling the Code

TL;DR: Fork our [GitHub Repository](https://github.com/vatsim-uk/core), and pull the code to your `C:\wamp\www\vukcore` directory.

Some good tutorials on _how to use Git_:
* [CodeAcademy](https://www.codecademy.com/learn/learn-git)
* [RogerDudler](https://rogerdudler.github.io/git-guide/)
* [Try.GitHub.Io](https://try.github.io/levels/1/challenges/1)
* [Laracasts](https://laracasts.com/series/git-me-some-version-control) - premium, but the best $9 you'll spend a month.

If you're going to be contributing code to the repository then you'll need to visit [our repository](https://github.com/vatsim-uk/core) and click the button that says `Fork`.  This will create a personal copy of the repository (one that you can write to), since you won't have write permissions on our repository.

With that done, open up your chosen Git tool and checkout **your** repository into `C:\wamp\www` (or wherver you installed WampServer).

### Adding some Virtual Hosts

Once your environment has been setup, you'll need to define some [virtual hosts](http://www.coderomeos.org/how-to-set-up-virtual-host-on-wamp-server) - you'll need something along the lines of `vukcore.localhost` and `vt.vukcore.localhost`.  Whenever we add a new server, it'll prefix `vukcore` with the identifier.  These VirtualHosts should **both** point to the `public` directory within the `core` directory.

### Deploying

TL;DR: A standard Laravel deployment process - install Composer Dependancies, edit the .env directory, run migrations, run gulp for production.

**You will need to follow sections 1, 3 and 4 on every new pull of our codebase**

#### 1 - Composer Dependancies

In the command line, run `composer install -o` to install all of the needed dependencies.  This might take a while, so go and whack that kettle on!

Beyond your initial setup, you can just run `composer update -o` on future code pulls.

#### 2 - Environment Variables

* Within your checked out code, copy `.env.example` to `.env`
* Edit the settings within that file as appropriate
 * `APP_ENV` = `development`
 * `APP_DEBUG` = `true`
 * `APP_DEBUGBAR` = `true`
 * `APP_KEY` = Leave blank, we'll fix this in a second
 * `APP_URL` = Whatever URL you setup in the last section (recommended `vukcore.localhost`)
 * `SESSION_DOMAIN` = Leave this blank (i.e `SESSION_DOMAIN=`)
 * `DB_MYSQL_*` = Should match the MySQL account you setup.
 * `TS_*` = If you have your own TS server, integration information can be added here.
 * `MAIL_*` = SMTP, setup the details as per your MailTrap.io settings
 * `SLACK_*` = You won't have access, so leave this blank
 * `VATSIM_CERT_*` = You won't have access, so leave this blank
 * `SSO_*` = If you have an [VATSIM SSO Account](https://cert.vatsim.net/sso) by all means use it.

Once you've saved that, in the command line run `php artisan key:generate` to set the `APP_KEY` variable.

#### 3 - Migrate the databases

Laravel makes use of Database migrations for setting up/adding seed data to the MySQL database.

You'll need to run these with `php artisan migrate --step -vvv`.

>(You might also need to run `php artisan module:migrate -vvv` to remove various errors about the `vt_application` table, or others, not existing on the inital setup of your local installation)

I'd suggest you read about [Laravel migrations](https://www.laravel.com/docs/master/migrations).

#### 4 - Run Gulp

The first time you try and run gulp, you'll get an error.  To fix that:

* Open a command prompt
* Navigate to your project directory
* Run `npm install -g gulp`
* Install dependencies `npm install`

Now you can run gulp with the command `gulp` in your project directory.

#### 5 - Add your own account

Open a new command prompt and within your project directory type `php artisan tinker`.

Within the new PHP environment that you're given access to, enter: `\App\Models\Mship\Account::findOrRetrieve(XXXXXXXX)->setPassword("this_is_my_password");`

Make sure you replace `XXXXXXXX` with your CID and `this_is_my_password` with a development password.  When you navigate to the landing page, you can enter your CID and password to login.


#### 6 - (Optional) Admin panel access

If you would like to work on something in the admin panel (which includes some panels for the V/T module), you will need to perform some steps in your database to give your user the correct permissions.

>The admin panel can be accessed through `vukcore.localhost/adm/dashboard` (Replace vukcore.localhost with your URL accordingly)

To enable access to the panel:
* Go to your database, and find the `mship_account_role` table. Set the `role_id` to `1` for your CID.
* Navigate to `vukcore.localhost/adm/dashboard`. You should now be able to log into the admin panel. 

#### Relax

After all that setup, relax for 5 minutes!  If you've had any problems, come and find someone in the Slack team.

## Contributing to the code

If you're just getting started with GitHub (and project contributions) then we suggest you take a look at issues marked with the "up-for-grabs" label.  These issues will be of reasonable size and challenge, for anyone to start contributing to the project.  [This was inspired by an article by Ken C. Dodds](https://medium.com/@kentcdodds/first-timers-only-78281ea47455#.wior7p101).

If you're comfortable with contributing to Open Source projects on GitHub **please ensure you read our workflow**.

It is expected that you will follow the GitFlow Workflow for managing the repository, but here's some important points:

* A new feature should branch `development` into a `feature/<name>` branch
* On completion it should be merged **not rebased** with development
* You may see us creating `release/<version>` branches - this is where final testing will occur.
* Where an issue is **assigned** to somebody it means they're working on it.  Speak to them before trying to contribute.
* Where an issue is marked as **acknowledged** then it's been agreed that it is required and can be produced.
 * We would advise against working on issues without an Acknowledged label.
 * If you want to start work on an issue, comment on the issue asking to work on it and it'll be assigned to you by a developer.
* Any code change **must** have an associated issue detailing (in full) why the change is being made
* Commit messages **must** make use of the issue management commands
 * When resolving an issue via a commit `Fixes #123 - Added the user's profile picture to their profile.`
 * When addressing an issue via a commit `Restructured the user's profile page to make room for the badges in #456`
* Commits should ideally be "atomic" in nature
 * A good article explaining atomic commits and their benefits can be viewed [here](https://www.freshconsulting.com/atomic-commits/)
 * Atomic commits allow project maintainers (and you!) to roll back small parts of changes made without having widespread knock-on effects, among other benefits
 * Each task that needs completing should go into a separate commit
 * For example, if you're fixing a bug and making a layout change in one branch, you would do the layout change in one commit and the bug fix in another
 * Ideally, you should only commit when a particular task is completed, though this may not happen for perfectly valid reasons. More commits are preferable to less.

## Testing a new release

If you wish to test a new release, you can deploy either the `development` or `release/*` branches to a local machine, following the installation instructions in the README, and test a release.

## Issue Tracking

If you require **support** with the Web Services, please utilise our Slack channels for this purpose.  Issues regarding the features and functions of the Web Services or how to perform a specific task will not be handled.

When submitting an issue, there's a few guidelines we'd ask you to respect to make it easier to manage (and for others to understand):

* **Search the issue tracker** before you submit your issue - it may already be present.
* When opening an issue, please provide as much information as necessary to ensure others are able to act upon the requests or bug report.
* We measure our tasks in **time**.  If your issue is likely to be a **large job** then speak to us about making a milestone for it, and you can add multiple issues within one milestone. 
* Please ensure you add screenshots or documentation references for bugs/changes so we can quickly ascertain if the request is suitable.

## Testing

When writing your modifications for a single issue, or an entire feature, it is expected that your contribution is supported with associated Unit Tests.

There may be times that a test is already written - in these circumstances it is acceptable to contribute without adding an additional test.

## Merge Requests

We welcome merge requests with fixes and improvements to the project.  The features we really would like public support on are marked with "up-for-grabs" but other improvements are also welcome - please ensure you read over the merge work-flow below.

If you wish to add a new feature or you spot a bug that you wish to fix, **please open an issue for it first** on the [UK Core Web Services Repository](https://github.com/vatsim-uk/core/issues).

The work-flow for submitting a new merge request is designed to be simple, but also ensure consistency from **all** contributors:

* Fork the project into your personal space on GitHub.com
* Create a new branch (with the name `<issue_number>-<name>`, replacing issue_number with the issue number you're resolving)
 * The exception to this is where an entire feature is being tackled, spanning multiple issues.  In this case you can create a `feature/<name>` branch.
* Commit your changes
 * When writing commit messages, consider closing your issues via the commit message (by starting the message with "fix #22" or "fixes #22" and then your description.
  * The issues will be referenced in the first instance and then closed once the MR is accepted.
* **Add any important setps that must be followed on deployment to the README.md file**
* Push the commit(s) to your fork
* Submit a merge request (MR) to **our** development branch
* The MR title should describe the change that has been made
* The MR description should confirm what changes have been made, how you know they're correct (with references)
 * It is expected that all submissions have associated test cases.
* Ensure you link any relevant issues in the merge request (you can type hash and the issue ID, eg #275).  Comment on those issues linking back to the MR (you can reference MRs using the format !<MR_ID> for example !22).
* Be prepared to answer any questions about your MR when it is reviewed for acceptance.

**If you are actively working on a large change** consider creating the MR early but prefixing it with [WIP] as this will prevent it from being accepted *but* let other people know you're working on that issue.

# Expectations
As contributors and maintainers of this project, we pledge to respect all people who contribute through reporting issues, posting feature requests, updating documentation, submitting merge requests or patches, and other activities.

We are committed to making participation in this project a harassment-free experience for everyone, regardless of level of experience, gender, gender identity and expression, sexual orientation, disability, personal appearance, body size, race, ethnicity, age, religion, or favourite aircraft.

Project maintainers have the right and responsibility to remove, edit, or reject comments, commits, code, issues and other contributions that are not aligned to this Code of Conduct.

This code of conduct applies both within this project space and public spaces when an individual is representing the project or its community.
