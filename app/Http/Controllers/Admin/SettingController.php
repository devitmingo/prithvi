<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Faq;

class SettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function settingsIndex()
    {
        $settings = Setting::all()->pluck('value', 'key_name')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function settingsUpdate(Request $request)
    {
        $inputs = $request->except(['_token', '_method']);

        foreach ($inputs as $key => $value) {
            Setting::updateOrCreate(
                ['key_name' => $key],
                ['value' => $value, 'status' => 1]
            );
        }

        return back()->with('success', 'Application settings updated successfully.');
    }

    /**
     * Display FAQs.
     */
    public function faqsIndex()
    {
        $faqs = Faq::orderBy('sequence', 'asc')->get();
        return view('admin.faqs.index', compact('faqs'));
    }

    /**
     * Store new FAQ.
     */
    public function faqStore(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sequence' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ]);

        Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'sequence' => $request->sequence ?? (Faq::max('sequence') + 1),
            'status' => $request->status,
        ]);

        return back()->with('success', 'FAQ added successfully.');
    }

    /**
     * Update FAQ.
     */
    public function faqUpdate(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sequence' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ]);

        $faq->update($request->only('question', 'answer', 'sequence', 'status'));

        return back()->with('success', 'FAQ updated successfully.');
    }

    /**
     * Delete FAQ.
     */
    public function faqDestroy(Faq $faq)
    {
        $faq->delete();
        return back()->with('success', 'FAQ deleted successfully.');
    }

    /**
     * Toggle FAQ status.
     */
    public function faqToggle(Faq $faq)
    {
        $faq->status = $faq->status ? 0 : 1;
        $faq->save();
        return back()->with('success', 'FAQ status toggled.');
    }
}
