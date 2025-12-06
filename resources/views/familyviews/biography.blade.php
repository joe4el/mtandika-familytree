@extends('layouts.app')

@section('content')
<div class="bio-page">
  <h2>{{ $member['name'] }}</h2>
  <p><strong>Date of Birth:</strong> {{ $member['dob'] ?? 'N/A' }}</p>
  <p><strong>Date of Death:</strong> {{ $member['dod'] ?? 'N/A' }}</p>
  <h3>Biography</h3>
  <p>{{ $member['bio'] ?? 'No biography available yet.' }}</p>

  <a href="{{ url()->previous() }}" class="btn btn-secondary">← Back</a>
</div>
@endsection
