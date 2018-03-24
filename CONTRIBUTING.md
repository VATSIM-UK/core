#### 6 - (Optional) Admin panel access

If you would like to work on something in the admin panel (which includes some panels for the V/T module), you will need to perform some steps in your database to give your user the correct permissions.

>The admin panel can be accessed through `vukcore.localhost/adm/dashboard` (Replace vukcore.localhost with your URL accordingly)

To enable access to the panel:
* Go to your database, and find the `mship_account_role` table. Set the `role_id` to `1` for your CID.
* Navigate to `vukcore.localhost/adm/dashboard`. You should now be able to log into the admin panel.

## Contributing to the code

If you're just getting started with GitHub (and project contributions) then we suggest you take a look at issues [marked with the "up-for-grabs" label](https://vatsimuk.atlassian.net/browse/CORE-26?jql=project%20%3D%20CORE%20AND%20labels%20%3D%20up-for-grabs%20order%20by%20lastViewed%20DESC).  These issues will be of reasonable size and challenge, for anyone to start contributing to the project.  [This was inspired by an article by Kent C. Dodds](https://medium.com/@kentcdodds/first-timers-only-78281ea47455#.wior7p101).

If you're comfortable with contributing to Open Source projects on GitHub **please ensure you read our workflow**.

It is expected that you will follow the GitFlow Workflow for managing the repository, but here's some important points:

* A new feature should branch `development` into a `feature/<JIRA-ISSUE-ID>-<name>` branch
* On completion it should be merged **not rebased** with development
* You may see us creating `release/<version>` branches - this is where final testing will occur.
* Where an issue is **assigned** to somebody it means they're working on it.  Speak to them before trying to contribute.
* If you want to work on an issue. Comment on the issue asking to work on it and a member of the team will add you to the project as a Contributor. This will then allow you to work on the ticket and update it as you go.
* Any code change **must** have an associated issue detailing (in full) why the change is being made
* Commits should ideally be "atomic" in nature
 * A good article explaining atomic commits and their benefits can be viewed [here](https://www.freshconsulting.com/atomic-commits/)
 * Atomic commits allow project maintainers (and you!) to roll back small parts of changes made without having widespread knock-on effects, among other benefits
 * Each task that needs completing should go into a separate commit
 * For example, if you're fixing a bug and making a layout change in one branch, you would do the layout change in one commit and the bug fix in another
 * Ideally, you should only commit when a particular task is completed, though this may not happen for perfectly valid reasons. More commits are preferable to less.

## Testing

When writing your modifications for a single issue, or an entire feature, it is expected that your contribution is supported with associated Unit Tests.

There may be times that a test is already written - in these circumstances it is acceptable to contribute without adding an additional test.

## Pull Requests

We welcome merge requests with fixes and improvements to the project.  The features we really would like public support on are marked with ["up-for-grabs"](https://vatsimuk.atlassian.net/browse/CORE-26?jql=project%20%3D%20CORE%20AND%20labels%20%3D%20up-for-grabs%20order%20by%20lastViewed%20DESC) but other improvements are also welcome - please ensure you read over the merge work-flow below.

If you wish to add a new feature or you spot a bug that you wish to fix, **please open an issue for it first** in the [CORE project](https://vatsimuk.atlassian.net/projects/CORE/issues).

**Note:** You will need to signup to create issues within the project. See [Issue Tracking](ISSUE_TRACKING.md) for more information.

The work-flow for submitting a new merge request is designed to be simple, but also ensure consistency from **all** contributors:

* Fork the project into your personal space on GitHub.com
* Create a new branch. You should include the JIRA issue ID in your branch name (e.g. `CORE-33-<branch name>`).
 * The exception to this is where an entire feature is being tackled, spanning multiple issues.  In this case you can create a feature branch which links up to the feature's associated JIRA 'Epic' Issue ID (e.g. `feature/CORE-33-<branch name>`)
* Commit your changes
* **Add any important steps that must be followed on deployment to the README.md file**
* Push the commit(s) to your fork
* Submit a Pull Request (PR) to **our** development branch
* The PR title should describe the change that has been made.
  * If you have **not** named your branch in way that links to the task's associated JIRA Issue, you **must** include the JIRA Issue ID in the PR's title. (e.g. `CORE-33 Documentation Updates`). *If your branch is linked to a JIRA issue, your associated Pull Request will be automatically linked*.
* The PR description should confirm what changes have been made, how you know they're correct (with references)
* It is expected that all submissions have associated test cases.
* Be prepared to answer any questions about your PR when it is reviewed for acceptance.

**If you are actively working on a large change** consider creating the PR early but prefixing it with [WIP] as this will prevent it from being accepted *but* let other people know you're working on that issue.
