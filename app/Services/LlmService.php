<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class LlmService
{
    protected $apiType;
    protected $openaiApiKey;
    protected $openaiApiUrl;
    protected $geminiApiKey;
    protected $geminiApiUrl;
    protected $maxRetries = 3;
    
    public function __construct()
    {
        $this->openaiApiKey = env('OPENAI_API_KEY', '');
        $this->openaiApiUrl = env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
        $this->geminiApiKey = env('GEMINI_API_KEY', '');
        $this->geminiApiUrl = env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent');
    }

    
    
    public function classifyEmail($message, $apiType = 'default')
    {
        // dd(env('GEMINI_API_KEY', ''));
        $this->apiType = $apiType;
        
        // If apiType is default, determine based on available keys
        if ($apiType === 'default') {
            if (!empty($this->openaiApiKey)) {
                $this->apiType = 'openai';
            } elseif (!empty($this->geminiApiKey)) {
                $this->apiType = 'gemini';
            } else {
                $this->apiType = 'mock';
            }
        }
        
        // Use mock if explicitly requested or if no API keys are available
        if ($this->apiType === 'mock' || ($this->apiType === 'openai' && empty($this->openaiApiKey)) || 
            ($this->apiType === 'gemini' && empty($this->geminiApiKey))) {
            return $this->mockResponse($message);
        }
        
        // Choose API based on type
        if ($this->apiType === 'gemini') {
            return $this->callGeminiApi($message);
        } else {
            return $this->callOpenAiApi($message);
        }
    }
    
    protected function callOpenAiApi($message)
    {
        // Implement retry logic
        $attempts = 0;
        while ($attempts < $this->maxRetries) {
            try {
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ])->post($this->openaiApiUrl, [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->getPrompt($message)
                        ]
                    ],
                    'temperature' => 0.3,
                ]);
                
                if ($response->successful()) {
                    $content = $response->json()['choices'][0]['message']['content'];
                    return json_decode($content, true);
                }
                
                $attempts++;
                sleep(1); // Wait before retrying
            } catch (Exception $e) {
                $attempts++;
                if ($attempts >= $this->maxRetries) {
                    // Return fallback response after max retries
                    return ['tags' => ['Other']];
                }
                sleep(1); // Wait before retrying
            }
        }
        
        return ['tags' => ['Other']];
    }
    
    protected function callGeminiApi($message)
    {
        // Implement retry logic
        $attempts = 0;
        $lastError = null;
        
        while ($attempts < $this->maxRetries) {
            try {
                $response = Http::timeout(10)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->withQueryParameters([
                    'key' => $this->geminiApiKey,
                ])->post($this->geminiApiUrl, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $this->getGeminiPrompt($message)
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 800,
                ],
            ]);
            
            if ($response->successful()) {
                $content = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
                \Log::debug('Gemini raw response: ' . $content);
                
                // Look for tags in the response using a more comprehensive approach
                return $this->extractTagsFromContent($content);
            } else {
                $errorMsg = $response->json()['error']['message'] ?? 'Unknown API error';
                $lastError = "Gemini API error: " . $errorMsg;
                \Log::error($lastError);
            }
            
            $attempts++;
            sleep(1); // Wait before retrying
        } catch (Exception $e) {
            $lastError = "Gemini API exception: " . $e->getMessage();
            \Log::error($lastError);
            $attempts++;
            if ($attempts >= $this->maxRetries) {
                break;
            }
            sleep(1); // Wait before retrying
        }
    }
    
    // Store the error message in session for display
    if ($lastError) {
        session()->flash('api_error', $lastError);
    }
    
    return ['tags' => ['Other']];
}
    
    protected function getPrompt($message)
    {
        return "You are an AI assistant. Classify this customer message into one or more of:
Bug Report, Billing Issue, Praise, Complaint, Feature Request
Technical Support, Sales Inquiry, Security Concern, Spam/Irrelevant, Refund Request
Shipping/Delivery, Other

Message:
\"{$message}\"

Response format: {\"tags\": [\"Billing Issue\", \"Complaint\"]}";
    }
    
    protected function getGeminiPrompt($message)
    {
        return "TASK: Classify the customer email below into one or more of these categories:
- Bug Report
- Billing Issue
- Praise
- Complaint
- Feature Request
- Technical Support
- Sales Inquiry
- Security Concern
- Spam/Irrelevant
- Refund Request
- Shipping/Delivery
- Other

EMAIL: \"{$message}\"

OUTPUT INSTRUCTIONS:
1. Analyze the email content carefully
2. Select ALL appropriate categories that apply
3. Return ONLY a JSON object with this exact format: {\"tags\": [\"Category1\", \"Category2\"]}
4. Do not include any explanation, introduction, or additional text
5. Make sure the output is valid JSON that can be parsed
6. The response MUST start with '{' and end with '}'

For example, if an email complains about being charged twice and asks for a refund, the output should be exactly:
{\"tags\": [\"Billing Issue\", \"Refund Request\"]}";
    }
    
    protected function mockResponse($message)
    {
        // Simple keyword-based classification for mocking
        $tags = [];
        
        $messageLC = strtolower($message);
        
        if (str_contains($messageLC, 'bug') || str_contains($messageLC, 'not working') || str_contains($messageLC, 'broken') || str_contains($messageLC, 'crash')) {
            $tags[] = 'Bug Report';
        }
        
        if (str_contains($messageLC, 'bill') || str_contains($messageLC, 'charge') || str_contains($messageLC, 'payment') || str_contains($messageLC, 'subscription')) {
            $tags[] = 'Billing Issue';
        }
        
        if (str_contains($messageLC, 'love') || str_contains($messageLC, 'great') || str_contains($messageLC, 'thanks') || str_contains($messageLC, 'awesome') || str_contains($messageLC, 'excellent')) {
            $tags[] = 'Praise';
        }
        
        if (str_contains($messageLC, 'unhappy') || str_contains($messageLC, 'disappointed') || str_contains($messageLC, 'terrible') || str_contains($messageLC, 'bad') || str_contains($messageLC, 'awful')) {
            $tags[] = 'Complaint';
        }
        
        if (str_contains($messageLC, 'feature') || str_contains($messageLC, 'add') || str_contains($messageLC, 'would like') || str_contains($messageLC, 'please add') || str_contains($messageLC, 'suggest')) {
            $tags[] = 'Feature Request';
        }
        
        if (str_contains($messageLC, 'help') || str_contains($messageLC, 'how to') || str_contains($messageLC, 'can\'t figure out') || str_contains($messageLC, 'instructions')) {
            $tags[] = 'Technical Support';
        }
        
        if (str_contains($messageLC, 'buy') || str_contains($messageLC, 'purchase') || str_contains($messageLC, 'price') || str_contains($messageLC, 'cost') || str_contains($messageLC, 'quote')) {
            $tags[] = 'Sales Inquiry';
        }
        
        if (str_contains($messageLC, 'security') || str_contains($messageLC, 'hack') || str_contains($messageLC, 'breach') || str_contains($messageLC, 'password') || str_contains($messageLC, 'unauthorized')) {
            $tags[] = 'Security Concern';
        }
        
        if (str_contains($messageLC, 'refund') || str_contains($messageLC, 'money back') || str_contains($messageLC, 'return') || str_contains($messageLC, 'reimburse')) {
            $tags[] = 'Refund Request';
        }
        
        if (str_contains($messageLC, 'shipping') || str_contains($messageLC, 'delivery') || str_contains($messageLC, 'track') || str_contains($messageLC, 'package') || str_contains($messageLC, 'order status')) {
            $tags[] = 'Shipping/Delivery';
        }
        
        if (str_contains($messageLC, 'spam') || str_contains($messageLC, 'unsubscribe') || str_contains($messageLC, 'advertisement')) {
            $tags[] = 'Spam/Irrelevant';
        }
        
        if (empty($tags)) {
            $tags[] = 'Other';
        }
        
        return ['tags' => $tags];
    }
    
    // Add this method to the LlmService class
    protected function extractTagsFromContent($content)
    {
        // 1. Try to parse the entire response as JSON
        $result = json_decode($content, true);
        if ($result && isset($result['tags'])) {
            return $result;
        }
        
        // 2. Try to find JSON within the response using regex
        if (preg_match('/{.*?}/s', $content, $matches)) {
            $jsonContent = $matches[0];
            $result = json_decode($jsonContent, true);
            if ($result && isset($result['tags'])) {
                return $result;
            }
        }
        
        // 3. Look for a more complex JSON pattern
        if (preg_match('/{.*"tags".*?}/s', $content, $matches)) {
            $jsonContent = $matches[0];
            $result = json_decode($jsonContent, true);
            if ($result && isset($result['tags'])) {
                return $result;
            }
        }
        
        // 4. Try to extract just the array if it's formatted differently
        if (preg_match('/\[".*?"\]/s', $content, $matches)) {
            $arrayContent = $matches[0];
            $tags = json_decode($arrayContent, true);
            if (is_array($tags)) {
                return ['tags' => $tags];
            }
        }
        
        // 5. Extract tags by looking for category names in the content
        $possibleTags = [
            'Bug Report', 'Billing Issue', 'Praise', 'Complaint', 
            'Feature Request', 'Technical Support', 'Sales Inquiry', 
            'Security Concern', 'Spam/Irrelevant', 'Refund Request',
            'Shipping/Delivery'
        ];
        
        $foundTags = [];
        foreach ($possibleTags as $tag) {
            if (stripos($content, $tag) !== false) {
                $foundTags[] = $tag;
            }
        }
        
        if (!empty($foundTags)) {
            return ['tags' => $foundTags];
        }
        
        // 6. Last resort - log failure and return Other
        \Log::debug('Failed to parse AI response after all attempts');
        return ['tags' => ['Other']];
    }
}