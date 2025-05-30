<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Event Report - {{ $event->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .event-details {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        .event-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .detail-row {
            margin: 10px 0;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .description {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Event Report</h1>
        <p>Generated for: {{ $user->name }}</p>
        <p>Generated on: {{ $generatedAt->format('F j, Y H:i:s') }}</p>
    </div>

    <div class="event-details">
        <div class="event-title">{{ $event->title }}</div>
        
        <div class="detail-row">
            <span class="detail-label">Event Type:</span>
            {{ ucfirst($event->event_type) }}
        </div>

        <div class="detail-row">
            <span class="detail-label">Date:</span>
            {{ $event->event_date->format('F j, Y') }}
        </div>

        @if($event->location)
            <div class="detail-row">
                <span class="detail-label">Location:</span>
                {{ $event->location }}
            </div>
        @endif

        <div class="detail-row">
            <span class="detail-label">Visibility:</span>
            {{ $event->is_public ? 'Public' : 'Private' }}
        </div>

        @if($event->description)
            <div class="description">
                <div class="detail-label">Description:</div>
                {{ $event->description }}
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated from the LEG (Lineage Exploration and Genealogy) application.</p>
    </div>
</body>
</html> 