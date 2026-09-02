<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSchoolBrandingRequest;
use App\Models\AuditLog;
use App\Multitenancy\CurrentTenant;

class SchoolBrandingController extends Controller
{
    public function edit(CurrentTenant $currentTenant)
    {
        return view('branding.edit', [
            'school' => $currentTenant->requireSchool(),
        ]);
    }

    public function update(UpdateSchoolBrandingRequest $request, CurrentTenant $currentTenant)
    {
        $school = $currentTenant->requireSchool();
        $before = $school->only(['primary_color', 'secondary_color']);

        $school->update($request->validated());

        AuditLog::create([
            'school_id' => $school->getKey(),
            'user_id' => $request->user()->getKey(),
            'event' => 'school.branding_updated',
            'subject_type' => $school::class,
            'subject_id' => $school->getKey(),
            'metadata' => [
                'before' => $before,
                'after' => $school->only(['primary_color', 'secondary_color']),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Les couleurs de l’école ont été mises à jour.');
    }
}
