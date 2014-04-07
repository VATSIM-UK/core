<p>
    A list of all the current VATSIM-UK courses are shown below
</p>

<div>
    <?php foreach ($courses as $course): ?>
        <h3><?= $course ?></h3>
        <div class="row">
            <div class="col-md-9">
                <p>
                    <?= $course->overview ?>
                </p>
                <?php if ($course->theory_status): ?>
                    <p>
                        <strong>Theory:</strong><?= $course->theory_overview ?>
                    </p>
                <?php endif; ?>
                <?php if ($course->practical_status): ?>
                    <p>
                        <strong>Practical:</strong><?= $course->practical_overview ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="col-md-3">
                <p><strong>Theory / Practical:</strong> <?= ($course->theory_status ? "YES" : "NO") ?> / <?= ($course->practical_status ? "YES" : "NO") ?></p>
                <p><strong>Status:</strong> Available</p>
                <form class="form-horizontal" method="GET" action="<?= URL::site("training/course/display/".$course->id) ?>">
                    <div class="form-group">
                        <div style="text-align: center;">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-default" name="viewCourse<?=$course->id?>" value="1">View</button>
                                <!--<button type="submit" class="btn btn-primary" name="enrolCourse<?=$course->id?>" value="0">Enrol</button>-->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>