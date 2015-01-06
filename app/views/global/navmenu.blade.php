<?/*php if ($_account->loaded()): ?>
                    <div id="menuContain" class="col-md-12 ui-corner-all">
                        <div class="menuRow container bg-warning">
                            <div class="row menuRow" id="menuRowAll">
                                <div class="col-md-2 ui-corner-all menuArea menuAreaAll">
                                    <div class="menuHeader menuHeaderAll col-md-12 ui-corner-top">My Account</div>
                                    <a href="<?= URL::site("sso/manage/display"); ?>">Dashboard</a>
                                    <a href="<?= URL::site("sso/auth/logout"); ?>">Logout</a>
                                </div>
                                <div class="col-md-2 ui-corner-all menuArea">
                                    <div class="menuHeader ui-corner-top">Training System</div>
                                    <a href="<?= URL::site("training/category/admin_list"); ?>">Manage Categories</a>
                                </div>
                                <div class="col-md-2 ui-corner-all menuArea">
                                    <div class="menuHeader ui-corner-top">Theory System</div>
                                    <a href="<?= URL::site("training/theory_test_admin/list"); ?>">Manage Tests</a>
                                    <a href="<?= URL::site("training/theory_question_admin/list"); ?>">Question Bank</a>
                                    <a href="<?= URL::site("training/theory_attempt_admin/history"); ?>" class="disabled">Attempt History</a>
                                </div>
                            </div>
                        </div>

                        <!--<div class="menuRow">
                            <div class="menuArea ui-corner-all">
                                <div class="menuHeader ui-corner-top">Members</div>
                                <a href="#">Dashboard</a>
                                <a href="#">My Account</a>
                                <a href="#">Membership</a>
                                <a href="#">Messages</a>
                                <a href="#">Signature</a>
                            </div>
                            <div class="menuArea ui-corner-all">
                                <div class="menuHeader ui-corner-top">Activities</div>
                                <a href="#">New Activity</a>
                                <a href="#">My Availability</a>
                                <a href="#">My Activities</a>
                                <a href="#">Calendar</a>
                                <a href="#">TeamSpeak</a>
                            </div>
                            <div class="menuArea ui-corner-all">
                                <div class="menuHeader ui-corner-top">Training</div>
                                <a href="#">My Status</a>
                                <a href="#">Available Courses</a>
                                <a href="#">Contact</a>
                                <a href="#" class="disabled">Students</a>
                            </div>
                            <div class="menuArea ui-corner-all">
                                <div class="menuHeader ui-corner-top">Theory</div>
                                <a href="#">Active Material</a>
                                <a href="#">All Material</a>
                                <a href="#">Exams</a>
                                <a href="#" class="disabled">Students</a>
                            </div>
                            <div class="menuArea ui-corner-all">
                                <div class="menuHeader ui-corner-top">Practical</div>
                                <a href="#">My Sessions</a>
                                <a href="#">History</a>
                                <a href="#">Exams</a>
                                <a href="#" class="disabled">Students</a>
                            </div>
                            <div class="clearer"></div>
                        </div>-->
                        <p align="right" style="font-size: 11px;">
                            <input type="checkbox" id="staticMenuToggle" value="1" />&nbsp;&nbsp;<span id="staticMenuText">Static?</span>
                        </p>
                    </div>

                    <div id="menuToggle" class="col-md-1 col-md-offset-5 ui-state-highlight ui-corner-bottom">
                        <div id="menuIcon" class="ui-icon ui-icon-carat-1-s"></div>
                    </div>
                </div>
            <?php endif; ?>