<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account & Data Deletion Request - Prithvi Buildcon</title>
    <meta name="description" content="Official Google Play Store Account and Personal Data Deletion Request portal for Prithvi Buildcon Mobile App.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #c59b27;
            --primary-dark: #9e7a16;
            --primary-light: #fbecc4;
            --danger: #ef4444;
            --danger-dark: #b91c1c;
            --danger-light: #fee2e2;
            --success: #10b981;
            --success-light: #d1fae5;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            background: linear-gradient(135deg, #0b0f19 0%, #111827 50%, #0f172a 100%);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        header {
            text-align: center;
            padding: 30px 0 20px;
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(197, 155, 39, 0.12);
            border: 1px solid rgba(197, 155, 39, 0.3);
            color: var(--primary);
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            text-transform: uppercase;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 16px;
            max-width: 650px;
            margin: 0 auto;
        }

        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 15px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #6ee7b7;
        }

        .success-box {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 24px;
            text-align: center;
        }

        .success-icon {
            width: 56px;
            height: 56px;
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 16px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .info-card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(51, 65, 85, 0.6);
            border-radius: 12px;
            padding: 18px;
        }

        .info-card h4 {
            color: #e2e8f0;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-card p {
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #cbd5e1;
        }

        .input-control {
            width: 100%;
            padding: 13px 16px;
            background: #0f172a;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(197, 155, 39, 0.2);
        }

        .helper-text {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 8px;
        }

        .radio-card {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .radio-card:hover {
            border-color: rgba(197, 155, 39, 0.5);
            background: rgba(15, 23, 42, 0.8);
        }

        .radio-card input[type="radio"] {
            margin-top: 4px;
            accent-color: var(--primary);
        }

        .checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
            font-size: 14px;
            color: #cbd5e1;
        }

        .checkbox-container input {
            margin-top: 4px;
            accent-color: var(--danger);
            width: 18px;
            height: 18px;
        }

        .btn-delete {
            width: 100%;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            border: none;
            padding: 14px 20px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
            transform: translateY(-1px);
        }

        .btn-delete:active {
            transform: translateY(0);
        }

        .url-example-box {
            background: #090d16;
            border: 1px dashed var(--border-color);
            border-radius: 10px;
            padding: 16px;
            margin-top: 15px;
            font-family: monospace;
            font-size: 13px;
            color: #93c5fd;
            overflow-x: auto;
            word-break: break-all;
        }

        footer {
            text-align: center;
            margin-top: auto;
            padding: 30px 0 10px;
            color: var(--text-muted);
            font-size: 13px;
            border-top: 1px solid rgba(51, 65, 85, 0.4);
        }

        footer a {
            color: var(--primary);
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            body {
                padding: 12px;
            }
            .card {
                padding: 20px;
            }
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-badge">
                <span>🛡️</span> Prithvi Buildcon Data Safety
            </div>
            <h1>Account & Data Deletion Portal</h1>
            <p class="subtitle">
                Official Google Play Store compliant account removal and personal data deletion service.
            </p>
        </header>

        @if (session('error'))
            <div class="alert alert-error">
                <span>⚠️</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if (!empty($successMessage))
            <div class="success-box">
                <div class="success-icon">✓</div>
                <h2 style="color: #34d399; font-size: 22px; margin-bottom: 10px;">
                    {{ $isRequest ? 'Deletion Request Submitted' : 'Account Successfully Deleted' }}
                </h2>
                <p style="color: #cbd5e1; font-size: 15px; margin-bottom: 16px;">
                    {{ $successMessage }}
                </p>

                @if (!empty($deletedUser))
                    <div style="background: rgba(15, 23, 42, 0.7); padding: 16px; border-radius: 10px; text-align: left; max-width: 400px; margin: 0 auto; font-size: 13px; color: #94a3b8;">
                        <div><strong style="color: #f8fafc;">User:</strong> {{ $deletedUser['name'] }}</div>
                        <div><strong style="color: #f8fafc;">User ID:</strong> {{ $deletedUser['user_id'] }}</div>
                        <div><strong style="color: #f8fafc;">Mobile:</strong> {{ $deletedUser['mobile'] }}</div>
                        <div><strong style="color: #f8fafc;">Processed At:</strong> {{ $deletedUser['deleted_at'] }}</div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Google Play Store Policy Information -->
        <div class="card">
            <h2 style="font-size: 20px; color: #f8fafc; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <span>📋</span> Google Play Data Deletion Policy Notice
            </h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
                In compliance with the Google Play Developer Data Safety Policy and DPDP regulations, users of the <strong>Prithvi Buildcon</strong> application have the right to permanently delete their account and associated data.
            </p>

            <div class="info-grid">
                <div class="info-card">
                    <h4><span>🗑️</span> Data That Will Be Deleted</h4>
                    <p>
                        • Personal profile (Name, Mobile, Email, Address)<br>
                        • Uploaded KYC files (Aadhar Card, PAN Card, Photos)<br>
                        • Attendance check-ins, location tags, & photos<br>
                        • Lead history, notes, & call log records<br>
                        • Active login tokens & device sessions
                    </p>
                </div>
                <div class="info-card">
                    <h4><span>⏱️</span> Deletion Options</h4>
                    <p>
                        <strong>1. Instant Permanent Deletion:</strong> Instantly wipes the user account and purges all media files permanently.<br><br>
                        <strong>2. 90-Day Grace Period:</strong> Submits a deletion request. Logging back in within 90 days automatically cancels the request and restores the account.
                    </p>
                </div>
            </div>
        </div>

        <!-- Deletion Form -->
        <div class="card">
            <h2 style="font-size: 20px; color: #f8fafc; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <span>⚙️</span> Delete Account Request Form
            </h2>

            <form action="{{ route('delete-account') }}" method="POST" onsubmit="return confirm('Are you sure you want to proceed with account deletion? This action cannot be undone.');">
                @csrf
                <div class="form-group">
                    <label for="identifier">User ID / Mobile Number / Email / Database ID <span style="color: var(--danger);">*</span></label>
                    <input 
                        type="text" 
                        name="id" 
                        id="identifier" 
                        class="input-control" 
                        placeholder="e.g. 9876543210 or AGT240812345 or user@example.com or 5" 
                        value="{{ old('id', request('id') ?? request('mobile') ?? request('user_id')) }}" 
                        required
                    >
                    <div class="helper-text">Enter your registered 10-digit mobile number, user code, or database ID.</div>
                </div>

                <div class="form-group">
                    <label for="password">Account Password (Optional / Verification)</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="input-control" 
                        placeholder="Enter your account password if known"
                    >
                    <div class="helper-text">Leave blank if requesting administrator removal or deleting by user ID.</div>
                </div>

                <div class="form-group">
                    <label>Select Deletion Type:</label>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="action" value="permanent" checked>
                            <div>
                                <strong style="color: #f8fafc; font-size: 14px;">Instant Permanent Deletion</strong>
                                <div style="color: var(--text-muted); font-size: 12px;">Permanently wipe user records, KYC images, and all related database entries immediately.</div>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="action" value="request">
                            <div>
                                <strong style="color: #f8fafc; font-size: 14px;">90-Day Deletion Request (Grace Period)</strong>
                                <div style="color: var(--text-muted); font-size: 12px;">Queue account for purging in 90 days. Logging in within 90 days restores account access.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="checkbox-container">
                    <input type="checkbox" id="confirm_check" required>
                    <label for="confirm_check" style="margin-bottom: 0; cursor: pointer;">
                        I understand that deleting my account will permanently erase my account profile, KYC identity documents, and attendance history according to the Google Play Store Data Policy.
                    </label>
                </div>

                <button type="submit" class="btn-delete" id="delete_btn">
                    <span>🗑️</span> Proceed to Delete Account
                </button>
            </form>

            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <div style="font-size: 14px; font-weight: 600; color: #e2e8f0; margin-bottom: 6px;">
                    🔗 Direct GET Method / URL API Support
                </div>
                <p style="font-size: 13px; color: var(--text-muted);">
                    You can also trigger deletion directly via GET URL request by passing the user ID or mobile number:
                </p>
                <div class="url-example-box">
                    GET {{ url('/delete-account') }}?id=<strong>{USER_ID_OR_MOBILE}</strong><br>
                    GET {{ url('/delete-account') }}/<strong>{USER_ID_OR_MOBILE}</strong>
                </div>
            </div>
        </div>

        <footer>
            <p>&copy; {{ date('Y') }} Prithvi Buildcon. All rights reserved.</p>
            <p style="margin-top: 5px;">
                <a href="{{ url('/') }}">Home</a> &bull; 
                <a href="{{ url('/delete-account') }}">Delete Account Policy</a> &bull; 
                <a href="mailto:support@prithvibuildcon.co">Contact Support</a>
            </p>
        </footer>
    </div>
</body>
</html>
