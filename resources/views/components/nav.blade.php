<nav id="nav"
     class="fixed top-0 left-0 right-0 z-50 bg-nav-bg text-white"
     x-data="{ mobileMenuOpen: false }"
     @keydown.escape.window="mobileMenuOpen = false">
    @if (is_local_environment())
    <div class="h-5 bg-red-600 text-center text-sm font-medium text-white">
        You are in a <b>NON-PRODUCTION</b> environment
    </div>
    @endif
    @include('components.top-notification')

    {{-- Top section: Logo and Account Dropdown --}}
    <div class="border-b-4 border-nav-accent">
        <div class="mx-auto flex w-[90%] items-center justify-between py-2.5">
            <div>
                <a class="inline-block p-1.5" href="{{ route('site.home') }}">
                    <img src="{{ asset('images/branding/vatsimuk_whiteblue.png') }}" alt="VATSIM UK Logo" class="mt-1 max-h-9 h-full" />
                </a>
            </div>
            @if(currentUserHasAuth())
            <div class="relative flex items-center" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                <button type="button"
                        class="nav-trigger-top"
                        @click="open = !open"
                        aria-haspopup="true"
                        :aria-expanded="open">
                    <span>{{ Auth::user()->name }} ({{ Auth::user()->id }})</span>
                    <i class="hidden fa fa-sliders md:inline-block"></i>
                    <svg class="nav-chevron" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="nav-dropdown-right"
                     @click.outside="open = false">
                    <ul class="nav-dropdown-list">
                        <li><a href="{{ route('landing') }}" class="nav-dropdown-link">Dashboard</a></li>
                        <li class="nav-dropdown-divider"></li>
                        <li><a class="nav-dropdown-link-static">ATC Rating: <b>{{ Auth::user()->qualification_atc ?: 'OBS' }}</b></a></li>
                        <li><a class="nav-dropdown-link-static">Pilot Rating(s): <b>{{ (Auth::user()->toArray())['pilot_rating'] ?: 'P0' }}</b></a></li>
                        <li class="md:hidden"><a href="{{ route('mship.notification.list') }}" class="nav-dropdown-link">Notifications</a></li>
                        <li class="nav-dropdown-divider"></li>
                        <li><a href="{{ route('password.change') }}" class="nav-dropdown-link">Modify Password</a></li>
                        @if(!Auth::user()->mandatory_password)
                        <li><a href="{{ route('password.delete') }}" class="nav-dropdown-link">Disable Password</a></li>
                        @endif
                        <li class="nav-dropdown-divider"></li>
                        <li><a href="{{ route('mship.manage.email.add') }}" class="nav-dropdown-link">Add Email Address</a></li>
                        <li><a href="{{ route('mship.manage.email.assignments') }}" class="nav-dropdown-link">Email Assignments</a></li>
                        <li class="nav-dropdown-divider"></li>
                        <li>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST">@csrf</form>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-dropdown-link">Log Out</a>
                        </li>
                    </ul>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" title="Login" class="nav-trigger-top">
                Login <i class="fa fa-arrow-right"></i>
            </a>
            @endif
        </div>
    </div>

    {{-- Bottom section: Main navigation --}}
    <div class="w-full border-b border-gray-600 bg-nav-secondary">
        <div class="mx-auto w-[90%]">
            <div class="flex items-center justify-between">
                <button type="button"
                        class="nav-mobile-toggle"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        aria-label="Toggle menu"
                        :aria-expanded="mobileMenuOpen">
                    <span class="my-1.5 block h-0.5 w-7 rounded bg-white"></span>
                    <span class="my-1.5 block h-0.5 w-7 rounded bg-white"></span>
                    <span class="my-1.5 block h-0.5 w-7 rounded bg-white"></span>
                </button>
            </div>

            <div id="nav-inner"
                 class="block overflow-x-hidden bg-nav-secondary lg:overflow-visible max-lg:hidden"
                 :class="mobileMenuOpen && 'max-lg:!block'">
                <ul class="flex flex-col lg:flex-row lg:items-stretch">
                    {{-- Home --}}
                    <li class="group relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="nav-trigger" @click="open = !open">
                            Home <svg class="nav-chevron" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="nav-dropdown">
                            <ul class="nav-dropdown-list">
                                <li><a href="{{ route('site.staff') }}" class="nav-dropdown-link">Staff</a></li>
                                <li><a href="https://community.vatsim.uk/files/downloads/category/9-minutes-reports/" class="nav-dropdown-link">Meeting Minutes</a></li>
                                <li><a href="https://helpdesk.vatsim.uk/" class="nav-dropdown-link">Contact Us</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- Feedback --}}
                    <li class="group relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="nav-trigger" @click="open = !open">Feedback <svg class="nav-chevron" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="nav-dropdown">
                            <ul class="nav-dropdown-list">
                                <li><a href="{{ route('mship.feedback.new') }}" class="nav-dropdown-link">Submit Feedback</a></li>
                                <li><a href="{{ route('mship.feedback.view') }}" class="nav-dropdown-link">View My Feedback</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- Pilots --}}
                    <li class="group relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="nav-trigger" @click="open = !open">Pilots <svg class="nav-chevron" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="nav-dropdown">
                            <ul class="nav-dropdown-list">
                                <li class="nav-dropdown-header">Resources</li>
                                <li><a href="https://ukcp.vatsim.uk/request-a-stand" class="nav-dropdown-link">Request a stand</a></li>
                                <li><a href="https://chartfox.org/" class="nav-dropdown-link">Chartfox</a></li>
                                <li><a href="{{ route('site.airports') }}" class="nav-dropdown-link">Airfield Information</a></li>
                                <li><a href="{{ route('site.operations.sectors') }}" class="nav-dropdown-link">UK Area Sectors</a></li>
                                <li class="nav-dropdown-divider"></li>
                                <li class="nav-dropdown-header">Pilot Training</li>
                                <li><a href="{{ route('site.pilots.landing') }}" class="nav-dropdown-link">Welcome</a></li>
                                <li><a href="{{ route('site.pilots.ratings') }}" class="nav-dropdown-link">Rating Training</a></li>
                                <li><a href="{{ route('site.pilots.tfp') }}" class="nav-dropdown-link">The Flying Programme</a></li>
                                <li><a href="https://moodle.vatsim.uk/course/index.php?categoryid=29" class="nav-dropdown-link">eLearning</a></li>
                                <li><a href="{{ route('site.pilots.mentor') }}" class="nav-dropdown-link">Become a Mentor</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- Controllers --}}
                    <li class="group relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="nav-trigger" @click="open = !open">Controllers <svg class="nav-chevron" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="nav-dropdown">
                            <ul class="nav-dropdown-list">
                                <li><a href="{{ route('site.atc.newController') }}" class="nav-dropdown-link">Become a controller</a></li>
                                <li><a href="{{ route('site.roster.index') }}" class="nav-dropdown-link">Controller Roster</a></li>
                                <li class="nav-dropdown-divider"></li>
                                <li class="nav-dropdown-header">Operations</li>
                                <li><a href="https://docs.vatsim.uk/" class="nav-dropdown-link">ATC Documentation</a></li>
                                <li><a href="https://docs.vatsim.uk/Briefing/" class="nav-dropdown-link">ATC Permanent Instructions</a></li>
                                <li><a href="https://docs.vatsim.uk/General/" class="nav-dropdown-link">ATC Software</a></li>
                                <li class="nav-dropdown-divider"></li>
                                <li class="nav-dropdown-header">ATC Training</li>
                                <li><a href="{{ route('site.atc.landing') }}" class="nav-dropdown-link">Welcome</a></li>
                                <li><a href="{{ route('site.atc.newController') }}" class="nav-dropdown-link">New Controller</a></li>
                                <li><a href="{{ route('site.atc.endorsements') }}" class="nav-dropdown-link">Rating Endorsements</a></li>
                                <li><a href="https://moodle.vatsim.uk/course/index.php?categoryid=3" class="nav-dropdown-link">eLearning</a></li>
                                <li><a href="{{ route('site.atc.mentor') }}" class="nav-dropdown-link">Become a Mentor</a></li>
                            </ul>
                            <ul class="nav-dropdown-list border-t border-white/20 mt-2">
                                <li class="nav-dropdown-header">Endorsements</li>
                                <li><a href="{{ route('controllers.endorsements.heathrow_ground_s1') }}" class="nav-dropdown-link">Heathrow Ground (S1)</a></li>
                                <li><a href="{{ route('site.atc.heathrow') }}" class="nav-dropdown-link">Heathrow</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- Membership --}}
                    <li class="group relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="nav-trigger" @click="open = !open">Membership <svg class="nav-chevron" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="nav-dropdown">
                            <ul class="nav-dropdown-list">
                                <li><a href="https://helpdesk.vatsim.uk/" class="nav-dropdown-link">Contact Us</a></li>
                                <li><a href="{{ route('networkdata.dashboard') }}" class="nav-dropdown-link">My Statistics</a></li>
                                <li class="nav-dropdown-divider"></li>
                                <li class="nav-dropdown-header">Waiting Lists</li>
                                <li><a href="{{ route('mship.waiting-lists.index') }}" class="nav-dropdown-link">My Waiting Lists</a></li>
                                <li class="nav-dropdown-divider"></li>
                                <li class="nav-dropdown-header">Visit / Transfer</li>
                                <li><a href="{{ route('site.community.vt-guide') }}" class="nav-dropdown-link">Guide</a></li>
                                <li><a href="{{ route('visiting.landing') }}" class="nav-dropdown-link">Dashboard</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- ATC Training Process --}}
                    <li class="group relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="nav-trigger" @click="open = !open">ATC Training Process <svg class="nav-chevron" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="nav-dropdown">
                            <ul class="nav-dropdown-list">
                                <li><a href="{{ route('site.policy.training.s1-syllabus') }}" class="nav-dropdown-link">S1 Syllabus</a></li>
                                <li><a href="{{ route('site.policy.training.s2-syllabus') }}" class="nav-dropdown-link">S2 Syllabus</a></li>
                                <li><a href="{{ route('site.policy.training.s3-syllabus') }}" class="nav-dropdown-link">S3 Syllabus</a></li>
                                <li><a href="{{ route('site.policy.training.c1-syllabus') }}" class="nav-dropdown-link">C1 Syllabus</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- Policy --}}
                    <li class="group relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="nav-trigger" @click="open = !open">Policy <svg class="nav-chevron" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="nav-dropdown">
                            <ul class="nav-dropdown-list">
                                <li class="nav-dropdown-header">Policies</li>
                                <li><a href="{{ route('site.policy.division') }}" class="nav-dropdown-link">Division Policy</a></li>
                                <li><a href="{{ route('site.policy.atc-training') }}" class="nav-dropdown-link">ATC Training Policy</a></li>
                                <li><a href="{{ route('site.policy.visiting-and-transferring') }}" class="nav-dropdown-link">Visiting & Transferring Policy</a></li>
                                <li class="nav-dropdown-divider"></li>
                                <li class="nav-dropdown-header">Protecting You</li>
                                <li><a href="{{ route('site.policy.terms') }}" class="nav-dropdown-link">Terms & Conditions</a></li>
                                <li><a href="{{ route('site.policy.privacy') }}" class="nav-dropdown-link">Privacy Policy</a></li>
                                <li><a href="{{ route('site.policy.data-protection') }}" class="nav-dropdown-link">Data Protection Policy</a></li>
                                <li class="nav-dropdown-divider"></li>
                                <li class="nav-dropdown-header">Guidelines</li>
                                <li><a href="{{ route('site.policy.branding') }}" class="nav-dropdown-link">Branding</a></li>
                            </ul>
                        </div>
                    </li>

                    {{-- Our Services --}}
                    <li class="group relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="nav-trigger" @click="open = !open">Our Services <svg class="nav-chevron" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="nav-dropdown">
                            <ul class="nav-dropdown-list">
                                <li class="nav-dropdown-header">Communications</li>
                                <li><a href="{{ route('site.community.teamspeak') }}" class="nav-dropdown-link">TeamSpeak</a></li>
                                <li><a href="{{ route('mship.manage.dashboard') }}" class="nav-dropdown-link">Discord</a></li>
                                <li class="nav-dropdown-divider"></li>
                                <li><a href="https://cts.vatsim.uk/" class="nav-dropdown-link">Training System</a></li>
                                <li><a href="https://docs.vatsim.uk/" class="nav-dropdown-link">Docs Site</a></li>
                                <li><a href="https://moodle.vatsim.uk/" class="nav-dropdown-link">eLearning</a></li>
                                <li><a href="https://helpdesk.vatsim.uk/" class="nav-dropdown-link">Helpdesk</a></li>
                                <li><a href="https://events.vatsim.uk/" class="nav-dropdown-link">Event Bookings</a></li>
                                <li><a href="https://github.com/VATSIM-UK" class="nav-dropdown-link">Github</a></li>
                                <li><a href="https://community.vatsim.uk/" class="nav-dropdown-link">Forum</a></li>
                                <li><a href="https://moodle.vatsim.uk/" class="nav-dropdown-link">Moodle</a></li>
                            </ul>
                        </div>
                    </li>

                    @if(currentUserHasAuth())
                    <li class="ml-auto flex items-center gap-0 border-t border-gray-600 lg:border-t-0">
                        <a href="{{ route('mship.notification.list') }}" title="Notifications" class="nav-icon-link lg:block {{ Auth::user()->has_unread_notifications ? 'text-red-400' : '' }}">
                            <i class="fa fa-bell"></i>
                        </a>
                        @if(Auth::user()->can('training.access'))
                        <a href="{{ route('filament.training.pages.dashboard') }}" title="Training Dashboard" class="nav-icon-link">
                            <i class="fa fa-graduation-cap"></i>
                        </a>
                        @endif
                        @if(Auth::user()->can('admin.access'))
                        <a href="{{ route('filament.app.pages.dashboard') }}" title="Admin Dashboard" class="nav-icon-link">
                            <i class="fa fa-briefcase"></i>
                        </a>
                        @endif
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</nav>
