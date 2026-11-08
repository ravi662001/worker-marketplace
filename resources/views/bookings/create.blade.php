@extends('layouts.app')

@section('title', 'Book ' . $service->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="panel">

                <span class="eyebrow">BOOK A PROFESSIONAL</span>

                <h1 class="fw-bold">{{ $service->name }}</h1>

                <p class="text-secondary">
                    Starting at ₹{{ number_format($service->base_price, 2) }}
                    · {{ $service->duration_minutes }} minutes
                </p>

                <form method="POST" action="{{ route('bookings.store', $service) }}">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Choose worker</label>

                            <select
                                class="form-select"
                                name="worker_id"
                                id="worker_id"
                                required
                            >
                                <option value="">Select available worker</option>

                                @foreach($workers as $worker)
                                    <option
                                        value="{{ $worker->id }}"
                                        @selected(old('worker_id') == $worker->id)
                                    >
                                        {{ $worker->name }} — {{ $worker->workerProfile->city }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Your name</label>

                            <input
                                class="form-control"
                                name="customer_name"
                                value="{{ old('customer_name', auth()->user()->name) }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mobile number</label>

                            <input
                                class="form-control"
                                name="customer_phone"
                                value="{{ old('customer_phone', auth()->user()->phone) }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City</label>

                            <input
                                class="form-control"
                                name="city"
                                value="{{ old('city', 'Varanasi') }}"
                                required
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label">Full service address</label>

                            <textarea
                                class="form-control"
                                name="address"
                                rows="2"
                                required
                            >{{ old('address') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">PIN code</label>

                            <input
                                class="form-control"
                                name="pincode"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                value="{{ old('pincode') }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Booking date</label>

                            <input
                                class="form-control"
                                type="date"
                                name="booking_date"
                                id="booking_date"
                                value="{{ old('booking_date') }}"
                                min="{{ date('Y-m-d') }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Available start time</label>

                            <select
                                class="form-select"
                                name="start_time"
                                id="start_time"
                                required
                                disabled
                            >
                                <option value="">First select worker and date</option>
                            </select>

                            <small
                                class="text-secondary d-block mt-2"
                                id="slot_message"
                                aria-live="polite"
                            >
                                Select a worker and booking date to see available slots.
                            </small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Work details (optional)</label>

                            <textarea
                                class="form-control"
                                name="notes"
                                rows="3"
                                placeholder="Tell the professional what you need..."
                            >{{ old('notes') }}</textarea>
                        </div>

                    </div>

                    <div class="price-summary mt-4">

                        <div class="d-flex justify-content-between">
                            <span>Service price</span>
                            <strong>₹{{ number_format($service->base_price, 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Platform fee (10%)</span>
                            <strong>₹{{ number_format($service->base_price * 0.10, 2) }}</strong>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between fs-5">
                            <strong>Estimated total</strong>
                            <strong>₹{{ number_format($service->base_price * 1.10, 2) }}</strong>
                        </div>

                        <small class="text-secondary">
                            Demo booking only: online payment integration is not enabled in this starter.
                        </small>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary btn-lg w-100 mt-4"
                        @disabled($workers->isEmpty())
                    >
                        Submit booking request
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const workerSelect = document.getElementById('worker_id');
    const dateInput = document.getElementById('booking_date');
    const timeSelect = document.getElementById('start_time');
    const slotMessage = document.getElementById('slot_message');

    const availableSlotsUrl = @json(route('bookings.available-slots', $service));

    const oldTime = @json(old('start_time'));

    async function loadAvailableSlots() {

        const workerId = workerSelect.value;
        const bookingDate = dateInput.value;

        timeSelect.innerHTML = '<option value="">Loading available slots...</option>';
        timeSelect.disabled = true;

        if (!workerId || !bookingDate) {
            timeSelect.innerHTML = '<option value="">First select worker and date</option>';
            slotMessage.textContent = 'Select a worker and booking date to see available slots.';
            return;
        }

        slotMessage.textContent = 'Checking worker availability...';

        try {

            const params = new URLSearchParams({
                worker_id: workerId,
                booking_date: bookingDate
            });

            const response = await fetch(
                availableSlotsUrl + '?' + params.toString(),
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Unable to fetch available slots.');
            }

            const result = await response.json();

            timeSelect.innerHTML = '';

            if (!result.slots || result.slots.length === 0) {

                timeSelect.innerHTML = '<option value="">No slots available</option>';

                slotMessage.textContent =
                    'No available slots for this worker on the selected date. Please choose another date or worker.';

                return;
            }

            timeSelect.innerHTML = '<option value="">Select a time slot</option>';

            result.slots.forEach(function (time) {

                const option = document.createElement('option');

                option.value = time;

                const [hours, minutes] = time.split(':');
                const hour = parseInt(hours, 10);
                const period = hour >= 12 ? 'PM' : 'AM';
                const displayHour = hour % 12 || 12;

                option.textContent =
                    displayHour + ':' + minutes + ' ' + period;

                if (oldTime === time) {
                    option.selected = true;
                }

                timeSelect.appendChild(option);
            });

            timeSelect.disabled = false;

            slotMessage.textContent =
                result.slots.length + ' available time slot(s) found.';

        } catch (error) {

            timeSelect.innerHTML = '<option value="">Could not load slots</option>';

            slotMessage.textContent =
                'Unable to check availability. Please refresh the page and try again.';

            console.error(error);
        }
    }

    workerSelect.addEventListener('change', loadAvailableSlots);
    dateInput.addEventListener('change', loadAvailableSlots);

    // Restore available slots if the form is reopened after validation.
    if (workerSelect.value && dateInput.value) {
        loadAvailableSlots();
    }

});
</script>
@endsection
