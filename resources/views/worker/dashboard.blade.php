@extends('layouts.app')

@section('title', 'Worker Dashboard')

@section('content')
<div class="container py-5">

    <div class="d-flex justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <span class="eyebrow">PROFESSIONAL PORTAL</span>
            <h1 class="fw-bold">Worker Dashboard</h1>
            <p class="text-secondary">Welcome, {{ auth()->user()->name }}</p>
        </div>

        <span class="status-pill">
            {{ $profile?->verification_status ?? 'profile incomplete' }}
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!$profile)

        <a class="btn btn-primary" href="{{ route('worker.register') }}">
            Complete Worker Profile
        </a>

    @else

        @if($profile->verification_status !== 'approved')
            <div class="alert alert-warning">
                Your profile is awaiting admin verification.
                You cannot receive bookings until approved.
            </div>
        @endif

        <!-- Availability Settings -->
        <div class="panel mb-4">
            <h4 class="fw-bold">Availability Settings</h4>

            <p class="text-secondary">
                Select your working days and working hours.
            </p>

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

                $selectedDays = old(
                    'working_days',
                    $profile->working_days ?? []
                );
            @endphp

            <form method="POST" action="{{ route('worker.availability.update') }}">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label class="form-label fw-semibold">Working Days</label>

                    <div class="row g-3">
                        @foreach($days as $value => $label)
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="working_days[]"
                                        value="{{ $value }}"
                                        id="day_{{ $value }}"
                                        {{ in_array($value, $selectedDays ?? []) ? 'checked' : '' }}
                                    >

                                    <label class="form-check-label" for="day_{{ $value }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Work Start Time</label>
                        <input
                            type="time"
                            name="work_start_time"
                            class="form-control"
                            value="{{ old('work_start_time', $profile->work_start_time ? substr($profile->work_start_time, 0, 5) : '09:00') }}"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Work End Time</label>
                        <input
                            type="time"
                            name="work_end_time"
                            class="form-control"
                            value="{{ old('work_end_time', $profile->work_end_time ? substr($profile->work_end_time, 0, 5) : '18:00') }}"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-4">
                    Save Availability
                </button>
            </form>
        </div>

        <!-- Holiday Management -->
        <div class="panel mb-4">
            <h4 class="fw-bold">Holiday Management</h4>

            <p class="text-secondary">
                Add dates when you are unavailable for work.
            </p>

            <form method="POST" action="{{ route('worker.holidays.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Holiday Date</label>
                        <input
                            type="date"
                            name="unavailable_date"
                            class="form-control"
                            min="{{ date('Y-m-d') }}"
                            value="{{ old('unavailable_date') }}"
                            required
                        >
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Reason (Optional)</label>
                        <input
                            type="text"
                            name="reason"
                            class="form-control"
                            placeholder="Personal work, festival, etc."
                            maxlength="255"
                            value="{{ old('reason') }}"
                        >
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            Add Holiday
                        </button>
                    </div>
                </div>
            </form>

            <hr class="my-4">

            <h5 class="fw-bold mb-3">My Holidays</h5>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Holiday Date</th>
                            <th>Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($holidays as $holiday)
                            <tr>
                                <td>
                                    {{ $holiday->unavailable_date->format('d M Y') }}
                                </td>

                                <td>
                                    {{ $holiday->reason ?: 'Not specified' }}
                                </td>

                                <td>
                                    <form
                                        method="POST"
                                        action="{{ route('worker.holidays.destroy', $holiday->id) }}"
                                        onsubmit="return confirm('Are you sure you want to remove this holiday?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-secondary">
                                    No holidays added yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @endif

    <!-- Existing Booking Section -->
    <div class="panel mt-4">
        <h4 class="fw-bold">Booking Requests and Jobs</h4>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->booking_code }}</td>

                            <td>
                                {{ $booking->customer_name }}
                                <br>
                                <small>{{ $booking->customer_phone }}</small>
                            </td>

                            <td>{{ $booking->service->name }}</td>

                            <td>
                                {{ $booking->booking_date->format('d M Y') }}
                                {{ substr($booking->start_time, 0, 5) }}
                            </td>

                            <td>
                                {{ str_replace('_', ' ', $booking->status) }}
                            </td>

                            <td>
                                @if($booking->status === 'pending')

                                    @foreach(['accepted' => 'Accept', 'rejected' => 'Reject'] as $status => $label)
                                        <form
                                            class="d-inline"
                                            method="POST"
                                            action="{{ route('worker.booking.status', $booking) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <input
                                                type="hidden"
                                                name="status"
                                                value="{{ $status }}"
                                            >

                                            <button class="btn btn-sm {{ $status === 'accepted' ? 'btn-success' : 'btn-outline-danger' }}">
                                                {{ $label }}
                                            </button>
                                        </form>
                                    @endforeach

                                @elseif(in_array($booking->status, ['accepted', 'on_the_way', 'in_progress']))

                                    <form
                                        method="POST"
                                        action="{{ route('worker.booking.status', $booking) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <select
                                            name="status"
                                            class="form-select form-select-sm d-inline w-auto"
                                        >
                                            <option value="on_the_way">On the way</option>
                                            <option value="in_progress">Start job</option>
                                            <option value="completed">Complete</option>
                                        </select>

                                        <button class="btn btn-sm btn-primary">
                                            Update
                                        </button>
                                    </form>

                                @else
                                    —
                                @endif
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary">
                                No bookings assigned yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $bookings->links() }}
    </div>

</div>
@endsection
