<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion</title>
    <style>
        body {

           font-family:  'Amiri', serif;
            background-color: #fdfdfd;
            color: #000;
            padding: 40px;
        }

        .certificate {
            border: 10px double #000;
            padding: 60px;
            background-color: #fff;
            width: 85%;
            margin: auto;
            position: relative;
            text-align: center;
            z-index: 1;
        }

        .certificate h1 {
            font-size: 40px;
            margin-bottom: 5px;
            color: #11560f;
            text-transform: uppercase;
        }

        .certificate h2 {
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: normal;
        }

        .certificate p {
            font-size: 20px;
            line-height: 2;
            margin: 15px 0;
        }

        .recipient-name {
            font-size: 28px;
            font-weight: bold;
            color: #11560f;
        }

        .school-name {
            font-size: 22px;
            font-weight: bold;
            color: #11560f;
            margin-top: 20px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding: 0 40px;
        }

        .footer .date,
        .footer .signature {
            font-size: 16px;
            text-align: center;
            width: 40%;
        }

        .signature-line-container {
            position: relative;
        }

        .signature-line {
            margin-top: 40px;
            border-top: 1px solid #000;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .stamps-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: -20px; /* لتقريب الستامبات من الخط */
        }

        .seal {
            width: 50px;
            opacity: 1;
            display: block;
        }

        .half-over-line {
            position: relative;
            top: -25px; /* نصفه فوق السطر */
        }

        .logo {
            position: absolute;
            top: 40px;
            right: 40px;
            width: 90px;
            z-index: 2;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            width: 300px;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <!-- Background Watermark -->
        <img src="{{ public_path('images/stamp3.png') }}" class="watermark" alt="Watermark">

        <!-- Logo -->
        <img src="{{ public_path('images/logo.jpg') }}" class="logo" alt="Logo">
        <h1>CERTIFICATE OF COMPLETION</h1>
        <h2>This certificate is awarded to</h2>

        <p class="recipient-name">{{ $name }}</p>

        <p>For successfully passing all seven theoretical driving tests,</p>
        <p>And fulfilling all standards and requirements approved by</p>

        <p class="school-name">AL-QYADA DRIVING SCHOOL</p>

        <div class="footer">
            <div class="date">
                Date:<br>
                <strong>{{ $date }}</strong>
                <br>
                <img src="{{ public_path('images/SIGNATURE.png') }}" alt="Signature"
                     style="width:120px; margin-top: 10px;">
            </div>
            <div class="signature">
                <div class="signature-line-container">
                    <div class="signature-line"></div>
                    <div class="stamps-container">
                        <img src="{{ public_path('images/stamp.png') }}" class="seal" alt="Seal">
                        <img src="{{ public_path('images/stamp3.png') }}" class="seal half-over-line" alt="Seal">
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
