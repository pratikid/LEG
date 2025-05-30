<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }} Events Report</title>
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
        .event {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        .event-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .event-details {
            margin-top: 10px;
            color: #666;
        }
        .event-description {
            margin-top: 10px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #f0f7ff;
            border: 1px solid #b3d7ff;
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
        <h1>{{ ucfirst($type) }} Events Report</h1>
        <p>Generated for: {{ $user->name }}</p>
        <p>Generated on: {{ $generatedAt->format('F j, Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h2>Summary</h2>
        <p>Total {{ $type }} events: {{ $events->count() }}</p>
        @if($events->count() > 0)
            <p>Date range: {{ $events->min('event_date')->format('F j, Y') }} to {{ $events->max('event_date')->format('F j, Y') }}</p>
        @endif
    </div>

    <div class="content">
        @forelse($events as $event)
            <div class="event">
                <div class="event-title">{{ $event->title }}</div>
                <div class="event-details">
                    <strong>Date:</strong> {{ $event->event_date->format('F j, Y') }}<br>
                    @if($event->location)
                        <strong>Location:</strong> {{ $event->location }}<br>
                    @endif
                    <strong>Visibility:</strong> {{ $event->is_public ? 'Public' : 'Private' }}
                </div>
                @if($event->description)
                    <div class="event-description">
                        <strong>Description:</strong><br>
                        {{ $event->description }}
                    </div>
                @endif
            </div>
        @empty
            <p>No {{ $type }} events found.</p>
        @endforelse
    </div>

    <div class="footer">
        <p>This report was generated from the LEG (Lineage Exploration and Genealogy) application.</p>
    </div>
</body>
</html> 