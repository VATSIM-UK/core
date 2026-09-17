# Public API

All routes are prefixed `/api`, unauthenticated, and rate-limited to 60 req/min unless noted otherwise.

## `GET /api/bookings`

Bookings and events for a single day, core + CTS merged and de-duplicated. Also rate-limited to 1 request per 10s per IP.

**Query params**: `date` (`YYYY-MM-DD`, optional, defaults to today; max 30 days in the past)

**Response**:

```json
{
  "bookings": [
    {
      "id": 123,
      "source": "core",
      "cts_booking_id": null,
      "position_id": 1,
      "position": "EGKK_APP",
      "from": "2026-01-01T10:00:00+00:00",
      "to": "2026-01-01T12:00:00+00:00",
      "type": "BK",
      "owner": 456
    }
  ],
  "date": "2026-01-01",
  "count": 1,
  "next_page_url": "...",
  "previous_page_url": "..."
}
```

- `owner`: CID, or `null`. Member for standard bookings; mentor/examiner for mentoring/exam bookings; always `null` for events.
- `type`: `BK`, `EX`, `ME`, `GS`, or `EV`.

**Errors**: `400` invalid `date` format or too far in the past. `429` rate limited.

## `GET /api/validations`

Members qualified to control a position.

**Query params**: `position` (callsign, required)

**Response**: `{"status": {"position": "..."}, "validated_members": [{"id": 1}, ...]}`

**Errors**: `400` missing `position`. `404` unknown position.

## `GET /api/metar/{airportIcao}`

Raw METAR text for an airport (proxied from VATSIM, cached 5 min), served as `text/plain`. Returns the string `METAR UNAVAILABLE` if the upstream fetch fails. Not JSON.

## `GET /api/trackaudio-latest`

Redirects (302) to the latest TrackAudio GitHub release, or to the releases page if the version check fails.
