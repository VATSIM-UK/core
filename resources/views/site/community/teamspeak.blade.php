@extends('layout')

@section('content')

    <div class="col-md-9">

        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fab fa-teamspeak"></i> &thinsp; TeamSpeak
                    </div>
                    <div class="panel-body">
                        <p>
                            On our pages you will find everything you ever needed to know about how to become a virtual
                            Air Traffic Controller or online pilot. However, that is not always enough. Sometimes it is
                            far quicker, far easier and much more fun to actually speak to someone direct The VATSIM UK
                            TeamSpeak server may just resolve that for you.
                        </p>

                        <p>
                            VATSIM UK hosts a TeamSpeak server which is available to members 24 hours a day, 7 days a
                            week. It is an essential tool for co-ordinating with adjacent controllers, mentoring
                            sessions, enjoying group flights together or simply having a chat. It is a great resource
                            which is well used by the division. And best of all, like all other VATSIM services, it&#39;s
                            completely free.
                        </p>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-book"></i> House Rules
                    </div>
                    <div class="panel-body">
                        <p>
                            At VATSIM UK we want to encourage a welcoming and inclusive community to ensure
                            every one of our members has the best experience possible. The VATSIM UK TeamSpeak
                            server is an official VATSIM communication medium, which means that all members are
                            subject to the&nbsp;
                        </p>

                        <ul>
                            <li>
                                <a href="http://vats.im/coc" rel="external nofollow" target="_blank">Code of
                                    Conduct</a>;
                            </li>
                            <li>
                                <a href="https://vatsim.net/docs/policy/code-of-regulations"
                                   rel="external nofollow">Code of Regulations</a>;
                            </li>
                            <li>
                                <a href="http://vats.im/ua" rel="external nofollow" target="_blank">User
                                    Agreement</a>;
                            </li>
                            <li>
                                <a href="{{ route('site.community.terms') }}" rel="">VATSIM UK Terms
                                    and Conditions</a>.
                            </li>
                        </ul>

                        <p>
                            Members who choose to break our TeamSpeak house rules can be kicked or even banned
                            from the server. If you wish to report a member breaking the rules, please approach
                            a member of staff (who is not currently in a coordination, mentoring or examination
                            room) or utilise the&nbsp;<a href="http://helpdesk.vatsim.uk/"
                                                         rel="external nofollow">helpdesk</a>.
                        </p>

                        <ol>
                            <li>
                                No swearing or vulgar language.&nbsp;VATSIM welcomes members aged 13+ and we
                                have a duty of care to protect our younger members from being exposed to bad
                                language. Additionally, some members feel uncomfortable at the use of bad
                                language, or may be offended by it.
                            </li>
                            <li>
                                No personal attacks, bullying or intimidation.&nbsp;We strive to encourage a
                                positive community atmosphere to ensure the best enjoyment is had in our hobby.
                                Any form of personal attacks, bullying or intimidation is not tolerated and will
                                be treated very seriously.
                            </li>
                            <li>
                                No posting links to inappropriate or illegal content.&nbsp;Our TeamSpeak server
                                has been kindly donated to us and we have an obligation to ensure it is used for
                                the right purposes. The server may not be used to share links to vulgar or
                                pornographic material, nor may it be used to link to any site which encourages
                                illegal activities.
                            </li>
                            <li>
                                No spamming.&nbsp;This rule is quite self-explanatory.
                            </li>
                            <li>
                                Respect other members.&nbsp;If you enter a room where there is controller
                                co-ordination going on, or a group flight is being enjoyed, please do not
                                interrupt the activity by playing music down the channel or by disturbing the
                                room occupants by any other means. Please respect the rules for different room
                                categories, as below.
                            </li>
                        </ol>

                        <p>
                            Please remember that you may only enter:
                        </p>

                        <ul>
                            <li>
                                a mentoring room - if you have the permission of the mentor (adjacent ATC may
                                enter for the purposes of coordination and then leave once complete);
                            </li>
                            <li>
                                a coordination room - if you are actively controlling a relevant facility for
                                the purposes of coordination;
                            </li>
                            <li>
                                an examination room - if you are actively controlling a relevant facility for
                                the purposes of coordination AND have the permission of the examiner.
                            </li>
                        </ul>

                        <p>
                            Should you have a question for a member of staff using these channels - you should
                            not disturb them (either by entering the channel or via PM/poke), please wait for
                            them to finish and change rooms first. &nbsp;If you&rsquo;re unable to locate the
                            answer to your question on either the website or the forum - you are always able to
                            <a href="https://helpdesk.vatsim.uk/" rel="external nofollow">raise a ticket using
                                the helpdesk</a>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-door-closed"></i> Room Guide
                    </div>
                    <div class="panel-body">
                        <p>
                            Although our TeamSpeak layout may seem a little confusing at first, it&#39;s actually laid
                            out in a very simple way. If you follow this simple guide to our different rooms, you will
                            familiarise yourself with the layout very quickly.
                        </p>

                        <h4>
                            Default Channel
                        </h4>

                        <p>
                            All members enter this room when first connecting to the server. Voice is disabled.
                        </p>

                        <h4>
                            Region-based Community Discussion Rooms
                        </h4>

                        <p>
                            These rooms are used by members of individual Regional Training Schemes for controlling,
                            flying, chatting and meetings. Although there are no entry restrictions to these rooms,
                            please consider the privacy of members, particularly during community-specific discussion or
                            meetings. Controller co-ordination is welcomed in these rooms, but please consider using the
                            co-ordination rooms if you wish to avoid being disturbed.
                        </p>

                        <h4>
                            Departure Lounge
                        </h4>

                        <p>
                            These rooms can be used for general chat, group flights, controlling sessions and
                            socialising. There are no entry restrictions to these rooms, but please respect the house
                            rules at all times.
                        </p>

                        <h4>
                            Coordination Rooms
                        </h4>

                        <p>
                            These rooms are to be used for coordination only. Only members controlling a relevant
                            position may enter. No chat or music.
                        </p>

                        <p>
                            <em>Please remember that you may only enter a&nbsp;coordination room - if you are actively
                                controlling a relevant facility for the purposes of coordination.</em>
                        </p>

                        <h4>
                            ATC Training Rooms
                        </h4>

                        <p>
                            These rooms are used exclusively for controller mentoring. No entry except with the
                            permission of the mentor. Adjacent ATC may enter to co-ordinate.
                        </p>

                        <p>
                            <em>Please remember that you may only enter a&nbsp;Training room - if you have the
                                permission of the mentor (adjacent ATC may enter for the purposes of coordination and
                                then leave once complete).</em>
                        </p>

                        <h4>
                            Pilot Training Rooms
                        </h4>

                        <p>
                            These rooms are used for pilot coordination and training purposes. There are no entry
                            restrictions to these rooms but please respect the privacy of occupants, particularly during
                            pilot training.
                        </p>

                        <p>
                            <em>Please remember that you may only enter a&nbsp;Training room - if you have the
                                permission of the mentor.</em>
                        </p>

                        <h4>
                            Examination Rooms
                        </h4>

                        <p>
                            For exams only. No entry without the express permission of the examiner.
                        </p>

                        <p>
                            <em>Please remember that you may only enter&nbsp;an examination room - if you are actively
                                controlling a relevant facility for the purposes of coordination AND have the permission
                                of the examiner.</em>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="col-md-3">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="fa fa-question"></i> &thinsp; Accessing TeamSpeak
            </div>
            <div class="panel-body">
                <h3>
                    Step 1 - Download&nbsp;the software
                </h3>

                <ol>
                    <li>
                        Go to the&nbsp;<a href="http://www.teamspeak.com/" rel="external nofollow" target="_blank">TeamSpeak
                            website</a>&nbsp;and download the latest client appropriate for your operating system.
                    </li>
                </ol>

                <h3>
                    Step 2 - Registering yourself
                </h3>

                <ol>
                    <li>
                        Visit&nbsp;<a href="{{ route('teamspeak.new') }}" rel="external nofollow">the TeamSpeak registration page.</a>
                    </li>
                    <li>
                        Follow the on-screen instructions.
                    </li>
                </ol>

                <p>
                    If you have any issues with registering on our server, please&nbsp;<a
                            href="http://helpdesk.vatsim.uk/" rel="external nofollow">submit a ticket</a>.
                </p>
            </div>
        </div>
    </div>
@stop
