# Overview

When you pick up an issue to work on, it will already have been triaged with the branch name you should use, and the branch you should use as a base. Typically, this will follow [gitflow](http://nvie.com/posts/a-successful-git-branching-model/) principles.

If you wish to start contributing, visit [https://github.com/VATSIM-UK/core/wiki/Reporting-and-Tracking-Issues](https://github.com/VATSIM-UK/core/wiki/Reporting-and-Tracking-Issues) for information on picking up new issues to work on.

# Development Guide

Please familiarise yourself with the [Development Guide](https://github.com/VATSIM-UK/core/wiki/Development-Guide) to understand how to write your code.

# Branching

Until a PR is made, a branch is considered to be your branch, and is owned by you - rebase it, force push, do as you wish, until you submit a PR. Double-check you are definitely on the correct branch before force-pushing. Master and develop are protected from force-pushing.

Collaborative work where multiple people are working on the same branch must have a PR associated with it, with [WIP] in the title. If you wish to work on a branch with someone, you should arrange this before pulling and working on the branch.

The majority of features, unless they are extremely small, should be developed in the main repository, and not forks, where possible (all Web Services Department must use branches in the main repository). Hotfix branches must be versioned (hotfix/x.y.z), not named, and feature branches must be named (feature/my-feature), not versioned. Any hotfix PRs from outside of the repository must be merged to a hotfix/x.y.z branch before being merged to master.

* A new feature should branch from `develop` to `feature/<JIRA-ISSUE-ID>-<name>`
* On completion, it will be merged (`--no-ff`) with `develop`
* Final testing before release occurs on `release/<version>` branches
* Once a branch has been merged, it should not receive any further commits, regardless of how much time has passed - to contribute further, you will need to start another branch off of `develop`

# Committing

* All code changes should be specific to the issue the branch/PR is for. If they address a separate issue, you should either use a separate branch, or check before you use the same branch for both issues.
* For guidance on how to describe and format your commits, see [https://chris.beams.io/posts/git-commit/](https://chris.beams.io/posts/git-commit/).
* Until you submit a PR, your branch is yours, regardless of whether it has been pushed. Feel free to rebase and force push as necessary to clean things up, unless you have submitted a PR, or unless someone else is working with you on the same branch.
* When addressing requested changes in PRs, under no circumstances should the commit message be "Address reviewer X's comments". This doesn't tell anyone what has actually changed, and it loses all meaning once the PR has been merged. Instead, you should continue to describe what has been committed in the summary, rather than why you have commited it. If you wish to say it's addressing some specific comments, that could go in the body of the commit if necessary.

# Continuous Integration

TravisCI should be in a passing state in order for a pull request to be merged.

## Styling (StyleCI)

In order to keep branches clean, contributors should not manually attempt to fix styling on branches (because then they tend to spread across 2-3 commits). They create unnecessary commits, and we can automatically fix after it's merged. We want to focus on code quality, not style.

PRs with failing StyleCI are fine, and preferred over PRs with style commits. We care about the content, not the style.

Larger features, especially those with WIP PRs, and many commits, can merge StyleCI fixes. The limit on this is 1 StyleCI fix every 20 commits at most - there MUST be 20 commits present before a StyleCI merge, and there must be 20 commits since the last StyleCI merge.

All StyleCI merges should be squashed, not merged with a merge commit.

# Testing

All code should be covered by the appropriate tests, and new PRs should not decrease the coverage level of the application. For guidance on testing your code, visit [https://github.com/VATSIM-UK/core/wiki/Testing](https://github.com/VATSIM-UK/core/wiki/Testing).

# Pull Requests

Any pull requests should be for issues that you have been assigned (see [https://github.com/VATSIM-UK/core/wiki/Reporting-and-Tracking-Issues](https://github.com/VATSIM-UK/core/wiki/Reporting-and-Tracking-Issues). If you want to contribute something which doesn't yet have an issue, please first open an issue and wait for it to be approved and assigned.

Please be aware of the following:
* Any configuration changes or additional deployment steps must be added to the `README.md` file.
* The PR title should describe the change that has been made, and should include the JIRA issue tag (e.g. `CORE-33 Documentation Updates`).
* The PR description should confirm what changes have been made, and include an explanation for any aspects of the PR you believe require clarification.
* You should be prepared to answer any questions about your PR when it is reviewed, and to make any requested changes. Sometimes, you may disagree with a requested change - as the developer of the code, you may understand the code better, and be able to provide a counter-argument. Discussion is encouraged where there is not a general consensus.
* Sometimes, it may be beneficial when working on a large issue with multiple contributors to open a new PR, and mark it with [WIP]. WIP PRs will not be reviewed or merged until the [WIP] has been removed.

# Other Notes

Laracast subscriptions are ideal for everyone from beginners to PHP, to advanced users who have been using Laravel for years. If you wish to improve your skill set, learn about new things, and generally improve the code you write, a subscription is highly recommended, even if you cancel it after a month (https://laracasts.com/join).