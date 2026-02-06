<div>
    {{-- Page Header --}}
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="page-title">Edit Booking</h2>
                        <p class="text-muted">Update booking details below</p>
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

            <form wire:submit.prevent="update">
                {{-- ========================================
                    CUSTOMER INFORMATION SECTION
                ========================================= --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header alert-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guest Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model.defer="guest_name"
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
                                    <input type="tel" wire:model.defer="guest_phone"
                                        class="form-control @error('guest_phone') is-invalid @enderror"
                                        placeholder="923001234567" required>
                                </div>
                                @error('guest_phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-whatsapp"></i> +</span>
                                    <input type="tel" wire:model.defer="guest_whatsapp"
                                        class="form-control @error('guest_whatsapp') is-invalid @enderror"
                                        placeholder="923001234567">
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox"
                                        wire:click="$set('guest_whatsapp', '{{ $guest_phone }}')" id="sameAsContact">
                                    <label class="form-check-label" for="sameAsContact">
                                        Same as contact number
                                    </label>
                                </div>
                                @error('guest_whatsapp')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visa Type</label>
                                <select wire:model.defer="visa_type"
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

                            {{-- Total Received Payment - Only show for Cash Payment --}}
                            @if ($payment_type === 'cash')
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Total Received Payment (SAR)</label>
                                    <input type="number" wire:model.live="received_payment"
                                        class="form-control @error('received_payment') is-invalid @enderror"
                                        step="0.01" min="0" max="{{ $totalAmount ?? 0 }}" placeholder="0.00">
                                    @if (($received_payment ?? 0) > 0 && ($totalAmount ?? 0) > 0)
                                        <small class="form-text text-info">
                                            <i class="fas fa-calculator"></i>
                                            Total Remaining:
                                            <strong>{{ number_format(($totalAmount ?? 0) - ($received_payment ?? 0), 2) }}
                                                SAR</strong>
                                        </small>
                                    @endif
                                    @error('received_payment')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ========================================
                    BOOKING DETAILS SECTION
                ========================================= --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header alert-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Booking Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="booking-item border rounded p-4 mb-4 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <span class="badge alert-primary">Booking #1</span>
                                    @if (!empty($booking?->booking_no))
                                        <span class="badge bg-dark ms-2">Serial: {{ $booking->booking_no }}</span>
                                    @endif
                                </h5>
                            </div>

                            {{-- Route Information --}}
                            <div class="route-section mb-4">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-route me-2"></i>Route Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pickup Location <span class="text-danger">*</span></label>
                                        <select wire:model.live="pickup_location_id"
                                            class="form-select @error('pickup_location_id') is-invalid @enderror"
                                            required>
                                            <option value="">Select Pickup Location</option>
                                            @foreach ($pickupLocations as $location)
                                                <option value="{{ $location->id }}">
                                                    {{ $location->pickup_location }} ({{ $location->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('pickup_location_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    @if($pickup_location_type === 'Hotel')
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Pickup Hotel Name</label>
                                            <select class="form-select @error('pickup_hotel_name') is-invalid @enderror"
                                                wire:model.defer="pickup_hotel_name"
                                                        @if(empty($availableHotels)) disabled @endif>
                                                <option value="">Select Hotel</option>
                                                @foreach($availableHotels as $hotel)
                                                    <option value="{{ $hotel->name }}" {{ $pickup_hotel_name == $hotel->name ? 'selected' : '' }}>{{ $hotel->name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Hotels from selected pickup location's city
                                            </small>
                                            @error('pickup_hotel_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Dropoff Location <span class="text-danger">*</span></label>
                                        <select wire:model.live="dropoff_location_id"
                                            class="form-select @error('dropoff_location_id') is-invalid @enderror"
                                            @if(!$pickup_location_id) disabled @endif required>
                                            <option value="">Select Dropoff Location</option>
                                            @foreach($dropoffLocations as $location)
                                                @if(in_array($location->id, $availableDropoffs))
                                                    <option value="{{ $location->id }}">
                                                        {{ $location->drop_off_location }} ({{ $location->type }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @if(!$pickup_location_id)
                                            <small class="form-text text-muted">
                                                <i class="fas fa-exclamation-triangle"></i> Select pickup location first
                                            </small>
                                        @endif
                                        @error('dropoff_location_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    @if($dropoff_location_type === 'Hotel')
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Dropoff Hotel Name</label>
                                            <select class="form-select @error('dropoff_hotel_name') is-invalid @enderror"
                                                wire:model.defer="dropoff_hotel_name"
                                                        @if(empty($availableDropoffHotels)) disabled @endif>
                                                <option value="">Select Hotel</option>
                                                @foreach($availableDropoffHotels as $hotel)
                                                    <option value="{{ $hotel->name }}" {{ $dropoff_hotel_name == $hotel->name ? 'selected' : '' }}>{{ $hotel->name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Hotels from selected dropoff location's city
                                            </small>
                                            @error('dropoff_hotel_name')
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
                                        <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                                        <select class="form-select @error('vehicle_id') is-invalid @enderror"
                                            wire:model.live="vehicle_id"
                                            @if(!$pickup_location_id || !$dropoff_location_id) disabled @endif required>
                                            <option value="">Select Vehicle</option>
                                            @foreach($vehicles as $vehicle)
                                                @if(in_array($vehicle->id, $availableVehicles))
                                                    <option value="{{ $vehicle->id }}">
                                                        {{ $vehicle->name }} - {{ $vehicle->model_variant }} 
                                                        (Seats: {{ $vehicle->seating_capacity }}, Bags: {{ $vehicle->bag_capacity }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @if(!$pickup_location_id || !$dropoff_location_id)
                                            <small class="form-text text-muted">
                                                <i class="fas fa-exclamation-triangle"></i> Select pickup and dropoff locations first
                                            </small>
                                        @elseif(count($availableVehicles) === 0)
                                            <small class="form-text text-danger">No vehicles available for this route</small>
                                        @else
                                            <small class="form-text text-muted">{{ count($availableVehicles) }} vehicle(s) available</small>
                                        @endif
                                        @error('vehicle_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Base Price (SAR) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('price') is-invalid @enderror"
                                            wire:model="price"
                                            placeholder="Auto-calculated"
                                            readonly>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Price will be calculated automatically
                                        </small>
                                        @error('price')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
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
                                        <label class="form-label">Adults <span class="text-danger">*</span></label>
                                        <input type="number"
                                            class="form-control @error('no_of_adults') is-invalid @enderror"
                                            wire:model.live="no_of_adults" min="1" required>
                                        @error('no_of_adults')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Children</label>
                                        <input type="number"
                                            class="form-control @error('no_of_children') is-invalid @enderror"
                                            wire:model.live="no_of_children" min="0">
                                        @error('no_of_children')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Infants field hidden as requested --}}
                                    {{-- <div class="col-md-3 mb-3">
                                        <label class="form-label">Infants</label>
                                        <input type="number"
                                            class="form-control @error('no_of_infants') is-invalid @enderror"
                                            wire:model.defer="no_of_infants" min="0">
                                        @error('no_of_infants')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div> --}}

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Total Passengers</label>
                                        <div class="alert alert-info mb-0 p-2 text-center">
                                            <strong>{{ $total_passengers }}</strong>
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
                                        <label class="form-label">Pickup Date <span class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('pickup_date') is-invalid @enderror"
                                            wire:model.defer="pickup_date" required>
                                        @error('pickup_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pickup Time <span class="text-danger">*</span></label>
                                        <input type="time"
                                            class="form-control @error('pickup_time') is-invalid @enderror"
                                            wire:model.defer="pickup_time" required>
                                        @error('pickup_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Flight Information (Only for Airport Pickup) --}}
                            @if ($pickup_location_type === 'Airport')
                                <div class="flight-section mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-plane me-2"></i>Flight Information <span class="badge bg-secondary">Optional</span>
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Airline</label>
                                            <select wire:model.defer="airline_id" 
                                                   class="form-select @error('airline_id') is-invalid @enderror">
                                                <option value="">Select Airline</option>
                                                @foreach ($airlines as $airline)
                                                    <option value="{{ $airline->id }}" {{ $airline_id == $airline->id ? 'selected' : '' }}>
                                                        {{ $airline->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('airline_id') 
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Flight Number</label>
                                            <input type="text"
                                                class="form-control @error('flight_number') is-invalid @enderror"
                                                wire:model.defer="flight_number"
                                                placeholder="e.g., SV123">
                                            @error('flight_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Arrival/Departure Date</label>
                                            <input type="date"
                                                class="form-control @error('arrival_departure_date') is-invalid @enderror"
                                                wire:model.defer="arrival_departure_date">
                                            @error('arrival_departure_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Arrival/Departure Time</label>
                                            <input type="time"
                                                class="form-control @error('arrival_departure_time') is-invalid @enderror"
                                                wire:model.defer="arrival_departure_time">
                                            @error('arrival_departure_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label">Flight Route</label>
                                            <input type="text"
                                                class="form-control @error('flight_details') is-invalid @enderror"
                                                wire:model.defer="flight_details"
                                                placeholder="e.g., JFK to JED">
                                            @error('flight_details')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Booking Status --}}
                            <div class="status-section mb-4">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Booking Status & Payment
                                </h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Booking Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('booking_status') is-invalid @enderror"
                                            wire:model.defer="booking_status" required>
                                            <option value="Pending">Pending</option>
                                            <option value="Pickup">Pickup</option>
                                            <option value="Dropoff">Dropoff</option>
                                            <option value="Hold">Hold</option>
                                            <option value="Cancelled">Cancelled</option>
                                            <option value="Complete">Complete</option>
                                        </select>
                                        @error('booking_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Discount (SAR)</label>
                                        <input type="number"
                                            class="form-control @error('discountAmount') is-invalid @enderror"
                                            wire:model.live="discountAmount"
                                            step="0.01" min="0" placeholder="0.00">
                                        @error('discountAmount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Received Payment - Only show for Cash Payment --}}
                                    @if ($payment_type === 'cash')
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Received Payment (SAR)</label>
                                            <input type="number"
                                                wire:model.live="received_payment"
                                                class="form-control @error('received_payment') is-invalid @enderror"
                                                step="0.01" min="0" max="{{ $totalAmount ?? 0 }}" placeholder="0.00" readonly>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Auto-calculated from total received payment
                                            </small>
                                            @if (($received_payment ?? 0) > 0 && ($price ?? 0) > 0)
                                                <small class="form-text text-info">
                                                    <i class="fas fa-calculator"></i>
                                                    Remaining:
                                                    <strong>{{ number_format(($price ?? 0) - ($received_payment ?? 0), 2) }}
                                                        SAR</strong>
                                                </small>
                                            @endif
                                            @error('received_payment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Extra Information --}}
                            <div class="extra-info-section mb-4">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-sticky-note me-2"></i>Additional Notes
                                </h6>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Extra Information</label>
                                        <textarea class="form-control @error('extra_information') is-invalid @enderror" 
                                            wire:model.defer="extra_information" rows="3" 
                                            placeholder="Any special requests or notes for this booking..."></textarea>
                                        @error('extra_information')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Additional Services --}}
                            <div class="services-section mb-4">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-concierge-bell me-2"></i>Additional Services
                                </h6>
                                <div class="row">
                                    @foreach ($additionalServicesList as $service)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox"
                                                    wire:model.live="selectedServices"
                                                    value="{{ $service->id }}" class="form-check-input"
                                                    id="service-{{ $service->id }}">
                                                <label class="form-check-label"
                                                    for="service-{{ $service->id }}">
                                                    {{ $service->services }}
                                                    @if ($service->charges_type === 'percentage')
                                                        <span class="badge bg-info">{{ $service->charge_value }}%</span>
                                                    @else
                                                        <span class="badge bg-success">{{ number_format((float) $service->charge_value, 2) }} SAR</span>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Booking Price Summary --}}
                            <div class="alert alert-primary">
                                <div class="row">
                                    @php
                                        $servicesTotal = 0;
                                        if (!empty($selectedServices)) {
                                            foreach ($selectedServices as $serviceId) {
                                                $service = $additionalServicesList->where('id', $serviceId)->first();
                                                if ($service) {
                                                    if ($service->charges_type === 'percentage') {
                                                        $servicesTotal += (($price ?? 0) * $service->charge_value) / 100;
                                                    } else {
                                                        $servicesTotal += $service->charge_value;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    <div class="col-md-3">
                                        <strong>Base Price:</strong> {{ number_format($price, 2) }} SAR
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Services:</strong> {{ number_format((float) $servicesTotal, 2) }} SAR
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Discount:</strong> -{{ number_format($discountAmount, 2) }} SAR
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Subtotal:</strong> {{ number_format($totalAmount, 2) }} SAR
                                    </div>
                                </div>
                            </div>
                        </div>
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
                        <p class="text-muted mb-2">Total Booking Amount</p>
                        <h2 class="text-primary mb-0" style="font-size: 2.5rem; font-weight: bold;">
                            {{ number_format($totalAmount, 2) }} SAR
                        </h2>
                        <div class="mt-3 text-muted small">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Base Price:</strong> {{ number_format($price, 2) }} SAR
                                </div>
                                <div class="col-md-4">
                                    <strong>Services:</strong> {{ number_format((float) $servicesTotal, 2) }} SAR
                                </div>
                                <div class="col-md-4">
                                    <strong>Discount:</strong> -{{ number_format($discountAmount, 2) }} SAR
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <strong>Total Remaining:</strong>
                                    <span class="text-danger fs-5">
                                        @if ($payment_type === 'cash')
                                            {{ number_format(($totalAmount ?? 0) - ($received_payment ?? 0), 2) }} SAR
                                        @else
                                            {{ number_format($totalAmount, 2) }} SAR
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
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('booking.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="fas fa-save me-2"></i>Update Booking
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    Updating...
                                </span>
                            </button>
                        </div>
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

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            padding: 20px;
            color: white;
        }

        .page-title {
            color: white;
            margin: 0;
            font-weight: 600;
        }

        .alert-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
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
