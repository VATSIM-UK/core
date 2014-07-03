<div>
    <div class="row">
        <div class="col-md-12">
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
    </div>
    <hr />
    <h2>Prerequesites of the course</h2>
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>
                            You must meet all of the pre-requisites below to enrol.
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($course->prerequisites as $pr): ?>
                        <tr>
                            <th>
                                x
                            </th>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($course->prerequisites) < 1): ?>
                        <tr>
                            <td>
                                This course has no prerequisites.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-offset-4 col-md-4" style="text-align: center;">
            <p><strong>Theory / Practical:</strong> <?= ($course->theory_status ? "YES" : "NO") ?> / <?= ($course->practical_status ? "YES" : "NO") ?></p>
            <p><strong>Status:</strong> Available</p>
            <form class="form-horizontal" method="GET" action="<?= URL::site("training/course/view/" . $course->id) ?>">
                <div class="form-group">
                    <div style="text-align: center;">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary" name="enrolCourse<?= $course->id ?>" value="0">Enrol</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>