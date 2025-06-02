@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Classified Emails</h4>
                    <div>
                        <a href="{{ route('emails.upload') }}" class="btn btn-primary me-2">Upload Emails</a>
                        <a href="{{ route('emails.export-csv') }}" class="btn btn-success">Export to CSV</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- API Configuration Status Alert -->
                    @php
                        $openaiConfigured = !empty(env('OPENAI_API_KEY'));
                        $geminiConfigured = !empty(env('GEMINI_API_KEY'));
                    @endphp
                    
                    <div class="alert {{ ($openaiConfigured || $geminiConfigured) ? 'alert-info' : 'alert-warning' }} mb-4">
                        <h5 class="alert-heading">AI Service Status</h5>
                        @if($openaiConfigured && $geminiConfigured)
                            <p><i class="fas fa-check-circle text-success"></i> Both OpenAI and Gemini API services are configured and available.</p>
                        @elseif($openaiConfigured)
                            <p><i class="fas fa-check-circle text-success"></i> OpenAI API service is configured and available.</p>
                            <p><i class="fas fa-times-circle text-warning"></i> Gemini API service is not configured.</p>
                        @elseif($geminiConfigured)
                            <p><i class="fas fa-times-circle text-warning"></i> OpenAI API service is not configured.</p>
                            <p><i class="fas fa-check-circle text-success"></i> Gemini API service is configured and available.</p>
                        @else
                            <p><i class="fas fa-exclamation-triangle text-warning"></i> <strong>No AI services configured.</strong> The system will use mock service for classification.</p>
                            <p>To enable AI-powered classification, add API keys in your .env file:</p>
                            <pre class="bg-light p-2">
# For OpenAI
OPENAI_API_KEY=your_openai_api_key_here

# For Gemini
GEMINI_API_KEY=your_gemini_api_key_here</pre>
                        @endif
                    </div>

                    @if(isset($tags) && count($tags) > 0)
                        <div class="mb-4">
                            <h5>Filter by tag:</h5>
                            <div class="d-flex flex-wrap">
                                <a href="{{ route('emails.filter', ['tag' => 'all']) }}" class="btn btn-sm btn-outline-primary me-2 mb-2 {{ !isset($tag) ? 'active' : '' }}">
                                    All
                                </a>
                                @foreach($tags as $filterTag)
                                    <a href="{{ route('emails.filter', ['tag' => $filterTag]) }}" class="btn btn-sm btn-outline-primary me-2 mb-2 {{ isset($tag) && $tag === $filterTag ? 'active' : '' }}">
                                        {{ $filterTag }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($emails->isEmpty())
                        <div class="alert alert-info">
                            <p>No emails have been processed yet.</p>
                            <a href="{{ route('emails.upload') }}" class="btn btn-primary mt-2">
                                Upload Emails {{ !$openaiConfigured && !$geminiConfigured ? '(Mock Service)' : '' }}
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="45%">Email Message</th>
                                        <th width="35%">Tags</th>
                                        <th width="15%">Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emails as $email)
                                        <tr>
                                            <td>{{ $email->id }}</td>
                                            <td class="email-message">{{ $email->message }}</td>
                                            <td>
                                                @if(!empty($email->tags))
                                                    @foreach($email->tags as $emailTag)
                                                        <span class="badge bg-primary tag-badge">{{ $emailTag }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No tags</span>
                                                @endif
                                            </td>
                                            <td>{{ $email->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination Links -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $emails->links() }}
                        </div>
                        
                        <!-- Display Pagination Info -->
                        <div class="text-center mt-2 text-muted">
                            @if($emails->total() > 0)
                                Showing {{ $emails->firstItem() }} to {{ $emails->lastItem() }} of {{ $emails->total() }} emails
                            @else
                                No emails found
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    /* Remove pagination styling as we're using Bootstrap's */
    
    .email-message {
        max-width: 500px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .tag-badge {
        margin-right: 0.25rem;
        margin-bottom: 0.25rem;
    }
</style>
@endsection