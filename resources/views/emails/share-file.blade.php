<p>Hello {{ $sharedWithUser->name }},</p>

<p>{{ $sharedByUser->name }} has shared the following content with you.</p>

<hr>

@foreach($content as $file)
    <p>{{ $file->is_folder ? 'Folder: ' : 'File: ' }} - {{ $file->name }}</p>
@endforeach

<p>Thank You</p>
<p>{{ config('app.name') }}</p>
