# UK Sector File Style Guide

## Contents
- [Introduction](#introduction)
- [Using this guide](#using)
- [Branch titles](#branch)
- [General rules](#general)
- [Issue titles](#issues)
- [Pull request titles](#prs)
- [Commit messages](#commits)

## Introduction <a name="introduction"></a>

This document is to serve as a 'style guide' for the UK Core repo, and should set out guidance for syntax and formatting in:

- Branch titles
- Issue titles
- Pull request titles
- Commit messages

It will also contain guidance on aviation/SF-specific terms and how these ought to be formatted.

## Using this guide <a name="using"></a>

This guide is intended to be guidelines, rather than firm and hard rules. Therefore, if it appears to be better style to break a 'rule', then this is encouraged. The guide is also intended to be one that can improve and be flexible, so edits are encouraged in light of changing best practice.

However, with that in mind, the guide is also intended to be enforced - reviewers, therefore, are well within their rights to request/make changes where submissions do not fit the Style Guide. This should be done where the Guide and the error in question are unambiguous. Where an error might be better described as one of pure taste, then it should be left to the discretion of the author.

Reviewers, as with all changes, may choose to make edits themselves if the change is minor or for the sake of expediency. However, if it is clear the author is not familiar with the Guide, it is better to request a change and link them to the Guide to enable them to learn.

## Branch titles <a name="branch"></a>
Branches that are related to the content of the core itself (99% of the issues) should simply be titled `issue-{issue number}`.
<br>✅ `issue-4427`
<br>❌ `delete-everything`

Where the issue is not a standard one, or no issue exists at all (such as for documentation relating to the SF or updates to the GH workflows) then a brief summary of the changes will suffice as a branch title. These will generally only by used by maintainers. 
This should be in all lowercase, with words separated by hyphens.
<br>✅`compiler-1.1.2`
<br>✅`readme-formatting`
<br>❌`LOTSOFCHANGELOGCHANGESthatmakepeoplehappy`

## General rules <a name="general"></a>
These rules apply to all the stated areas.

Writing should be written in [sentence-case](https://apastyle.apa.org/style-grammar-guidelines/capitalization/sentence-case):
<br>✅ Issue: `Pilots landing page incorrect spelling`
<br>❌ Issue: `Pilots Landing Page Incorrect Spelling`
<br>✅ PR: `Fixes #3673 - Correct pilots landing page spelling`
<br>❌ PR: `Fixes #3673 - Correct Pilots Landing Page Spelling`

Writing should also should omit (where it makes sense to do so):

- Generic words such as 'issue' or 'problem' (and replaced with a more helpful description)
- Articles such as 'the' and 'an'
- Auxiliary/superfluous verbs ('are', 'is', 'has')

<br>✅ Issue: `Manchester (EGCC) A1 hold wrong location`
<br>❌ Issue: `Manchester (EGCC) has an issue with the A1 hold`
<br>✅ PR: `Fixes #2134 - Correct Manchester (EGCC) A1 hold location`
<br>❌ PR: `Fixes #2134 - Correct the A1 hold location at Manchester (EGCC)`


## Issue titles <a name="issues"></a>
Where it makes sense to do so, issue titles should omit the main verb:
<br>✅ `New .Net documents links`
<br>❌ `Update .Net Links`


They should be a brief description of either the issue that requires solving, or what the changes would (aim to) do. It should be sufficiently precise without being too verbose (the issue main body should be used to elaborate). A verb does not need to be included unless it reads better.
<br>✅ `Spelling mistake on Discord Invite page`
<br>✅ `Broken link on Homepage`
<br>❌ `Problems with Gatwick (EGKK) runways`
<br>❌ `S23 -> S24 altitude agreement at KOKSY doesn't work, tried with various...`

## Pull request titles <a name="prs"></a>

Where pull requests relate to an issue (as 99% of PRs will), then they should begin with `Fixes #{issue number}`:
<br>✅`Fixes #1234 - Update .Net Links`
<br>❌`Update .Net Links`

Where they do not, this will of course be omitted.

Pull request titles take inspiration from commit message formatting - see [here](https://cbea.ms/git-commit/) for some guidance. The main rules are as follows:

- It should begin with an imperative (a command)
<br>✅`Fixes #4832 - Update Terms & Conditions`
<br>❌`Fixes #4832 - Terms & Conditions updated`
- It should be a description of what the PR (if merged), will do. This does not need to be terse, but should not be overly verbose:
<br>✅`Fixes #5785 - Corrected .net Document Links`
<br>❌`Fixes #5785 - Corrected Links to .net COC COR UAR SMP`

## Commit messages  <a name="commits"></a>

Individual commit messages are arguably less important than the PR title, as all PRs are 'squashed' into a single commit, but may be referenced when reviewing the PR and may be used when observing the changes made to a file over time. Generally, [this](https://cbea.ms/git-commit/)  guide (also linked above) will suffice. Additionally, while it is not related to style, there should be only a small amount of changes in each commit - this assists in reviewing it.
<br>✅`Added contributing guides`
<br>❌`Issue fixed`