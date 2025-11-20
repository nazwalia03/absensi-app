<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        // super admin / admin full access
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        // Rules to view list: if manager sees subordinates list, check permission
        return $user->can('attendance.view') || $user->can('attendance.view_subordinates');
    }

    public function view(User $user, Attendance $attendance)
    {
        // admin already returned true by before()
        // allow if user owns the attendance record
        if ($attendance->user_id === $user->id) {
            return $user->can('attendance.view');
        }

        // a manager can view subordinate's attendance if they have permission and the attendance belongs to subordinate
        if ($user->can('attendance.view_subordinates') && $this->isSubordinate($user, $attendance->user)) {
            return true;
        }

        return false;
    }

    public function create(User $user)
    {
        return $user->can('attendance.create');
    }

    public function update(User $user, Attendance $attendance)
    {
        // only admin allowed (covered by before). non-admin false.
        return false;
    }

    public function delete(User $user, Attendance $attendance)
    {
        // only admin allowed
        return false;
    }

    // helper to determine subordinate relation (sesuaikan relasi)
    protected function isSubordinate(User $manager, User $possibleSubordinate)
    {
        // Asumsi: users table punya manager_id pointing to their manager's id
        // atau ada relasi custom seperti department/position hierarchy
        return $possibleSubordinate->manager_id === $manager->id;
    }
}
