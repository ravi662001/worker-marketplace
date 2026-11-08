
@extends('layouts.app')

@section('title', 'Become a worker')

@section('content')
<div class="container py-5">
    <div class="form-shell">
        <h1 class="fw-bold">Become a service professional</h1>
        <p class="text-secondary">
            Submit your details and availability. Your profile will be reviewed by the admin.
        </p>

        <form method="POST" action="{{ route('worker.register.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">About your skills</label>
                <textarea class="form-control" name="bio" rows="3" required>{{ old('bio') }}</textarea>
                @error('bio') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">City</label>
                    <input class="form-control" name="city" value="{{ old('city', 'Varanasi') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">State</label>
                    <input class="form-control" name="state" value="{{ old('state', 'Uttar Pradesh') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">PIN code</label>
                    <input class="form-control" name="pincode" maxlength="6" value="{{ old('pincode') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Experience (years)</label>
                    <input class="form-control" type="number" min="0" max="60" name="experience_years" value="{{ old('experience_years', 0) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Service radius (km)</label>
                    <input class="form-control" type="number" min="1" max="100" name="service_radius_km" value="{{ old('service_radius_km', 10) }}" required>
                </div>
            </div>

            <hr class="my-4">

            <h4 class="fw-bold">Working Availability</h4>
            <p class="text-secondary">Select the days when you accept customer bookings.</p>

            <div class="mb-3">
                <label class="form-label d-block">Working Days</label>

                @php
                    $days = [
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    ];

                    $selectedDays = old('working_days', [
                        'monday', 'tuesday', 'wednesday',
                        'thursday', 'friday', 'saturday'
                    ]);
                @endphp

                <div class="row g-2">
                    @foreach($days as $value => $label)
                        <div class="col-6 col-md-4">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="working_days[]"
                                    value="{{ $value }}"
                                    id="day_{{ $value }}"
                                    {{ in_array($value, $selectedDays) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="day_{{ $value }}">
                                    {{ $label }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('working_days') <small class="text-danger">{{ $message }}</small> @enderror
                @error('working_days.*') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Work Start Time</label>
                    <input
                        class="form-control"
                        type="time"
                        name="work_start_time"
                        value="{{ old('work_start_time', '09:00') }}"
                        required
                    >
                    @error('work_start_time') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Work End Time</label>
                    <input
                        class="form-control"
                        type="time"
                        name="work_end_time"
                        value="{{ old('work_end_time', '18:00') }}"
                        required
                    >
                    @error('work_end_time') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <button class="btn btn-primary mt-4" type="submit">
                Submit for approval
            </button>
        </form>
    </div>
</div>
@endsection
