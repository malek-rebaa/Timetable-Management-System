<?php

namespace App\Http\Controllers;

use App\Helpers\PasswordGenerator;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class SchoolManagementController extends Controller
{
    public function index()
    {
        $schools = School::query()
            ->withCount([
                'memberships as active_members_count' => fn ($query) => $query->where('status', 'ACTIVE'),
            ])
            ->orderBy('name')
            ->get();

        return view('schools.index', compact('schools'));
    }

    /**
     * Crée l'école, sa base isolée, ses tables et son premier administrateur.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'admin_first_name' => ['required', 'string', 'max:255'],
            'admin_last_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('master.users', 'email')],
            'admin_phone' => ['nullable', 'string', 'max:20'],
            'primary_color' => ['required', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[A-Fa-f0-9]{6}$/'],
        ]);

        $school = School::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            // Nom provisoire, remplacé par un nom lisible dès que l'identifiant existe.
            'database_name' => 'edutime_school_pending_'.Str::lower(Str::random(12)),
            'status' => 'PENDING',
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'timezone' => 'Africa/Tunis',
            'locale' => 'fr',
        ]);

        $school->update(['database_name' => 'edutime_school_'.$school->getKey()]);
        $plainPassword = PasswordGenerator::generate();

        try {
            DB::connection('master')->transaction(function () use ($validated, $school, $plainPassword, $request): void {
                $superAdmin = User::create([
                    'first_name' => $validated['admin_first_name'],
                    'last_name' => $validated['admin_last_name'],
                    'email' => $validated['admin_email'],
                    'phone' => $validated['admin_phone'] ?: null,
                    'password' => Hash::make($plainPassword),
                    'role' => 'SUPER_ADMIN',
                    'is_active' => true,
                ]);

                $school->users()->attach($superAdmin->getKey(), [
                    'role' => 'SUPER_ADMIN',
                    'status' => 'ACTIVE',
                    'joined_at' => now(),
                ]);
            });

            $this->runProvisioning($school);
            $this->logProvisioning($school, $request);
        } catch (Throwable $exception) {
            $school->update(['status' => 'PENDING']);
            report($exception);

            return redirect()->route('schools.index')
                ->with('error', 'Le compte a été créé, mais le provisioning est en attente. Vérifiez MySQL/XAMPP puis cliquez sur « Relancer ».')
                ->with('generated_email', $validated['admin_email'])
                ->with('generated_password', $plainPassword);
        }

        // Le super admin peut immédiatement basculer vers l'espace de cette école.
        $request->session()->put('active_school_id', $school->getKey());

        return redirect()->route('schools.index')
            ->with('success', 'École créée, base isolée prête et administrateur ajouté.')
            ->with('generated_email', $validated['admin_email'])
            ->with('generated_password', $plainPassword);
    }

    public function provision(Request $request, School $school): RedirectResponse
    {
        abort_unless($school->status === 'PENDING', 422, 'Seule une école en attente peut être relancée.');

        try {
            $this->runProvisioning($school);
            $this->logProvisioning($school, $request);
        } catch (Throwable $exception) {
            $school->update(['status' => 'PENDING']);
            report($exception);

            return back()->with('error', 'Le provisioning est toujours en attente. Vérifiez que MySQL/XAMPP est démarré.');
        }

        return back()->with('success', "L'école {$school->name} est maintenant prête.");
    }

    public function activate(Request $request, School $school): RedirectResponse
    {
        abort_unless($school->status === 'ACTIVE', 422, 'Cette école n’est pas encore prête.');

        $request->session()->put('active_school_id', $school->getKey());

        return redirect()->route('dashboard')->with('success', "Espace de {$school->name} sélectionné.");
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'ecole';
        $slug = $base;
        $suffix = 2;

        while (School::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function runProvisioning(School $school): void
    {
        $exitCode = Artisan::call('school:provision', [
            'school' => $school->getKey(),
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            throw new \RuntimeException(trim(Artisan::output()) ?: 'La création de la base école a échoué.');
        }
    }

    private function logProvisioning(School $school, Request $request): void
    {
        AuditLog::create([
            'school_id' => $school->getKey(),
            'user_id' => $request->user()?->getKey(),
            'event' => 'school.provisioned',
            'subject_type' => School::class,
            'subject_id' => $school->getKey(),
            'metadata' => ['database_name' => $school->database_name],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
