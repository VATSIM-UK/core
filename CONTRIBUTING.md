# Welcome 

Welcome to the UK Core Web Services Repository, for all new Web Services in the UK!  Thank you for your interest in contributing to the project.  Full details and guidelines on how to ensure this project is managed well are included below.

# Contributor license agreement
By submitting code as an individual you agree that VATSIM UK can use your ammendments, fixes, patches, changes, modifications, submissions and creations in the upkeep, maintenance and deployment of UK Web Services and that the ownership of your submissions transfers to VATSIM UK in their entirety.

# Helping others
Please help other UK Core WS contributors wherever you can (everybody starts somewhere).  If you require assistance (or wish to provide additional assistance) you can find our contributors in the VATSIM UK slack team.

To access Slack, you can visit https://core.vatsim-uk.co.uk and follow the registration instructions.  Once you've logged in, find the channel "WebServices"

# I want to contribute!

If you wish to contribute to the UK Core Web Services project, there's many ways in which you can help out.

## Contributing to the code

If you're just getting started with GitLab (and project contributions) then we suggest you take a look at issues marked with the "up-for-grabs" label.  These issues will be of resonable size and challenge, for anyone to start contributing to the project.  [This was inspired by an article by Ken C. Dodds](https://medium.com/@kentcdodds/first-timers-only-78281ea47455#.wior7p101).

If you're comfortable with contributing to Open Source projects on GitLab **please ensure you read our expectations** for issue tracking, feature proposals and merge requests.

It is expected that you will follow the GitFlow Workflow for managing the repository:
* A new feature should branch `development` into a `feature/<name>` branch
* On completion it should be merged **not rebased** with development
* New releases will be branched from `development` into a `release/<number>` branch.
 * Any final patches will be added here, before being merged with both `development` and `production`.
* `feature/*` and `release/*` branches **must** be deleted once they've been merged and are completed.

## Testing a new release

If you wish to test a new release, you can deploy either the `development` or `release/*` branches to a local machine, following the installation instructions in the README, and test a release.

## Issue Tracking

If you require **support** with the Web Services, please utilise our Slack channels for this purpose.  Issues regarding the features and functions of the Web Services or how to perform a specific task will not be handled.

When submitting an issue, there's a few guidelines we'd ask you to respect to make it easier to manage (and for others to understand):
* **Search the issue tracker** before you submit your issue - it may already be present.
* When opening an issue, please provide as much information as necessary to ensure others are able to act upon the requests or bug report.
* **Issue Weight** allows us to get an idea of how much work is required.  This is measured in hours.  Any issue exceeding the maximum value to be set **must** be split into sub-tasks.
* If you disagree with the weight of an issue, comment and discuss this with the developers to reach a suitable medium (other contributors may base their decision to contribute on the weight assigned)
* Please ensure you add screenshots or documentation references for bugs/changes so we can quickly ascertain if the request is suitable.

## Testing

When writing your modifications for a single issue, or an entire feature, it is expected that your contribution is supported with associated Unit Tests.

There may be times that a test is already written - in these circumstances it is acceptable to contrinbute without adding an additional test.

## Merge Requests

We welcome merge requests with fixes and improvements to the project.  The features we really would like public support on are marked with "up-for-grabs" but other improvements are also welcome - please ensure you read over the merge work-flow below.

If you wish to add a new feature or you spot a bug that you wish to fix, **please open an issue for it first** on the [UK Core Web Services Repository](https://gitlab.com/vatsim-uk/core/issues).

The work-flow for submitting a new merge request is designed to be simple, but also ensure consistency from **all** contributors:
* Fork the project into your personal space on GitLab.com
* Create a new branch (with the name `issue-<issue_number>`, replacing issue_number with the issue number you're resolving)
 * The exception to this is where an entire feature is being tackled, spanning multiple issues.  In this case you can create a `feature/<name>` branch.
* Commit your changes
 * When writing commit messages, consider closing your issues via the commit message (by starting the message with "fix #22" or "fixes #22" and then your description.
  * The issues will be referenced in the first instance and then closed once the MR is accepted.
* **Add your changes to the CHANGELOG.md file**
 * Is is really important to also update the README.md and detail what actions need to be carried out when deploying.
* Push the commit(s) to your fork
* Submit a merge request (MR) to the development branch
* The MR title should describe the change that has been made
* The MR description should confirm what changes have been made, how you know they're correct (with references)
 * It is expected that all submissions have associated test cases.
* Ensure you link any relevant issues in the merge request (you can type hash and the issue ID, eg #275).  Comment on those issues linking back to the MR (you can reference MRs using the format !<MR_ID> for example !22).
* Be prepared to answer any questions about your MR when it is reviewed for acceptance

**If you are actively working on a large change** consider creating the MR early but prefixing it with [WIP] as this will prevent it from being accepted *but* let other people know you're working on that issue.

# Expectations
As contributors and maintainers of this project, we pledge to respect all people who contribute through reporting issues, posting feature requests, updating documentation, submitting merge requests or patches, and other activities.

We are committed to making participation in this project a harassment-free experience for everyone, regardless of level of experience, gender, gender identity and expression, sexual orientation, disability, personal appearance, body size, race, ethnicity, age, religion, or favourite aircraft.

Project maintainers have the right and responsibility to remove, edit, or reject comments, commits, code, issues and other contributions that are not aligned to this Code of Conduct.

This code of conduct applies both within this project space and public spaces when an individual is representing the project or its community.

It is expected that you will **not** disclose the contents of the repository.  Any access requires for the issue tracker or codebase should be sent to the Web Services Director of VATSIM UK.