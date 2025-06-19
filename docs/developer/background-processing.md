# Background Processing & Notifications

This document describes the background processing system for GEDCOM imports and the notification system in LEG.

## Overview

The background processing system allows users to upload GEDCOM files and have them processed asynchronously, with real-time progress tracking and notifications upon completion.

## Architecture

### Components

1. **ImportGedcomJob** - Background job for processing GEDCOM files
2. **ImportProgress** - Model for tracking import progress
3. **Notifications** - Email and database notifications for import status
4. **Queue Workers** - Docker containers for processing background jobs
5. **Progress Tracking** - Real-time progress updates via API

### Queue Configuration

The system uses Redis as the queue driver with multiple queues:

- `imports` - High priority queue for GEDCOM imports
- `notifications` - Queue for sending notifications
- `default` - General purpose queue

## Implementation Details

### Background Job Processing

```php
// Dispatch a GEDCOM import job
ImportGedcomJob::dispatch($filePath, $treeId, $userId, $fileName);
```

The job:
1. Reads the GEDCOM file from storage
2. Parses the content using GedcomService
3. Imports data to PostgreSQL and Neo4j
4. Updates progress tracking
5. Sends notifications
6. Cleans up temporary files

### Progress Tracking

Progress is tracked in the `import_progress` table:

- `status`: pending, processing, completed, failed
- `total_records`: Total number of records to process
- `processed_records`: Number of records processed
- `error_message`: Error details if import fails

### Notifications

Two types of notifications are sent:

1. **GedcomImportCompleted** - Sent on successful import
   - Includes tree details and record counts
   - Provides link to tree visualization
   - Sent via email and stored in database

2. **GedcomImportFailed** - Sent on import failure
   - Includes error details
   - Provides link to retry import
   - Sent via email and stored in database

### Real-time Progress Updates

Progress is updated via AJAX polling:

```javascript
// Poll progress every 2 seconds
setInterval(() => {
    fetch(`/import-progress/${treeId}`)
        .then(response => response.json())
        .then(data => updateProgressUI(data));
}, 2000);
```

## Docker Setup

The system includes a dedicated queue worker container:

```yaml
queue:
  build:
    context: .
    dockerfile: docker/app/Dockerfile
  command: php artisan queue:work redis --queue=imports,notifications,default
  environment:
    - QUEUE_CONNECTION=redis
```

## Usage

### Starting the System

1. Start all containers:
   ```bash
   docker compose up -d
   ```

2. Run migrations:
   ```bash
   docker compose exec app php artisan migrate
   ```

3. The queue worker will automatically start processing jobs.

### Monitoring

- Check queue status: `docker compose exec app php artisan queue:monitor`
- View failed jobs: `docker compose exec app php artisan queue:failed`
- Retry failed jobs: `docker compose exec app php artisan queue:retry all`

### Scaling

To scale queue workers:

```yaml
queue:
  deploy:
    replicas: 3  # Run 3 queue worker instances
```

## API Endpoints

### Import Progress

- `GET /import-progress/{treeId}` - Get progress for specific tree
- `GET /import-progress` - Get all progress for current user

### Notifications

- `POST /notifications/{id}/mark-as-read` - Mark notification as read
- `POST /notifications/mark-all-as-read` - Mark all notifications as read
- `GET /notifications/unread-count` - Get unread notification count

## Error Handling

- Jobs are retried up to 3 times on failure
- Failed jobs are logged and can be retried manually
- Temporary files are cleaned up on both success and failure
- Users are notified of failures with error details

## Performance Considerations

- Large GEDCOM files are processed in chunks
- Database transactions ensure data consistency
- Neo4j operations are batched for better performance
- Progress updates are throttled to prevent API spam

## Future Enhancements

- WebSocket-based real-time progress updates
- Import validation before processing
- Support for multiple file formats
- Import scheduling and batch processing
- Advanced error recovery mechanisms 