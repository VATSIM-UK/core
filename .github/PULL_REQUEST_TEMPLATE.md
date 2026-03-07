Fixes #[issue_no]

## Summary of Changes

< Please provide a sensible summary of changes for this merge request, ensuring all changes are explained. >

## Type of Change

- [ ] Bug fix
- [ ] Feature
- [ ] Refactor (no intended functional change)
- [ ] Documentation
- [ ] Chore / maintenance

## Architecture and Behavior Checklist

- [ ] Controllers remain focused on transport concerns (HTTP/request-response orchestration)
- [ ] Business/domain logic is implemented in services
- [ ] Service binding/resolution patterns are consistent with project conventions
- [ ] API/response contracts are unchanged unless explicitly documented below
- [ ] Queue/integration behavior is unchanged unless explicitly documented below

### Intended Behavior Changes (if any)

< If this PR intentionally changes behavior, describe exactly what changed and why. Otherwise write "None". >

## Verification

### Automated Tests

- [ ] Unit tests added/updated where business logic changed
- [ ] Feature/integration tests added/updated where contracts changed
- [ ] Test suite run locally

### Manual Validation

< Describe manual checks performed and expected outcomes. >

## Queue and Integration Impact (if applicable)

- [ ] No queue/integration changes
- [ ] Job queue names / retries / backoff changed
- [ ] Rate-limiting or overlap protection changed
- [ ] Horizon configuration updated for any new queues

Details:
< Enter details or write "N/A". >

## Risk Assessment

< Describe key regression risks and how they were mitigated. >

## Screenshots (if necessary)

< Please provide screenshots if this will facilitate a faster review of these changes. >

## Related

- Related issue(s): #<issue_no> (if applicable)
