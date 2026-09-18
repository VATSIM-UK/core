<?php

namespace App\Models\Mship\Concerns;

use DB;

/**
 * Trait SyncsToMoodle.
 */
trait HasMoodleAccount
{
    public function syncUserToMoodle()
    {
        if (! $this->moodleEnabled()) {
            return false;
        }
        $moodleAccount = DB::table(config('services.moodle.database').'.mdl_user')
            ->where('username', $this->id)
            ->get(['id', 'username', 'auth', 'deleted', 'firstname', 'lastname', 'email', 'idnumber'])
            ->first();
        $this->syncToMoodle($moodleAccount);
    }

    /**
     * Sync the current account to Moodle.
     *
     * @param  mixed  $moodleAccount  Related Moodle account, false if doesn't exist
     */
    public function syncToMoodle($moodleAccount)
    {
        if (! $this->moodleEnabled()) {
            return false;
        }

        if (! $moodleAccount && $this->canLoginToMoodle()) {
            $this->createMoodleAccount();
        } elseif ($moodleAccount) {
            $this->updateMoodleAccount($this->canLoginToMoodle(), $moodleAccount);
        } else {
            // do nothing - user is not eligible for a Moodle account, nor do they have one already
        }
    }

    /**
     * Create a new account for the user in Moodle.
     *
     * @param  string  $email
     */
    protected function createMoodleAccount()
    {
        $accountID = DB::table(config('services.moodle.database').'.mdl_user')->insertGetId([
            'auth' => 'oauth2',
            'deleted' => 0,
            'confirmed' => 1,
            'policyagreed' => 1,
            'mnethostid' => 1,
            'username' => $this->id,
            'password' => md5(str_random(60)),
            'firstname' => $this->name_first,
            'lastname' => $this->name_last,
            'email' => $this->getMoodleEmail(),
        ]);

        $this->checkAndAddOAuthLink($accountID);
    }

    /**
     * Ensures the Moodle Account has a valid OAuth link to Core SSO.
     *
     * @param  int  $moodleAccountID
     */
    protected function checkAndAddOAuthLink($moodleAccountID)
    {
        // Ensure has SSO link
        $linkedLogin = DB::table(config('services.moodle.database').'.mdl_auth_oauth2_linked_login')
            ->where('username', $this->id)
            ->where('issuerid', config('services.moodle.oauth_issuer_id'))
            ->where('userid', $moodleAccountID)
            ->first();

        if (! $linkedLogin) {
            DB::table(config('services.moodle.database').'.mdl_auth_oauth2_linked_login')->insert([
                'timecreated' => time(),
                'timemodified' => time(),
                'usermodified' => 0,
                'userid' => $moodleAccountID,
                'issuerid' => config('services.moodle.oauth_issuer_id'),
                'username' => $this->id,
                'email' => $this->getMoodleEmail(),
                'confirmtoken' => '',
                'confirmtokenexpires' => 0,
            ]);
        }
    }

    /**
     * Update the user's existing Moodle account.
     *
     * @param  string  $email
     * @param  bool  $allowLogin
     * @param  mixed  $moodleAccount
     */
    protected function updateMoodleAccount($allowLogin, $moodleAccount)
    {
        $old = [
            'auth' => $moodleAccount->auth,
            'deleted' => $moodleAccount->deleted,
            'firstname' => $moodleAccount->firstname,
            'lastname' => $moodleAccount->lastname,
            'email' => $moodleAccount->email,
            'idnumber' => $moodleAccount->idnumber,
        ];

        $new = [
            'auth' => $allowLogin ? 'oauth2' : 'nologin',
            'deleted' => $allowLogin ? 0 : 1,
            'firstname' => $this->name_first,
            'lastname' => $this->name_last,
            'email' => $this->getMoodleEmail(),
            'idnumber' => (string) $this->id,
        ];

        $dirty = array_keys(array_diff_assoc($old, $new));
        if (! empty($dirty)) {
            DB::table(config('services.moodle.database').'.mdl_user')->where('username', (string) $this->id)->update($new);
        } else {
            // do nothing - account is up to date
        }

        $this->checkAndAddOAuthLink($moodleAccount->id);
    }

    /**
     * Get the email to use for the Moodle account.
     *
     * @return string
     */
    protected function getMoodleEmail()
    {
        return $this->getEmailForService(DB::table('oauth_clients')->where('name', 'Moodle')->first()->id);
    }

    /**
     * Check whether the user should be able to login to Moodle.
     *
     * @return bool
     */
    protected function canLoginToMoodle()
    {
        return $this->hasState('DIVISION')
            || $this->hasState('VISITING')
            || $this->hasState('TRANSFERRING');
    }

    /**
     * Whether the member has passed the given Moodle course, at any time.
     */
    public function hasPassedMoodleCourse(int|string $course): bool
    {
        $moodleAccountId = $this->moodleAccountId();

        if (! $moodleAccountId) {
            return false;
        }

        return $this->passedMoodleExamQuery($moodleAccountId)
            ->where(fn ($query) => $query->where('course.id', $course)->orWhere('course.idnumber', (string) $course))
            ->exists();
    }

    /**
     * Whether the member has passed the given Moodle exam, at any time.
     */
    public function hasPassedMoodleQuiz(int $quiz): bool
    {
        $moodleAccountId = $this->moodleAccountId();

        if (! $moodleAccountId) {
            return false;
        }

        return $this->passedMoodleExamQuery($moodleAccountId)
            ->where('quiz.id', $quiz)
            ->exists();
    }

    /**
     * Every exam the given Moodle user has passed, at any time.
     *
     * A pass is a quiz grade at or above that quiz's grade to pass (mdl_grade_items.gradepass).
     * Quizzes with no grade to pass configured can never be passed, so they are ignored.
     */
    private function passedMoodleExamQuery(int $moodleAccountId)
    {
        $moodleDatabase = config('services.moodle.database');

        return DB::table($moodleDatabase.'.mdl_quiz_grades as grades')
            ->join($moodleDatabase.'.mdl_quiz as quiz', 'quiz.id', '=', 'grades.quiz')
            ->join($moodleDatabase.'.mdl_course as course', 'course.id', '=', 'quiz.course')
            ->join($moodleDatabase.'.mdl_grade_items as grade_items', function ($join) {
                $join->on('grade_items.iteminstance', '=', 'quiz.id')
                    ->where('grade_items.itemtype', '=', 'mod')
                    ->where('grade_items.itemmodule', '=', 'quiz');
            })
            ->where('grades.userid', $moodleAccountId)
            ->where('grade_items.gradepass', '>', 0)
            ->whereColumn('grades.grade', '>=', 'grade_items.gradepass');
    }

    /**
     * The id of this member's Moodle account, if they have one.
     */
    private function moodleAccountId(): ?int
    {
        $moodleDatabase = config('services.moodle.database');

        if (! $moodleDatabase) {
            return null;
        }

        return DB::table($moodleDatabase.'.mdl_user')
            ->where('username', (string) $this->id)
            ->value('id');
    }

    private function moodleEnabled()
    {
        return config('services.moodle.database') && DB::table('oauth_clients')->where('name', 'Moodle')->first();
    }
}
