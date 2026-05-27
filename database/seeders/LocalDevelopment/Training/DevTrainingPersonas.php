<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training;

/**
 * Fixed fictional CIDs, emails, and local-only role permission sets for {@see DevTrainingPersonasSeeder}.
 *
 * {@see DevTrainingPersonas::STAFF_ROLE} and {@see DevTrainingPersonas::STUDENT_ROLE} do not map to production
 * roles — test access control against permission names, not these dev role names.
 *
 * @see database/seeders/LocalDevelopment/README.md#dev-roles-vs-production
 */
final class DevTrainingPersonas
{
    public const STAFF_ROLE = 'dev-training-staff';

    public const STUDENT_ROLE = 'dev-training-student';

    /**
     * @var list<string>
     */
    public const STAFF_PERMISSIONS = [
        'training.access',
        'training.exams.access',
        'training.exams.setup',
        'training.exams.conduct.twr',
        'training.exams.conduct.app',
        'training.mentors.view.atc',
        'training.mentors.manage.atc',
        'waiting-lists.access',
        'waiting-lists.view.*',
        'waiting-lists.add-accounts.*',
        'waiting-lists.update-accounts.*',
        'waiting-lists.remove-accounts.*',
        'waiting-lists.training-place.offer.*',
        'waiting-lists.training-place.view-offer.*',
        'waiting-lists.training-place.rescind-offer.*',
        'waiting-lists.add-flags.*',
        'waiting-lists.admin.*',
        'training-places.view.*',
        'training-places.manual-setup',
        'training-places.create-adhoc',
        'training-places.revoke.*',
        'training-places.restore.*',
    ];

    /**
     * @var list<string>
     */
    public const STUDENT_PERMISSIONS = [
        'training.access',
    ];

    public const STAFF_CID = 9000001;

    public const STUDENT_CID = 9000010;

    public const STUDENT_LOA_CID = 9000011;

    public const STUDENT_EXAMS_CID = 9000012;

    /** Sandbox / local mentor account for conduct-session testing. */
    public const MENTOR_CONDUCT_CID = 10000005;

    public const STAFF_EMAIL = 'dev-training-staff@example.test';

    public const STUDENT_EMAIL = 'dev-training-student@example.test';

    public const STUDENT_LOA_EMAIL = 'dev-training-student-loa@example.test';

    public const STUDENT_EXAMS_EMAIL = 'dev-training-student-exams@example.test';
}
