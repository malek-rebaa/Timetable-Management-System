<?php

namespace App\Http\Middleware;

use App\Models\SchoolMembership;
use App\Models\School;
use App\Multitenancy\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(private readonly TenantDatabaseManager $tenantDatabase)
    {
    }

    /**
     * Le school_id est enregistré uniquement après avoir été validé dans school_user.
     * Ce middleware doit toujours être placé après le middleware auth.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $schoolId = $request->session()->get('active_school_id');

        if ($user === null) {
            abort(403, 'Aucune école active n’est sélectionnée.');
        }

        $membership = $schoolId === null ? null : SchoolMembership::query()
            ->with('school')
            ->where('school_id', $schoolId)
            ->where('user_id', $user->getKey())
            ->where('status', 'ACTIVE')
            ->first();

        $isOwner = $user->role === 'OWNER' || $user->hasSystemRole('OWNER');

        if ($isOwner) {
            $school = $schoolId === null ? null : School::query()
                ->whereKey($schoolId)
                ->where('status', 'ACTIVE')
                ->first();
        } else {
            $school = $membership?->school;
        }

        if ($school === null || $school->status !== 'ACTIVE') {
            $request->session()->forget('active_school_id');
            abort(403, 'Votre accès à cette école n’est plus actif.');
        }

        $request->session()->put('active_school_id', $school->getKey());
        $this->tenantDatabase->activate($school);

        try {
            return $next($request);
        } finally {
            // Important pour Horizon, queue:work et tout worker longue durée.
            $this->tenantDatabase->deactivate();
        }
    }
}
