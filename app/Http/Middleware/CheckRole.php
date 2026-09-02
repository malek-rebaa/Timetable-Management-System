<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolMembership;
use App\Multitenancy\CurrentTenant;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (in_array('SUPER_ADMIN', $roles, true)
            && ($user->role === 'SUPER_ADMIN' || $user->hasSystemRole('SUPER_ADMIN'))) {
            return $next($request);
        }

        $schoolId = app(CurrentTenant::class)->id();
        $membership = $schoolId === null ? null : SchoolMembership::query()
            ->where('school_id', $schoolId)
            ->where('user_id', $user->getKey())
            ->where('status', 'ACTIVE')
            ->first();

        $roleMap = [
            'ADMIN' => 'SCHOOL_ADMIN',
            'TEACHER' => 'TEACHER',
            'PARENT' => 'PARENT',
            'STUDENT' => 'STUDENT',
        ];

        foreach ($roles as $role) {
            if (($roleMap[$role] ?? null) === $membership?->role) {
                return $next($request);
            }

            // Compatibilité pendant la migration des comptes historiques.
            if ($user->role === $role && $membership !== null) {
                return $next($request);
            }
        }

        abort(403, 'Accès non autorisé.');
    }
}
