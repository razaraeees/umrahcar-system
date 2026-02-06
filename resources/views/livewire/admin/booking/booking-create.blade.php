<div>
    {{-- Page Header --}}
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="page-title">Create New Booking</h2>
                        <p class="text-muted">Fill in the booking details below</p>
                    </div>
                    <div>
                        <a href="{{ route('booking.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Bookings
                        </a>
                    </div>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form wire:submit.prevent="saveBooking">
                {{-- ========================================
                    CUSTOMER INFORMATION SECTION (ONE TIME ONLY)
                ========================================= --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header alert-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guest Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="guest_name"
                                    class="form-control @error('guest_name') is-invalid @enderror"
                                    placeholder="Enter guest full name" required>
                                @error('guest_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i> +</span>
                                    <input type="tel" wire:model.live="guest_phone"
                                        class="form-control @error('guest_phone') is-invalid @enderror"
                                        placeholder="923001234567" required>
                                    @error('guest_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-whatsapp"></i> +</span>
                                    <input type="tel" wire:model="guest_whatsapp"
                                        class="form-control @error('guest_whatsapp') is-invalid @enderror"
                                        placeholder="923001234567" {{ $same_as_contact ? 'disabled' : '' }}>
                                    @error('guest_whatsapp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" wire:model.live="same_as_contact" class="form-check-input"
                                        id="sameAsContact">
                                    <label class="form-check-label" for="sameAsContact">
                                        Same as contact number
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visa Type</label>
                                <select wire:model="visa_type"
                                    class="form-select @error('visa_type') is-invalid @enderror">
                                    <option value="">Select Visa Type</option>
                                    @forelse ($visaTypes as $visaType)
                                        <option value="{{ $visaType->name }}">{{ $visaType->name }}</option>
                                    @empty
                                        <option value="" disabled>No visa types found</option>
                                    @endforelse
                                </select>
                                @error('visa_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                                <select wire:model.live="payment_type"
                                    class="form-select @error('payment_type') is-invalid @enderror" required>
                                    <option value="">Select Payment Type</option>
                                    <option value="credit">Credit</option>
                                    <option value="cash">Cash</option>
                                </select>
                                @error('payment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Received Payment - Only show for Cash Payment (Customer Level) --}}
                            @if ($payment_type === 'cash')
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Total Received Payment (SAR)</label>
                                    <input type="number" wire:model.live="total_received_payment"
                                        class="form-control @error('total_received_payment') is-invalid @enderror"
                                        step="0.01" min="0" placeholder="0.00">
                                    @if (($total_received_payment ?? 0) > 0 && ($totalAmount ?? 0) > 0)
                                        <small class="form-text text-info">
                                            <i class="fas fa-calculator"></i>
                                            Total Remaining:
                                            <strong>{{ number_format((float) (($totalAmount ?? 0) - ($total_received_payment ?? 0)), 2) }}
                                                SAR</strong>
                                        </small>
                                    @endif
                                    @error('total_received_payment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ========================================
                    BOOKING DETAILS SECTION (MULTIPLE BOOKINGS)
                ========================================= --}}
                <div class="card mb-4 shadow-sm">
                    <div
                        class="card-header alert-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Booking Details</h4>
                        <button type="button" wire:click="addBooking" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>Add More Booking
                        </button>
                    </div>
                    <div class="card-body">
                        @foreach ($bookings as $index => $booking)
                            <div class="booking-item border rounded p-4 mb-4 bg-light"
                                wire:key="booking-{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <span class="badge alert-primary">Booking #{{ $index + 1 }}</span>
                                        @if (!empty($booking['booking_no']))
                                            <span class="badge bg-dark ms-2">Serial: {{ $booking['booking_no'] }}</span>
                                        @endif
                                    </h5>
                                    @if ($index > 0)
                                        <button type="button" wire:click="removeBooking({{ $index }})"
                                            class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash me-1"></i>Remove
                                        </button>
                                    @endif
                                </div>

                                {{-- Route Information --}}
                                <div class="route-section mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-route me-2"></i>Route Information
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Pickup Location <span
                                                    class="text-danger">*</span></label>
                                            <select wire:model.live="bookings.{{ $index }}.pickup_location_id"
                                                class="form-select @error('bookings.' . $index . '.pickup_location_id') is-invalid @enderror"
                                                required>
                                                <option value="">Select Pickup Location</option>
                                                @foreach ($pickupLocations as $location)
                                                    <option value="{{ $location->id }}">
                                                        {{ $location->pickup_location }} ({{ $location->type }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('bookings.' . $index . '.pickup_location_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        @if (($booking['pickup_location_type'] ?? '') === 'Hotel')
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Pickup Hotel Name <span
                                                        class="text-danger">*</span></label>
                                                <select wire:model="bookings.{{ $index }}.pickup_hotel_name"
                                                    class="form-select @error('bookings.' . $index . '.pickup_hotel_name') is-invalid @enderror"
                                                    required>
                                                    <option value="">Select Hotel</option>
                                                    @if (isset($booking['available_hotels']))
                                                        @foreach ($booking['available_hotels'] as $hotel)
                                                            <option value="{{ $hotel['name'] ?? $hotel }}">
                                                                {{ $hotel['name'] ?? $hotel }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle"></i> Hotels from selected pickup
                                                    location's city
                                                </small>
                                                @error('bookings.' . $index . '.pickup_hotel_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endif

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Dropoff Location <span
                                                    class="text-danger">*</span></label>
                                            <select wire:model.live="bookings.{{ $index }}.dropoff_location_id"
                                                class="form-select @error('bookings.' . $index . '.dropoff_location_id') is-invalid @enderror"
                                                {{ empty($booking['pickup_location_id']) ? 'disabled' : '' }} required>
                                                <option value="">Select Dropoff Location</option>
                                                @if (isset($booking['available_dropoffs']) && !empty($booking['available_dropoffs']))
                                                    @foreach ($dropoffLocations as $location)
                                                        @if (in_array($location->id, $booking['available_dropoffs']))
                                                            <option value="{{ $location->id }}">
                                                                {{ $location->drop_off_location }}
                                                                ({{ $location->type }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if (empty($booking['pickup_location_id']))
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-exclamation-triangle"></i> Select pickup location
                                                    first
                                                </small>
                                            @endif
                                            @error('bookings.' . $index . '.dropoff_location_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        @if (($booking['dropoff_location_type'] ?? '') === 'Hotel')
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Dropoff Hotel Name <span
                                                        class="text-danger">*</span></label>
                                                <select wire:model="bookings.{{ $index }}.dropoff_hotel_name"
                                                    class="form-select @error('bookings.' . $index . '.dropoff_hotel_name') is-invalid @enderror"
                                                    required>
                                                    <option value="">Select Hotel</option>
                                                    @if (isset($booking['available_dropoff_hotels']))
                                                        @foreach ($booking['available_dropoff_hotels'] as $hotel)
                                                            <option value="{{ $hotel['name'] ?? $hotel }}">
                                                                {{ $hotel['name'] ?? $hotel }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle"></i> Hotels from selected dropoff
                                                    location's city
                                                </small>
                                                @error('bookings.' . $index . '.dropoff_hotel_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Vehicle Information --}}
                                <div class="vehicle-section mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-car me-2"></i>Vehicle Information
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Vehicle <span
                                                    class="text-danger">*</span></label>
                                            <select wire:model.live="bookings.{{ $index }}.vehicle_id"
                                                class="form-select @error('bookings.' . $index . '.vehicle_id') is-invalid @enderror"
                                                {{ empty($booking['pickup_location_id']) || empty($booking['dropoff_location_id']) ? 'disabled' : '' }}
                                                required>
                                                <option value="">Select Vehicle</option>
                                                @if (isset($booking['available_vehicles']) && !empty($booking['available_vehicles']))
                                                    @foreach ($vehicles as $vehicle)
                                                        @if (in_array($vehicle->id, $booking['available_vehicles']))
                                                            <option value="{{ $vehicle->id }}">
                                                                {{ $vehicle->name }} - {{ $vehicle->model_variant }}
                                                                (Seats: {{ $vehicle->seating_capacity }}, Bags:
                                                                {{ $vehicle->bag_capacity }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if (empty($booking['pickup_location_id']) || empty($booking['dropoff_location_id']))
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-exclamation-triangle"></i> Select pickup and
                                                    dropoff locations first
                                                </small>
                                            @endif
                                            @error('bookings.' . $index . '.vehicle_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Base Price (SAR) <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" wire:model="bookings.{{ $index }}.price"
                                                class="form-control @error('bookings.' . $index . '.price') is-invalid @enderror"
                                                step="0.01" readonly>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Price will be calculated
                                                automatically
                                            </small>
                                            @error('bookings.' . $index . '.price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Passenger Information --}}
                                <div class="passenger-section mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-users me-2"></i>Passenger Information
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Adults <span
                                                    class="text-danger">*</span></label>
                                            <input type="number"
                                                wire:model.live="bookings.{{ $index }}.no_of_adults"
                                                class="form-control @error('bookings.' . $index . '.no_of_adults') is-invalid @enderror"
                                                min="1" required>
                                            @error('bookings.' . $index . '.no_of_adults')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Children</label>
                                            <input type="number"
                                                wire:model.live="bookings.{{ $index }}.no_of_children"
                                                class="form-control @error('bookings.' . $index . '.no_of_children') is-invalid @enderror"
                                                min="0">
                                            @error('bookings.' . $index . '.no_of_children')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Infants field hidden as requested --}}
                                        {{-- <div class="col-md-3 mb-3">
                                            <label class="form-label">Infants</label>
                                            <input type="number" wire:model.live="bookings.{{ $index }}.no_of_infants" 
                                                class="form-control @error('bookings.' . $index . '.no_of_infants') is-invalid @enderror" 
                                                min="0">
                                            @error('bookings.' . $index . '.no_of_infants') 
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div> --}}

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Total Passengers</label>
                                            <div class="alert alert-info mb-0 p-2 text-center">
                                                <strong>{{ $booking['total_passengers'] ?? 0 }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Schedule Information --}}
                                <div class="schedule-section mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-clock me-2"></i>Schedule Information
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Pickup Date <span
                                                    class="text-danger">*</span></label>
                                            <input type="date"
                                                wire:model="bookings.{{ $index }}.pickup_date"
                                                class="form-control @error('bookings.' . $index . '.pickup_date') is-invalid @enderror"
                                                min="{{ now()->format('Y-m-d') }}" required>
                                            @error('bookings.' . $index . '.pickup_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Pickup Time <span
                                                    class="text-danger">*</span></label>
                                            <input type="time"
                                                wire:model="bookings.{{ $index }}.pickup_time"
                                                class="form-control @error('bookings.' . $index . '.pickup_time') is-invalid @enderror"
                                                required>
                                            @error('bookings.' . $index . '.pickup_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Flight Information (Only for Airport Pickup) - PER BOOKING --}}
                                @if (($booking['pickup_location_type'] ?? '') === 'Airport')
                                    <div class="flight-section mb-4">
                                        <h6 class="border-bottom pb-2 mb-3">
                                            <i class="fas fa-plane me-2"></i>Flight Information <span
                                                class="badge bg-secondary">Optional</span>
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Airline</label>
                                                <select wire:model="bookings.{{ $index }}.airline_id"
                                                    class="form-select @error('bookings.' . $index . '.airline_id') is-invalid @enderror">
                                                    <option value="">Select Airline</option>
                                                    @foreach ($airlines as $airline)
                                                        <option value="{{ $airline->id }}">{{ $airline->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('bookings.' . $index . '.airline_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Flight Number</label>
                                                <input type="text"
                                                    wire:model="bookings.{{ $index }}.flight_number"
                                                    class="form-control @error('bookings.' . $index . '.flight_number') is-invalid @enderror"
                                                    placeholder="e.g., SV123">
                                                @error('bookings.' . $index . '.flight_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Arrival/Departure Date</label>
                                                <input type="date"
                                                    wire:model="bookings.{{ $index }}.arrival_departure_date"
                                                    class="form-control @error('bookings.' . $index . '.arrival_departure_date') is-invalid @enderror"
                                                    min="{{ now()->format('Y-m-d') }}">
                                                @error('bookings.' . $index . '.arrival_departure_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Arrival/Departure Time</label>
                                                <input type="time"
                                                    wire:model="bookings.{{ $index }}.arrival_departure_time"
                                                    class="form-control @error('bookings.' . $index . '.arrival_departure_time') is-invalid @enderror">
                                                @error('bookings.' . $index . '.arrival_departure_time')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Flight Route</label>
                                                <input type="text"
                                                    wire:model="bookings.{{ $index }}.flight_details"
                                                    class="form-control @error('bookings.' . $index . '.flight_details') is-invalid @enderror"
                                                    placeholder="e.g., JFK to JED">
                                                @error('bookings.' . $index . '.flight_details')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Booking Status - PER BOOKING --}}
                                <div class="status-section mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Booking Status & Payment
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Booking Status <span
                                                    class="text-danger">*</span></label>
                                            <select wire:model="bookings.{{ $index }}.booking_status"
                                                class="form-select @error('bookings.' . $index . '.booking_status') is-invalid @enderror"
                                                required>
                                                <option value="pending">Pending</option>
                                                <option value="hold">Hold</option>
                                                <option value="pickup">Pickup</option>
                                                <option value="dropoff">Drop-off</option>
                                                <option value="complete">Complete</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                            @error('bookings.' . $index . '.booking_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Received Payment - Only show for Cash Payment --}}
                                        @if ($payment_type === 'cash')
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Received Payment (SAR)</label>
                                                <input type="number"
                                                    wire:model.live="bookings.{{ $index }}.received_payment"
                                                    class="form-control @error('bookings.' . $index . '.received_payment') is-invalid @enderror"
                                                    step="0.01" min="0" placeholder="0.00" readonly>
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle"></i>
                                                    Auto-calculated from total received payment
                                                </small>
                                                @if (($booking['received_payment'] ?? 0) > 0 && ($booking['price'] ?? 0) > 0)
                                                    <small class="form-text text-info">
                                                        <i class="fas fa-calculator"></i>
                                                        Remaining:
                                                        <strong>{{ number_format((float) (($booking['price'] ?? 0) - ($booking['received_payment'] ?? 0)), 2) }}
                                                            SAR</strong>
                                                    </small>
                                                @endif
                                                @error('bookings.' . $index . '.received_payment')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endif

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Discount (SAR)</label>
                                            <input type="number"
                                                wire:model.live="bookings.{{ $index }}.discount_amount"
                                                class="form-control @error('bookings.' . $index . '.discount_amount') is-invalid @enderror"
                                                step="0.01" min="0" placeholder="0.00">
                                            @error('bookings.' . $index . '.discount_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Extra Information - PER BOOKING --}}
                                <div class="extra-info-section mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-sticky-note me-2"></i>Additional Notes
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Extra Information</label>
                                            <textarea wire:model="bookings.{{ $index }}.extra_information"
                                                class="form-control @error('bookings.' . $index . '.extra_information') is-invalid @enderror" rows="3"
                                                placeholder="Any special requests or notes for this booking..."></textarea>
                                            @error('bookings.' . $index . '.extra_information')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Booking Price Summary --}}
                                <div class="alert alert-primary">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Base Price:</strong> {{ number_format((float) ($booking['price'] ?? 0), 2) }}
                                            SAR
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Services:</strong>
                                            @php
                                                $servicesTotal = 0;
                                                if (isset($booking['selected_services'])) {
                                                    foreach ($booking['selected_services'] as $serviceId) {
                                                        $service = $additionalServicesList->firstWhere(
                                                            'id',
                                                            $serviceId,
                                                        );
                                                        if ($service) {
                                                            if ($service->charges_type === 'percentage') {
                                                                $servicesTotal +=
                                                                    (($booking['price'] ?? 0) *
                                                                        $service->charge_value) /
                                                                    100;
                                                            } else {
                                                                $servicesTotal += $service->charge_value;
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp
                                            {{ number_format((float) $servicesTotal, 2) }} SAR
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Discount:</strong>
                                            -{{ number_format((float) ($booking['discount_amount'] ?? 0), 2) }} SAR
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Subtotal:</strong>
                                            {{ number_format((float) (($booking['price'] ?? 0) + $servicesTotal - (float) ($booking['discount_amount'] ?? 0)), 2) }}
                                            SAR
                                        </div>
                                    </div>
                                </div>

                                {{-- Additional Services - PER BOOKING --}}
                                <div class="services-section mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-concierge-bell me-2"></i>Additional Services
                                    </h6>
                                    <div class="row">
                                        @foreach ($additionalServicesList as $service)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                        wire:model.live="bookings.{{ $index }}.selected_services"
                                                        value="{{ $service->id }}" class="form-check-input"
                                                        id="service-{{ $index }}-{{ $service->id }}">
                                                    <label class="form-check-label"
                                                        for="service-{{ $index }}-{{ $service->id }}">
                                                        {{ $service->services }}
                                                        @if ($service->charges_type === 'percentage')
                                                            <span
                                                                class="badge bg-info">{{ $service->charge_value }}%</span>
                                                        @else
                                                            <span
                                                                class="badge bg-success">{{ number_format((float) $service->charge_value, 2) }}
                                                                SAR</span>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ========================================
                    TOTAL AMOUNT SECTION
                ========================================= --}}
                <div class="card mb-4 shadow-sm border-primary">
                    <div class="card-header alert-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-calculator me-2"></i>Grand Total</h4>
                    </div>
                    <div class="card-body text-center">
                        <p class="text-muted mb-2">Sum of All Bookings + Their Individual Services</p>
                        <h2 class="text-primary mb-0" style="font-size: 2.5rem; font-weight: bold;">
                            {{ number_format((float) $totalAmount, 2) }} SAR
                        </h2>
                        @php
                            $totalBookingPrice = array_sum(array_map('floatval', array_column($bookings, 'price')));
                            $totalDiscount = array_sum(
                                array_map('floatval', array_column($bookings, 'discount_amount')),
                            );
                            $totalReceived = array_sum(
                                array_map('floatval', array_column($bookings, 'received_payment')),
                            );
                            $allServicesTotal = 0;
                            foreach ($bookings as $booking) {
                                if (isset($booking['selected_services'])) {
                                    foreach ($booking['selected_services'] as $serviceId) {
                                        $service = $additionalServicesList->firstWhere('id', $serviceId);
                                        if ($service) {
                                            if ($service->charges_type === 'percentage') {
                                                $allServicesTotal +=
                                                    (($booking['price'] ?? 0) * $service->charge_value) / 100;
                                            } else {
                                                $allServicesTotal += $service->charge_value;
                                            }
                                        }
                                    }
                                }
                            }
                        @endphp
                        <div class="mt-3 text-muted small">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Total Base Price:</strong> {{ number_format((float) $totalBookingPrice, 2) }} SAR
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Services:</strong> {{ number_format((float) $allServicesTotal, 2) }} SAR
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Discount:</strong> -{{ number_format((float) $totalDiscount, 2) }} SAR
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Received:</strong> 
                                    @if ($payment_type === 'cash')
                                        {{ number_format((float) ($total_received_payment ?? 0), 2) }} SAR
                                    @else
                                        {{ number_format((float) $totalReceived, 2) }} SAR
                                    @endif
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <strong>Total Remaining:</strong>
                                    <span class="text-danger fs-5">
                                        @if ($payment_type === 'cash')
                                            {{ number_format((float) ($totalAmount ?? 0) - (float) ($total_received_payment ?? 0), 2) }} SAR
                                        @else
                                            {{ number_format((float) $totalAmount - (float) $totalReceived, 2) }} SAR
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================
                    BUTTONS
                ========================================= --}}
                <div class="card-body">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" wire:click="cancel" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="fas fa-save me-2"></i>Save All Bookings
                            </span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .booking-item {
            transition: all 0.3s ease;
        }

        .booking-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .card-header h4 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .invalid-feedback {
            display: block;
        }

        .btn-lg {
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Auto-scroll to error on validation failure
            Livewire.on('scrollToError', () => {
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            });
        });
    </script>
@endpush
