# Waiting Lists Introduction

Core manages Vatsim UK's waiting lists for pilot and ATC training. 
Given the significant and ongoing demand for training it makes sense to have something purpose built
to track members' position in the queue. 

At time of writing Core is used to manage waiting lists for ATC ratings (excluding OBS -> S1), Endorsements and Pilot Training.

## Waiting Lists

Individual waiting lists are not defined explicitly in Core. Instead they are managed in the database.

In addition to having a name and ID a waiting list:

- Belongs to either the ATC or Pilot training departments.
  This drives downstream functionality, including the way we show waiting list positions to members
- Has feature toggles
  These control if the waiting list checks cts exam statuses or atc hours
- CTS exam level
  For ATC training waiting lists store the id of the CTS exam used to check theory results.
- Can be marked as for home members only.
  This is used to prevent addition of non division members and to automate removals of members transferring out of the division.

Waiting lists also have a list of flags (also stored in the database) which are applied to members joining the list.
These are used by staff to manually *flag* members as having completed Moodle exams, met waiting list requirements etc.
Flags are a simple binary toggle.

Waiting lists are associated with staff who can manage the list.

## Waiting List Accounts

Members are added to a waiting list through the creation of a `WaitingListAccount` object attached to the waiting list
and the member. 

Waiting List order is determined by the creation date of the `WaitingListAccount`.

To remove a member to the `WaitingListAccount` is soft deleted. 

Waiting List Accounts have their flags synchronized from the parent waiting list and the state of these flags can then
be changed manually.

Waiting List Accounts have their flag status cached onto the WaitingListAccount model.

## Feature Run Down

- Add
  - Sync flags
  - Log
- Check eligibility
  - this appears somewhat mislabelled as it's actually doing the flag caching?
- Home member removal (processes on account state change)
- Manual removal (consider adding removal reason)
