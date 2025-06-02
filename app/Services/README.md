// filepath: README.md
# Email Classification System

A Laravel-based application that uses AI to classify customer emails into predefined categories.

## Overview

This application reads customer emails and classifies them into one or more of the following tags:
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

## Features

- Accept emails via text input or file upload
- Classify emails using AI (OpenAI API) or mock classification
- View all emails with their assigned tags
- Filter emails by tag
- Export results to CSV
- Retry logic for API calls to ensure reliability

## Installation

1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env` and configure your database settings
4. Generate an application key: `php artisan key:generate`
5. Run database migrations: `php artisan migrate`
6. (Optional) Add your OpenAI API key to `.env` to use actual AI classification

## Usage

1. Start the server: `php artisan serve`
2. Visit the upload page to enter emails or upload a file
3. Process the emails to get classifications
4. View the results and filter by tags if needed
5. Export the results to CSV for further analysis

## Sample Input

```
I've been charged twice for my monthly subscription and need a refund ASAP.
Your app keeps crashing every time I try to upload a photo. This is frustrating!
I absolutely love your service! The customer support is amazing.
How do I change my password? I think someone may have accessed my account.
Can you add a dark mode feature to your mobile app?
```

## Sample Output

The application will classify each email with one or more tags:

1. "I've been charged twice..." → ["Billing Issue", "Refund Request"]
2. "Your app keeps crashing..." → ["Bug Report", "Complaint"]
3. "I absolutely love your service..." → ["Praise"]
4. "How do I change my password..." → ["Technical Support", "Security Concern"]
5. "Can you add a dark mode..." → ["Feature Request"]

## Assumptions and Limitations

- The mock classification is keyword-based and less accurate than a real AI model
- Processing is synchronous; large volumes of emails might be slow
- Limited input validation (basic checks for file type and size)
- No authentication system implemented in this demo