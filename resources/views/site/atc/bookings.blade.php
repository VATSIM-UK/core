@extends('layout')

@section('content')
    <div class="row">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-calendar"></i> &thinsp; Bookings
                </div>
                <div class="panel-body">
                    <p>
                        Below are outlined some of the additional rules regarding Controller Bookings. These rules are
                        not designed to prevent you from controlling, but instead to give everyone the opportunity to
                        log on and control. Above all, please remain considerate of others when making controller
                        bookings and ensure that you arrive and control for any bookings you make.
                    </p>

                    <h2>
                        General
                    </h2>

                    <ul>
                        <li>
                            A booking on the CT System shall reserve a position on the VATSIM network during the time
                            period specified. Bookings may synchronise to external sources, however must appear on the
                            CT System in order to be considered valid;
                        </li>
                        <li>
                            A controller may only make a booking if they are currently allowed to control that position
                            and will be allowed to do so at the time booked. A member may make a maximum of 6 advance
                            bookings up to 90 days in advance at any one time;
                        </li>
                        <li>
                            Members may not book less than 2 hours in advance unless they are currently controlling that
                            position and are booking to extend their current controlling session.
                        </li>
                        <li>
                            Booking shall not be excessive in duration;
                        </li>
                        <li>
                            A controller must vacate a position if a member with a valid booking arrives to take over;
                        </li>
                        <li>
                            Members should honour their bookings (including allocated controlling during events) and not
                            book positions they are unlikely to be available for.
                        </li>
                    </ul>

                    <h2>
                        Validity
                    </h2>

                    <ul>
                        <li>
                            A booking ceases to be valid if the member is more than 15 minutes late logging onto the
                            position or voluntarily vacates the position unless specified otherwise below;
                        </li>
                        <li>
                            If a student does not arrive in adequate time for their mentoring session, the mentor may
                            choose to assume the booked time for their own controlling, or nullify the booking.
                            Mentoring session bookings ceases to be valid if the student or mentor has not logged in to
                            control within 30 minutes of the session start time;
                        </li>
                        <li>
                            Bookings&nbsp;may&nbsp;be overridden by mentoring sessions. This includes bookings for
                            underlying splits, for example a GND booking for a TWR mentoring session;
                        </li>
                        <li>
                            Active exam or endorsement bookings remain valid until completed. Such bookings override
                            other controller bookings, including when the session continues beyond the booked time;
                        </li>
                    </ul>

                    <h2>
                        Events
                    </h2>

                    <ul>
                        <li>
                            When approved by the Marketing Director, controllers may be allocated positions to control.
                            Allocated controlling shall take priority over controller bookings.
                        </li>
                    </ul>

                    <h2>
                        Split Positions
                    </h2>

                    <ul>
                        <li>
                            When a position split is opened, the first controller to book may choose the split position
                            they will control, regardless of the positions booked on the CT System, with the exception
                            of:
                            <ul>
                                <li>
                                    Controllers wishing to bandbox two or more primary area sectors should book a
                                    single sector and then log on to the bandboxed position. Other members may then
                                    log on to or book the other Primary or Secondary sectors without the need for 
                                    obtaining the first controller&rsquo;s preference;
                                </li>
                                <li>
                                    If a controller does choose to book a position bandboxing the Primary area
                                    sectors, other controllers may choose to log on to or book a contained Primary or
                                    Secondary sector without obtaining the first controller&rsquo;s preference. This 
                                    action must leave the bandbox controller with a minimum of one Primary or Secondary 
                                    sector to control.
                                </li>
                            </ul>
                        </li>
                    </ul>
                    
                    <p>
                        Primary and Secondary Sectors are defined on the <a "{{ route('site.operations.sectors') }}" rel="">Area Sectors</a> page.
                    </p>
                    
                </div>
            </div>
        </div>

    </div>

@stop
