<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            background-color: #ffffff;
            color: #2e7d32;
            text-align: center;
            padding: 40px;
        }

        .certificate {
            border: 8px solid #81c784;
            padding: 60px;
            border-radius: 10px;
            background-color: #f1f8e9;
            width: 90%;
            margin: auto;
        }

        .certificate h1 {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .certificate h2 {
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 30px;
        }

        .certificate p {
            font-size: 18px;
            line-height: 1.6;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #1b5e20;
            margin-top: 40px;
        }

        .date {
            margin-top: 30px;
            font-size: 16px;
            color: #33691e;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>Certificate of Completion</h1>
        <h2>This is to certify that</h2>
        <p><strong>{{ $name }}</strong></p>
        <p>has successfully passed all required exams conducted by</p>
        <p class="school-name">Driving School</p>
        <p class="date">Date: {{ $date }}</p>
    </div>
</body>
</html>
