<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Services\LlmService;
use Illuminate\Http\Request;

class EmailClassifierController extends Controller
{
    protected $llmService;
    protected $perPage = 15; // Number of emails per page
    
    public function __construct(LlmService $llmService)
    {
        $this->llmService = $llmService;
    }
    
    public function index()
    {
        // Get paginated emails instead of all
        $emails = Email::orderBy('created_at', 'desc')->paginate($this->perPage);
        $tags = $this->getAllUniqueTags();
        
        return view('emails.index', compact('emails', 'tags'));
    }
    
    public function upload()
    {
        return view('emails.upload');
    }
    
    public function process(Request $request)
    {
        $inputType = $request->input('input_type', 'text');
        $apiType = $request->input('api_type', 'default');
        $downloadResults = $request->has('download_results');
        $isAjaxRequest = $request->has('ajax');
        $highlightKeywords = $request->has('highlight');
        
        if ($inputType === 'text') {
            $request->validate([
                'emails' => 'required',
            ]);
            
            $emailTexts = explode("\n", $request->emails);
        } else { // file input
            $request->validate([
                'email_file' => 'required|file|mimes:txt,csv|max:1024',
            ]);
            
            $file = $request->file('email_file');
            $contents = file_get_contents($file->path());
            $emailTexts = explode("\n", $contents);
        }
        
        $emailTexts = array_filter($emailTexts, fn($text) => !empty(trim($text)));
        $processedEmails = [];
        $resultsForJson = [];
        $hasApiError = false;
        $apiErrorMessage = null;
        
        foreach ($emailTexts as $message) {
            $message = trim($message);
            
            // Skip empty messages
            if (empty($message)) {
                continue;
            }
            
            // Classify the email using the selected API type
            $classification = $this->llmService->classifyEmail($message, $apiType);
            
            // Store the email (skip if requested)
            if (!$request->has('skip_save')) {
                $email = Email::create([
                    'message' => $message,
                    'tags' => $classification['tags'] ?? ['Other'],
                    'processed' => true,
                ]);
                
                $processedEmails[] = $email;
            }
            
            // Prepare result data for JSON response
            $resultEntry = [
                'content' => $message,
                'tags' => $classification['tags'] ?? ['Other'],
            ];
            
            // Add highlighted content if requested
            if ($highlightKeywords) {
                $resultEntry['highlighted_content'] = $this->highlightKeywords($message, $resultEntry['tags']);
            }
            
            $resultsForJson[] = $resultEntry;
        }
        
        // Check for API errors
        if (session()->has('api_error')) {
            $hasApiError = true;
            $apiErrorMessage = session('api_error');
            session()->forget('api_error');
        }
        
        // Handle AJAX request
        if ($isAjaxRequest) {
            return response()->json([
                'results' => $resultsForJson,
                'highlight' => $highlightKeywords,
                'api_error' => $apiErrorMessage,
                'success' => !$hasApiError
            ]);
        }
        
        // Handle download request
        if ($downloadResults && count($processedEmails) > 0) {
            return $this->downloadResults($processedEmails);
        }
        
        // Handle regular form submission
        if ($hasApiError) {
            return redirect()->route('emails.index')
                ->with('warning', 'Emails processed with API errors. Some classifications may be inaccurate.')
                ->with('api_error', $apiErrorMessage);
        }
        
        return redirect()->route('emails.index')
            ->with('success', count($processedEmails) . ' emails processed successfully!');
    }
    
    /**
     * Highlight keywords in the email content based on tags
     */
    private function highlightKeywords($content, $tags)
    {
        $highlightedContent = $content;
        
        // Keywords mapping for each tag
        $keywordMap = [
            'Bug Report' => ['bug', 'crash', 'error', 'fix', 'broken', 'issue', 'problem'],
            'Billing Issue' => ['bill', 'charge', 'payment', 'invoice', 'cost', 'price', 'subscription', 'charged'],
            'Praise' => ['great', 'love', 'excellent', 'amazing', 'good', 'best', 'fantastic', 'wonderful', 'thank'],
            'Complaint' => ['bad', 'terrible', 'awful', 'disappointed', 'poor', 'worst', 'unhappy', 'angry', 'frustrat'],
            'Feature Request' => ['feature', 'add', 'implement', 'would like', 'should have', 'missing', 'improve'],
            'Technical Support' => ['help', 'support', 'assist', 'guidance', 'guide', 'how to', 'unable to', 'cannot'],
            'Sales Inquiry' => ['buy', 'purchase', 'price', 'cost', 'discount', 'offer', 'deal', 'package', 'plan'],
            'Security Concern' => ['security', 'hack', 'breach', 'password', 'access', 'unauthorized', 'privacy'],
            'Spam/Irrelevant' => ['offer', 'discount', 'free', 'win', 'congratulations', 'click', 'link'],
            'Refund Request' => ['refund', 'money back', 'return', 'cancel', 'credit'],
            'Shipping/Delivery' => ['shipping', 'delivery', 'arrive', 'package', 'tracking', 'shipment', 'order'],
            'Other' => []
        ];
        
        // For each tag, highlight relevant keywords
        foreach ($tags as $tag) {
            if (isset($keywordMap[$tag])) {
                foreach ($keywordMap[$tag] as $keyword) {
                    // Use word boundary to highlight whole words only
                    $pattern = '/\b(' . preg_quote($keyword, '/') . '[a-z]*)\b/i';
                    $replacement = '<mark class="highlight-word">$1</mark>';
                    $highlightedContent = preg_replace($pattern, $replacement, $highlightedContent);
                }
            }
        }
        
        return $highlightedContent;
    }
    
    public function exportCsv()
    {
        $emails = Email::all();
        return $this->generateCsv($emails, 'all_email_classifications.csv');
    }
    
    public function downloadResults($emails)
    {
        return $this->generateCsv($emails, 'processed_email_classifications.csv');
    }
    
    protected function generateCsv($emails, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($emails) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['ID', 'Message', 'Tags', 'Created At']);
            
            // Add data
            foreach ($emails as $email) {
                fputcsv($file, [
                    $email->id,
                    $email->message,
                    implode(', ', $email->tags ?: []),
                    $email->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    public function filter(Request $request)
    {
        $tag = $request->tag;
        
        if ($tag === 'all') {
            return redirect()->route('emails.index');
        }
        
        // Use pagination for filtered results
        $emails = Email::whereJsonContains('tags', $tag)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
            
        // Make sure pagination links include the tag parameter
        $emails->appends(['tag' => $tag]);
        
        $tags = $this->getAllUniqueTags();
        
        return view('emails.index', compact('emails', 'tags', 'tag'));
    }
    
    private function getAllUniqueTags()
    {
        $allTags = [];
        $emails = Email::all();
        
        foreach ($emails as $email) {
            if (!empty($email->tags)) {
                $allTags = array_merge($allTags, $email->tags);
            }
        }
        
        $allTags = array_unique($allTags);
        sort($allTags);
        
        return $allTags;
    }
}
