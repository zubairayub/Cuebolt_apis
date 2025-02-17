<?php

// app/Http/Controllers/Api/FaqController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserFaq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaqController extends Controller
{
    // Store a new FAQ
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        $faq = UserFaq::create([
            'user_id' => Auth::id(),
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
        ]);

        return response()->json(['message' => 'FAQ created successfully', 'data' => $faq], 201);
    }

    // Get FAQs for authenticated user
    public function index()
    {
        $faqs = UserFaq::where('user_id', Auth::id())->get();

        return response()->json(['data' => $faqs], 200);
    }

    // Show a single FAQ
    public function show($id)
    {
        $faq = UserFaq::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$faq) {
            return response()->json(['message' => 'FAQ not found'], 404);
        }

        return response()->json(['data' => $faq], 200);
    }

    // Update an FAQ
    public function update(Request $request, $id)
    {
            // Find the FAQ by ID
        $faq = UserFaq::findOrFail($id);
      
        // Validate the request
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:500',
        ]);

        // Update FAQ
        $faq->update($validated);

        return response()->json(['message' => 'FAQ updated successfully', 'faq' => $faq], 200);
    }

    // Delete an FAQ
    public function destroy($id)
    {
        $faq = UserFaq::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$faq) {
            return response()->json(['message' => 'FAQ not found'], 404);
        }

        $faq->delete();

        return response()->json(['message' => 'FAQ deleted successfully'], 200);
    }
}
