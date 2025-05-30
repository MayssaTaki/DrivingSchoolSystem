<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">

    <style>
        @font-face {
            font-family: 'Amiri';
            src: url('{{ storage_path("fonts/Amiri-Regular.ttf") }}') format("truetype");
        }

        body {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
        }
    </style>
</head>
<body>
    <h2>Booking Status Change Report</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Session Date</th>
                <th>Session Time</th>
                <th>Status</th>
                <th>Changed At</th>
                <th>Changed By</th>
                <th>Role</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ optional($log->booking->session)->session_date }}</td>
                    <td>{{ optional($log->booking->session)->start_time }}</td>
                    <td>{{ $log->status }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->changed_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ optional($log->changer)->name }}</td>
                    <td>{{ optional($log->changer)->role }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
