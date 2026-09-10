<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            margin: 0;
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
        }
        .frame {
            padding: 48px;
            border: 10px solid #1f2937;
            height: 700px;
            box-sizing: border-box;
            text-align: center;
        }
        .org-name { font-size: 16px; letter-spacing: 2px; text-transform: uppercase; color: #6b7280; }
        .title { font-size: 34px; font-weight: bold; margin-top: 24px; }
        .presented { margin-top: 32px; font-size: 14px; color: #6b7280; }
        .student-name { font-size: 30px; font-weight: bold; margin-top: 8px; border-bottom: 2px solid #1f2937; display: inline-block; padding-bottom: 8px; }
        .course-line { margin-top: 24px; font-size: 16px; }
        .course-name { font-weight: bold; }
        .meta { margin-top: 48px; font-size: 12px; color: #6b7280; }
        .footer { margin-top: 40px; display: table; width: 100%; }
        .footer-cell { display: table-cell; width: 50%; text-align: center; font-size: 11px; color: #6b7280; }
        .qr { margin-top: 16px; }
    </style>
</head>
<body>
    <div class="frame">
        <div class="org-name">{{ $certificate->organization_name_snapshot }}</div>
        <div class="title">Certificate of Completion</div>
        <div class="presented">This certificate is proudly presented to</div>
        <div class="student-name">{{ $certificate->student_name_snapshot }}</div>
        <div class="course-line">
            for successfully completing the course<br>
            <span class="course-name">{{ $certificate->course_name_snapshot }}</span>
        </div>

        @if($certificate->teacher_name_snapshot)
            <div class="meta">Instructor: {{ $certificate->teacher_name_snapshot }}</div>
        @endif

        <div class="meta">
            Issued on {{ $certificate->issued_at->format('F j, Y') }}<br>
            Certificate No. {{ $certificate->certificate_number }}
        </div>

        <div class="qr">
            <img src="{{ $qrDataUri }}" width="90" height="90">
        </div>

        <div class="footer">
            <div class="footer-cell">Verification code: {{ $certificate->verification_code }}</div>
            <div class="footer-cell">{{ $verificationUrl }}</div>
        </div>
    </div>
</body>
</html>
