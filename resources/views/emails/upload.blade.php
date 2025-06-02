@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            @php
                $openaiConfigured = !empty(env('OPENAI_API_KEY'));
                $geminiConfigured = !empty(env('GEMINI_API_KEY'));
                $anyApiConfigured = $openaiConfigured || $geminiConfigured;
            @endphp
            
            @if(!$anyApiConfigured)
                <div class="alert alert-warning mb-4">
                    <h5 class="alert-heading">No AI Services Configured</h5>
                    <p><i class="fas fa-exclamation-triangle"></i> No AI API keys found in your environment.</p>
                    <p>The system will use a <strong>basic keyword-based mock classifier</strong> which is less accurate than AI-powered classification.</p>
                    <p>For better results, configure API keys in your .env file.</p>
                </div>
            @endif

            <!-- API Error Alert (hidden by default) -->
            <div id="apiErrorAlert" class="alert alert-danger mb-4" style="display: none;">
                <h5 class="alert-heading">API Error</h5>
                <p id="apiErrorMessage"></p>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Upload Emails for Classification</h4>
                    <a href="{{ route('emails.index') }}" class="btn btn-secondary">Back to Emails</a>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="inputTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="text-tab" data-bs-toggle="tab" data-bs-target="#text-input" type="button" role="tab" aria-controls="text-input" aria-selected="true">Text Input</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-input" type="button" role="tab" aria-controls="file-input" aria-selected="false">File Upload</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="inputTabsContent">
                        <!-- Text Input Tab -->
                        <div class="tab-pane fade show active" id="text-input" role="tabpanel" aria-labelledby="text-tab">
                            <form id="textForm" action="{{ route('emails.process') }}" method="POST">
                                @csrf
                                <input type="hidden" name="input_type" value="text">
                                
                                <div class="mb-3">
                                    <label for="emails" class="form-label">Enter email messages (one per line):</label>
                                    <textarea 
                                        name="emails" 
                                        id="emails" 
                                        rows="10" 
                                        class="form-control @error('emails') is-invalid @enderror" 
                                        placeholder="I've been charged twice and I need my money back.
Your app keeps crashing when I try to upload photos.
I love your service, it's been amazing!
How do I change my subscription plan?"
                                        required
                                    >{{ old('emails') }}</textarea>
                                    @error('emails')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Select AI Service:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="api_type" id="apiMock" value="mock" {{ !$anyApiConfigured ? 'checked' : '' }}>
                                        <label class="form-check-label" for="apiMock">
                                            Mock Service (Keyword-based classification)
                                        </label>
                                    </div>
                                    
                                    @if($openaiConfigured)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="api_type" id="apiOpenAI" value="openai" {{ $openaiConfigured && !$geminiConfigured ? 'checked' : '' }}>
                                        <label class="form-check-label" for="apiOpenAI">
                                            OpenAI <span class="badge bg-success">Configured</span>
                                        </label>
                                    </div>
                                    @else
                                    <div class="form-check disabled">
                                        <input class="form-check-input" type="radio" name="api_type" id="apiOpenAI" value="openai" disabled>
                                        <label class="form-check-label text-muted" for="apiOpenAI">
                                            OpenAI <span class="badge bg-secondary">Not Configured</span>
                                        </label>
                                    </div>
                                    @endif
                                    
                                    @if($geminiConfigured)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="api_type" id="apiGemini" value="gemini" {{ $geminiConfigured && !$openaiConfigured ? 'checked' : '' }}>
                                        <label class="form-check-label" for="apiGemini">
                                            Google Gemini <span class="badge bg-success">Configured</span>
                                        </label>
                                    </div>
                                    @else
                                    <div class="form-check disabled">
                                        <input class="form-check-input" type="radio" name="api_type" id="apiGemini" value="gemini" disabled>
                                        <label class="form-check-label text-muted" for="apiGemini">
                                            Google Gemini <span class="badge bg-secondary">Not Configured</span>
                                        </label>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="downloadResults" name="download_results" value="1">
                                        <label class="form-check-label" for="downloadResults">
                                            Download results after processing
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3 d-flex">
                                    <button type="submit" class="btn btn-primary me-2">Process Emails</button>
                                    <button type="submit" class="btn btn-outline-primary" id="processHighlighted">Process with Highlighting</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- File Upload Tab -->
                        <div class="tab-pane fade" id="file-input" role="tabpanel" aria-labelledby="file-tab">
                            <form id="fileForm" action="{{ route('emails.process') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="input_type" value="file">
                                
                                <div class="mb-3">
                                    <label for="email_file" class="form-label">Upload a text file with email messages (one per line):</label>
                                    <input 
                                        type="file" 
                                        name="email_file" 
                                        id="email_file" 
                                        class="form-control @error('email_file') is-invalid @enderror" 
                                        accept=".txt,.csv"
                                        required>
                                    @error('email_file')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <div class="form-text">
                                        Upload a .txt or .csv file containing one email message per line.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Select AI Service:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="api_type" id="apiMockFile" value="mock" {{ !$anyApiConfigured ? 'checked' : '' }}>
                                        <label class="form-check-label" for="apiMockFile">
                                            Mock Service (Keyword-based classification)
                                        </label>
                                    </div>
                                    
                                    @if($openaiConfigured)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="api_type" id="apiOpenAIFile" value="openai" {{ $openaiConfigured && !$geminiConfigured ? 'checked' : '' }}>
                                        <label class="form-check-label" for="apiOpenAIFile">
                                            OpenAI <span class="badge bg-success">Configured</span>
                                        </label>
                                    </div>
                                    @else
                                    <div class="form-check disabled">
                                        <input class="form-check-input" type="radio" name="api_type" id="apiOpenAIFile" value="openai" disabled>
                                        <label class="form-check-label text-muted" for="apiOpenAIFile">
                                            OpenAI <span class="badge bg-secondary">Not Configured</span>
                                        </label>
                                    </div>
                                    @endif
                                    
                                    @if($geminiConfigured)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="api_type" id="apiGeminiFile" value="gemini" {{ $geminiConfigured && !$openaiConfigured ? 'checked' : '' }}>
                                        <label class="form-check-label" for="apiGeminiFile">
                                            Google Gemini <span class="badge bg-success">Configured</span>
                                        </label>
                                    </div>
                                    @else
                                    <div class="form-check disabled">
                                        <input class="form-check-input" type="radio" name="api_type" id="apiGeminiFile" value="gemini" disabled>
                                        <label class="form-check-label text-muted" for="apiGeminiFile">
                                            Google Gemini <span class="badge bg-secondary">Not Configured</span>
                                        </label>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="downloadResultsFile" name="download_results" value="1" checked>
                                        <label class="form-check-label" for="downloadResultsFile">
                                            Download results after processing
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3 d-flex">
                                    <button type="submit" class="btn btn-primary me-2">Process Emails</button>
                                    <button type="submit" class="btn btn-outline-primary" id="processFileHighlighted">Process with Highlighting</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Loading Indicator (hidden by default) -->
            <div id="loadingIndicator" class="text-center my-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Processing emails... This may take a moment.</p>
            </div>
            
            <!-- Results Section (hidden by default) -->
            <div id="resultsSection" class="card mt-4" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Classification Results</h5>
                    <button id="downloadResultsBtn" class="btn btn-sm btn-success">
                        <i class="fas fa-download"></i> Download Results
                    </button>
                </div>
                <div class="card-body">
                    <div id="resultsContainer">
                        <!-- Results will be inserted here via JavaScript -->
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Sample Email Messages</h5>
                </div>
                <div class="card-body">
                    <p>Here are some sample email messages you can use for testing:</p>
                    <ol>
                        <li>"I've been charged twice for my monthly subscription and need a refund ASAP."</li>
                        <li>"Your app keeps crashing every time I try to upload a photo. This is frustrating!"</li>
                        <li>"I absolutely love your service! The customer support is amazing."</li>
                        <li>"How do I change my password? I think someone may have accessed my account."</li>
                        <li>"Can you add a dark mode feature to your mobile app?"</li>
                        <li>"I ordered a product 2 weeks ago and still haven't received it. Where is my package?"</li>
                        <li>"What's the price for the premium plan for a team of 10 people?"</li>
                        <li>"I'm very disappointed with the quality of the product I received."</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textForm = document.getElementById('textForm');
    const fileForm = document.getElementById('fileForm');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const resultsSection = document.getElementById('resultsSection');
    const resultsContainer = document.getElementById('resultsContainer');
    const apiErrorAlert = document.getElementById('apiErrorAlert');
    const apiErrorMessage = document.getElementById('apiErrorMessage');
    const downloadResultsBtn = document.getElementById('downloadResultsBtn');
    const processHighlightedBtn = document.getElementById('processHighlighted');
    const processFileHighlightedBtn = document.getElementById('processFileHighlighted');
    
    let classificationResults = [];

    // Handle text form submission
    textForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check if this is a highlighted submission
        const isHighlighted = e.submitter === processHighlightedBtn;
        
        // Add highlighting flag if needed
        const formData = new FormData(textForm);
        if (isHighlighted) {
            formData.append('highlight', '1');
        }
        
        processForm(formData);
    });
    
    // Handle file form submission
    fileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check if this is a highlighted submission
        const isHighlighted = e.submitter === processFileHighlightedBtn;
        
        // Add highlighting flag if needed
        const formData = new FormData(fileForm);
        if (isHighlighted) {
            formData.append('highlight', '1');
        }
        
        processForm(formData);
    });
    
    // Process the form data
    function processForm(formData) {
        // Reset UI
        apiErrorAlert.style.display = 'none';
        resultsContainer.innerHTML = '';
        
        // Show loading indicator
        loadingIndicator.style.display = 'block';
        resultsSection.style.display = 'none';
        
        // Add AJAX flag
        formData.append('ajax', '1');
        
        fetch('{{ route('emails.process') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading indicator
            loadingIndicator.style.display = 'none';
            
            // Check for API errors
            if (data.api_error) {
                apiErrorMessage.textContent = data.api_error;
                apiErrorAlert.style.display = 'block';
            }
            
            // Store results for potential download
            classificationResults = data.results || [];
            
            // Display results
            displayResults(classificationResults, data.highlight);
            
            // Show results section
            resultsSection.style.display = 'block';
            
            // If download was requested, trigger download
            if (formData.get('download_results') === '1') {
                downloadResults();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingIndicator.style.display = 'none';
            apiErrorMessage.textContent = 'An error occurred while processing your request.';
            apiErrorAlert.style.display = 'block';
        });
    }
    
    // Display results in the container
    function displayResults(results, highlight) {
        if (!results || results.length === 0) {
            resultsContainer.innerHTML = '<div class="alert alert-info">No results to display.</div>';
            return;
        }
        
        const resultHtml = document.createElement('div');
        
        // Create table for results
        const table = document.createElement('table');
        table.className = 'table table-bordered table-hover';
        
        // Create header
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr class="table-primary">
                <th scope="col">#</th>
                <th scope="col">Email Content</th>
                <th scope="col">Classification</th>
            </tr>
        `;
        table.appendChild(thead);
        
        // Create body
        const tbody = document.createElement('tbody');
        
        results.forEach((result, index) => {
            const tr = document.createElement('tr');
            
            // Row number
            const tdNum = document.createElement('td');
            tdNum.textContent = index + 1;
            tr.appendChild(tdNum);
            
            // Email content
            const tdContent = document.createElement('td');
            
            // If highlighting is enabled, apply it
            if (highlight && result.highlighted_content) {
                tdContent.innerHTML = result.highlighted_content;
            } else {
                tdContent.textContent = result.content;
            }
            
            tr.appendChild(tdContent);
            
            // Tags/Classification
            const tdTags = document.createElement('td');
            if (result.tags && result.tags.length > 0) {
                result.tags.forEach(tag => {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-primary me-1 mb-1';
                    badge.textContent = tag;
                    tdTags.appendChild(badge);
                });
            } else {
                tdTags.textContent = 'No tags found';
            }
            tr.appendChild(tdTags);
            
            tbody.appendChild(tr);
        });
        
        table.appendChild(tbody);
        resultHtml.appendChild(table);
        
        // Update the container
        resultsContainer.innerHTML = '';
        resultsContainer.appendChild(resultHtml);
    }
    
    // Download results as CSV
    function downloadResults() {
        if (!classificationResults || classificationResults.length === 0) {
            return;
        }
        
        // Create CSV content
        let csvContent = "Email,Tags\n";
        
        classificationResults.forEach(result => {
            // Format the email content (escape quotes and commas)
            const formattedContent = `"${result.content.replace(/"/g, '""')}"`;
            
            // Format the tags
            const formattedTags = result.tags ? `"${result.tags.join(', ')}"` : '""';
            
            csvContent += `${formattedContent},${formattedTags}\n`;
        });
        
        // Create download link
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', 'email_classification_results.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Add event listener for download button
    downloadResultsBtn.addEventListener('click', downloadResults);
});
</script>
@endsection