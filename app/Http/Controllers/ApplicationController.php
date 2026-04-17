<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Support\BcApplicationForm;
use App\Support\CollegeApplicationForm;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    public function show(Request $request)
    {
        $selectedCourseId = trim((string) $request->query('course', ''));
        $selectedCourseTitle = trim((string) $request->query('course_name', ''));
        $selectedProviderKey = trim((string) $request->query('provider', ''));
        $selectedProviderName = trim((string) $request->query('provider_name', ''));
        $templateKey = CollegeApplicationForm::resolveTemplateKey(
            $selectedProviderKey,
            trim((string) $request->query('template', ''))
        );

        abort_if($templateKey === null, 404, 'Application form not available for this college yet.');

        $formSchema = CollegeApplicationForm::schema($templateKey);

        return view('apply', [
            'formSchema' => $formSchema,
            'formData' => CollegeApplicationForm::formData(
                old() ?? [],
                $formSchema,
                $selectedCourseTitle,
                $selectedCourseId,
                $selectedProviderKey,
                $selectedProviderName
            ),
            'selectedCourseId' => $selectedCourseId,
            'selectedCourseTitle' => $selectedCourseTitle,
            'selectedProviderKey' => $selectedProviderKey,
            'selectedProviderName' => $selectedProviderName,
            'originalPdfUrl' => CollegeApplicationForm::originalPdfUrl($templateKey),
        ]);
    }

    /**
     * Store application form submission
     */
    public function store(Request $request)
    {
        $templateKey = CollegeApplicationForm::resolveTemplateKey(
            (string) $request->input('source_provider_key', ''),
            (string) $request->input('form_template', '')
        );

        abort_if($templateKey === null, 404, 'Application form not available for this college yet.');

        $formSchema = CollegeApplicationForm::schema($templateKey);
        $request->validate(CollegeApplicationForm::requestRules($formSchema));

        $formData = CollegeApplicationForm::formData(
            $request->all(),
            $formSchema,
            $request->input('source_course_title'),
            $request->input('source_course_id'),
            $request->input('source_provider_key'),
            $request->input('source_provider_name')
        );

        $courseId = CollegeApplicationForm::courseId($formData);
        $courseName = CollegeApplicationForm::courseName($formData);

        try {
            $application = Application::create([
                'user_id' => auth()->id(),
                'course_id' => $courseId,
                'course_name' => Str::limit($courseName, 250, ''),
                'form_data' => $formData,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            return redirect()->route('application.success', $application->id)
                ->with('success', 'Application submitted successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to submit application: '.$e->getMessage());
        }
    }

    /**
     * Show success page after submission
     */
    public function success(Application $application)
    {
        if ($application->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('application-success', compact('application'));
    }

    /**
     * View application as PDF
     */
    public function viewPdf(Application $application)
    {
        if ($application->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('application-pdf', array_merge(
            ['application' => $application],
            $this->applicationViewData($application)
        ));
    }

    /**
     * Admin: List all applications
     */
    public function adminList(Request $request)
    {
        $this->authorizeAdmin();

        $applications = Application::query()
            ->with('user')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.trim((string) $request->string('search')).'%';

                $query->where(function ($applicationQuery) use ($search) {
                    $applicationQuery
                        ->where('course_name', 'like', $search)
                        ->orWhere('course_id', 'like', $search)
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        });
                });
            })
            ->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.applications-list', compact('applications'));
    }

    /**
     * Admin: View single application
     */
    public function adminView(Application $application)
    {
        $this->authorizeAdmin();

        return view('admin.application-view', array_merge(
            ['application' => $application],
            $this->applicationViewData($application)
        ));
    }

    /**
     * Admin: Update application status
     */
    public function adminUpdateStatus(Request $request, Application $application)
    {
        $this->authorizeAdmin();

        if ($this->usesLegacyBentleyPayload($application)) {
            $validated = $request->validate(array_merge(
                [
                    'status' => 'required|in:draft,submitted,approved,rejected',
                    'notes' => 'nullable|string',
                ],
                Arr::only(BcApplicationForm::requestRules(true), BcApplicationForm::adminFieldNames())
            ));

            $mergedFormData = BcApplicationForm::formData(array_merge(
                $application->form_data ?? [],
                Arr::only($validated, BcApplicationForm::adminFieldNames())
            ));

            $application->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'form_data' => $mergedFormData,
                'course_id' => BcApplicationForm::courseIds($mergedFormData) ?: $application->course_id,
                'course_name' => Str::limit(BcApplicationForm::courseSummary($mergedFormData) ?: (string) $application->course_name, 250, ''),
                'reviewed_at' => now(),
            ]);

            return back()->with('success', 'Application status updated successfully!');
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,submitted,approved,rejected',
            'notes' => 'nullable|string',
            'office_received_date' => 'nullable|string|max:255',
            'office_approved_date' => 'nullable|string|max:255',
            'approved_by' => 'nullable|string|max:255',
            'office_signature' => 'nullable|string|max:255',
        ]);

        $templateKey = CollegeApplicationForm::resolveTemplateKey(
            Arr::get($application->form_data ?? [], 'source_provider_key'),
            Arr::get($application->form_data ?? [], 'form_template')
        );

        abort_if($templateKey === null, 404, 'Application form template is missing.');

        $formSchema = CollegeApplicationForm::schema($templateKey);
        $mergedFormData = CollegeApplicationForm::formData(
            array_merge($application->form_data ?? [], Arr::only($validated, CollegeApplicationForm::ADMIN_FIELDS)),
            $formSchema
        );

        $application->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'],
            'form_data' => $mergedFormData,
            'course_id' => CollegeApplicationForm::courseId($mergedFormData) ?: $application->course_id,
            'course_name' => Str::limit(CollegeApplicationForm::courseName($mergedFormData) ?: (string) $application->course_name, 250, ''),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Application status updated successfully!');
    }

    /**
     * Admin: Export application as PDF
     */
    public function adminExportPdf(Application $application)
    {
        $this->authorizeAdmin();

        return view('admin.application-pdf', array_merge(
            ['application' => $application],
            $this->applicationViewData($application)
        ));
    }

    /**
     * Admin: Delete application
     */
    public function adminDestroy(Application $application)
    {
        $this->authorizeAdmin();

        $application->delete();

        return redirect()->route('admin.applications.list')
            ->with('success', 'Application deleted successfully.');
    }

    private function authorizeAdmin()
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }
    }

    private function applicationViewData(Application $application): array
    {
        if ($this->usesLegacyBentleyPayload($application)) {
            return [
                'formSchema' => BcApplicationForm::schema(),
                'formData' => BcApplicationForm::formData($application->form_data ?? []),
                'originalPdfUrl' => asset('bc.pdf'),
            ];
        }

        $templateKey = CollegeApplicationForm::resolveTemplateKey(
            Arr::get($application->form_data ?? [], 'source_provider_key'),
            Arr::get($application->form_data ?? [], 'form_template')
        );

        abort_if($templateKey === null, 404, 'Application form template is missing.');

        $formSchema = CollegeApplicationForm::schema($templateKey);

        return [
            'formSchema' => $formSchema,
            'formData' => CollegeApplicationForm::formData($application->form_data ?? [], $formSchema),
            'originalPdfUrl' => CollegeApplicationForm::originalPdfUrl($templateKey),
        ];
    }

    private function usesLegacyBentleyPayload(Application $application): bool
    {
        $formData = $application->form_data ?? [];

        return is_array($formData)
            && ! isset($formData['form_template'])
            && array_key_exists('selected_courses', $formData);
    }
}
