<?php

namespace App\Livewire\Admin\Booking;

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Bookings;
use App\Models\CarDetails;
use App\Models\PickUpLocation;
use App\Models\DropLocation;
use App\Models\Hotel;
use App\Models\City;
use App\Models\RouteFares;
use App\Models\AdditionalService;
use App\Models\Airline;
use App\Models\VisaType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingCreate extends Component
{
    // ========================================
    // CUSTOMER INFORMATION
    // ========================================
    #[Validate('required|string|max:255')]
    public $guest_name = '';

    #[Validate('required|string|max:20|regex:/^[\+0-9\(\)\s]+$/')]
    public $guest_phone = '';

    #[Validate('nullable|string|max:20|regex:/^[\+0-9\(\)\s]+$/')]
    public $guest_whatsapp = '';

    public $same_as_contact = false;

    public $visa_type = '';

    #[Validate('required|in:credit,cash')]
    public $payment_type = 'credit';

    public $total_received_payment = 0;

    // ========================================
    // MULTIPLE BOOKINGS ARRAY
    // ========================================
    public $bookings = [];
    public $bookingCount = 1;
    public $nextSerialNumber = 1;

    // ========================================
    // ADDITIONAL INFORMATION
    // ========================================
    #[Validate('nullable|string|max:2000')]
    public $extra_information = '';

    // ========================================
    // ADDITIONAL SERVICES & PRICING
    // ========================================
    public $additionalServicesList = [];
    public $selectedServices = [];
    public $totalAmount = 0;
    public $airlines = [];
    public $visaTypes = [];
    
    #[Validate('nullable|numeric|min:0')]
    public $discountAmount = 0;

    // ========================================
    // DYNAMIC FILTERING ARRAYS
    // ========================================
    public $availableDropoffs = [];
    public $availableVehicles = [];
    public $availableHotels = [];
    public $availableDropoffHotels = [];

    /**
     * ========================================
     * LIFECYCLE HOOKS
     * ========================================
     */

    public function mount()
    {
        $this->initializeBookings();
        $this->loadAdditionalServices();
        $this->loadAirlines();
        $this->loadVisaTypes();
        $this->total_received_payment = 0; // Reset total received payment
    }

    /**
     * Initialize bookings array with default values
     */
    private function initializeBookings()
    {
        $this->nextSerialNumber = $this->getNextSerialSeed();
        $this->bookings = [$this->buildBookingRow()];
    }

    /**
     * Load additional services from database
     */
    private function loadAdditionalServices()
    {
        $this->additionalServicesList = AdditionalService::where('status', 1)
            ->orderBy('services')
            ->get();
    }

    /**
     * Load visa types from database
     */
    private function loadVisaTypes()
    {
        $this->visaTypes = VisaType::orderBy('name')->get();
    }

    /**
     * Load airlines from database
     */
    private function loadAirlines()
    {
        $this->airlines = Airline::active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get airline name by ID
     */
    private function getAirlineName($airlineId)
    {
        if (!$airlineId) {
            return null;
        }
        
        foreach ($this->airlines as $airline) {
            if ($airline->id == $airlineId) {
                return $airline->name;
            }
        }
        
        return null;
    }

    /**
     * ========================================
     * CUSTOMER INFORMATION METHODS
     * ========================================
     */

    /**
     * Toggle WhatsApp same as contact number
     */
    public function updatedSameAsContact()
    {
        if ($this->same_as_contact) {
            $this->guest_whatsapp = $this->guest_phone;
        } else {
            $this->guest_whatsapp = '';
        }
    }

    /**
     * Update WhatsApp when phone changes if same_as_contact is checked
     */
    public function updatedGuestPhone()
    {
        if ($this->same_as_contact) {
            $this->guest_whatsapp = $this->guest_phone;
        }
    }

    /**
     * ========================================
     * BOOKING MANAGEMENT
     * ========================================
     */

    /**
     * Add new booking to the array
     */
    public function addBooking()
    {
        $this->bookings[] = $this->buildBookingRow();

        $this->bookingCount = count($this->bookings);
    }

    /**
     * Remove booking from array
     */
    public function removeBooking($index)
    {
        if (count($this->bookings) > 1) {
            unset($this->bookings[$index]);
            $this->bookings = array_values($this->bookings);
            $this->bookingCount = count($this->bookings);
            $this->calculateTotalAmount();
        }
    }

    /**
     * ========================================
     * PICKUP LOCATION METHODS
     * ========================================
     */

    /**
     * Updated pickup location for specific booking
     */
    public function updatedBookings($value, $name)
    {
        $parts = explode('.', $name);

        if (count($parts) < 2) {
            return;
        }

        $index = (int) $parts[0];
        $field = $parts[1];

        // Handle different field updates
        if ($field === 'pickup_location_id') {
            $this->handlePickupLocationChange($index, $value);
        } elseif ($field === 'dropoff_location_id') {
            $this->handleDropoffLocationChange($index, $value);
        } elseif ($field === 'vehicle_id') {
            $this->handleVehicleChange($index, $value);
        } elseif (in_array($field, ['no_of_adults', 'no_of_children'])) {
            $this->calculateBookingPassengers($index);
        } elseif ($field === 'selected_services') { // Add this
            $this->calculateTotalAmount();
        }
    }

    /**
     * Handle pickup location change
     */
    private function handlePickupLocationChange($index, $pickupLocationId)
    {
        if (!$pickupLocationId) {
            $this->resetPickupData($index);
            return;
        }

        $pickupLocation = PickUpLocation::find($pickupLocationId);

        if (!$pickupLocation) {
            $this->addError("bookings.{$index}.pickup_location_id", 'Invalid pickup location selected.');
            $this->resetPickupData($index);
            return;
        }

        // Update pickup location details
        $this->bookings[$index]['pickup_location_name'] = $pickupLocation->pickup_location;
        $this->bookings[$index]['pickup_location_type'] = $pickupLocation->type;

        // Clear hotel name if not hotel type
        if ($pickupLocation->type !== 'Hotel') {
            $this->bookings[$index]['pickup_hotel_name'] = '';
            $this->bookings[$index]['available_hotels'] = [];
        } else {
            $this->loadHotelsForPickupCity($index, $pickupLocation);
        }

        // Clear flight info if not airport
        if ($pickupLocation->type !== 'Airport') {
            $this->clearFlightInfo($index);
        }

        // Filter available dropoffs
        $this->filterAvailableDropoffs($index, $pickupLocationId);

        // Reset dependent fields
        $this->resetDropoffAndVehicleData($index);
    }

    /**
     * Load hotels for pickup city
     */
    private function loadHotelsForPickupCity($index, $pickupLocation)
    {
        if (!$pickupLocation->city_id) {
            $this->bookings[$index]['available_hotels'] = [];
            return;
        }

        $this->bookings[$index]['available_hotels'] = Hotel::where('city_id', $pickupLocation->city_id)
            ->where('status', 1)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Filter available dropoffs based on pickup
     */
    private function filterAvailableDropoffs($index, $pickupLocationId)
    {
        $this->bookings[$index]['available_dropoffs'] = RouteFares::where('status', 'active')
            ->where('pickup_id', $pickupLocationId)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->pluck('dropoff_id')
            ->unique()
            ->toArray();
    }

    /**
     * Reset pickup data for specific booking
     */
    private function resetPickupData($index)
    {
        $this->bookings[$index]['pickup_location_name'] = '';
        $this->bookings[$index]['pickup_location_type'] = '';
        $this->bookings[$index]['pickup_hotel_name'] = '';
        $this->bookings[$index]['available_hotels'] = [];
        $this->bookings[$index]['available_dropoffs'] = [];
        $this->clearFlightInfo($index);
        $this->resetDropoffAndVehicleData($index);
    }

    /**
     * ========================================
     * DROPOFF LOCATION METHODS
     * ========================================
     */

    /**
     * Handle dropoff location change
     */
    private function handleDropoffLocationChange($index, $dropoffLocationId)
    {
        if (!$dropoffLocationId) {
            $this->resetDropoffData($index);
            return;
        }

        $dropoffLocation = DropLocation::find($dropoffLocationId);

        if (!$dropoffLocation) {
            $this->addError("bookings.{$index}.dropoff_location_id", 'Invalid dropoff location selected.');
            $this->resetDropoffData($index);
            return;
        }

        // Update dropoff location details
        $this->bookings[$index]['dropoff_location_name'] = $dropoffLocation->drop_off_location;
        $this->bookings[$index]['dropoff_location_type'] = $dropoffLocation->type;

        // Clear hotel name if not hotel type
        if ($dropoffLocation->type !== 'Hotel') {
            $this->bookings[$index]['dropoff_hotel_name'] = '';
            $this->bookings[$index]['available_dropoff_hotels'] = [];
        } else {
            $this->loadHotelsForDropoffCity($index, $dropoffLocation);
        }

        // Filter available vehicles
        $this->filterAvailableVehicles($index);

        // Reset vehicle and price
        $this->resetVehicleData($index);
    }

    /**
     * Load hotels for dropoff city
     */
    private function loadHotelsForDropoffCity($index, $dropoffLocation)
    {
        if (!$dropoffLocation->city_id) {
            $this->bookings[$index]['available_dropoff_hotels'] = [];
            return;
        }

        $this->bookings[$index]['available_dropoff_hotels'] = Hotel::where('city_id', $dropoffLocation->city_id)
            ->where('status', 1)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Filter available vehicles based on pickup and dropoff
     */
    private function filterAvailableVehicles($index)
    {
        $pickupId = $this->bookings[$index]['pickup_location_id'];
        $dropoffId = $this->bookings[$index]['dropoff_location_id'];

        if (!$pickupId || !$dropoffId) {
            $this->bookings[$index]['available_vehicles'] = [];
            return;
        }

        $this->bookings[$index]['available_vehicles'] = RouteFares::where('status', 'active')
            ->where('pickup_id', $pickupId)
            ->where('dropoff_id', $dropoffId)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->pluck('vehicle_id')
            ->unique()
            ->toArray();
    }

    /**
     * Reset dropoff data
     */
    private function resetDropoffData($index)
    {
        $this->bookings[$index]['dropoff_location_name'] = '';
        $this->bookings[$index]['dropoff_location_type'] = '';
        $this->bookings[$index]['dropoff_hotel_name'] = '';
        $this->bookings[$index]['available_dropoff_hotels'] = [];
        $this->bookings[$index]['available_vehicles'] = [];
    }

    /**
     * Reset dropoff and vehicle data
     */
    private function resetDropoffAndVehicleData($index)
    {
        $this->resetDropoffData($index);
        $this->resetVehicleData($index);
    }

    /**
     * ========================================
     * VEHICLE METHODS
     * ========================================
     */

    /**
     * Handle vehicle change
     */
    private function handleVehicleChange($index, $vehicleId)
    {
        if (!$vehicleId) {
            $this->resetVehicleData($index);
            return;
        }

        $vehicle = CarDetails::find($vehicleId);

        if (!$vehicle) {
            $this->addError("bookings.{$index}.vehicle_id", 'Invalid vehicle selected.');
            $this->resetVehicleData($index);
            return;
        }

        $this->bookings[$index]['vehicle_name'] = "{$vehicle->name} - {$vehicle->model_variant}";

        // Validate passenger capacity
        if (!$this->validatePassengerCapacity($index, $vehicle->seating_capacity)) {
            return;
        }

        // Calculate price
        $this->calculateBookingPrice($index);
    }

    /**
     * Calculate price for specific booking
     */
    private function calculateBookingPrice($index)
    {
        $pickupId = $this->bookings[$index]['pickup_location_id'];
        $dropoffId = $this->bookings[$index]['dropoff_location_id'];
        $vehicleId = $this->bookings[$index]['vehicle_id'];

        if (!$pickupId || !$dropoffId || !$vehicleId) {
            $this->bookings[$index]['price'] = 0;
            $this->calculateTotalAmount();
            return;
        }

        $routeFare = RouteFares::where('pickup_id', $pickupId)
            ->where('dropoff_id', $dropoffId)
            ->where('vehicle_id', $vehicleId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();

        if (!$routeFare) {
            $this->bookings[$index]['price'] = 0;
            $this->addError("bookings.{$index}.price", 'No fare available for this route and vehicle combination.');
            $this->calculateTotalAmount();
            return;
        }

        $this->bookings[$index]['price'] = $routeFare->amount;
        $this->resetErrorBag("bookings.{$index}.price");
        $this->calculateTotalAmount();
    }

    /**
     * Reset vehicle data
     */
    private function resetVehicleData($index)
    {
        $this->bookings[$index]['vehicle_id'] = '';
        $this->bookings[$index]['vehicle_name'] = '';
        $this->bookings[$index]['price'] = 0;
        $this->calculateTotalAmount();
    }

    /**
     * ========================================
     * PASSENGER METHODS
     * ========================================
     */

    /**
     * Calculate total passengers for specific booking
     */
    private function calculateBookingPassengers($index)
    {
        $adults = (int) ($this->bookings[$index]['no_of_adults'] ?? 1);
        $children = (int) ($this->bookings[$index]['no_of_children'] ?? 0);
        // Infants field removed - only adults + children count

        $this->bookings[$index]['total_passengers'] = $adults + $children;

        // Validate if vehicle is selected
        if (!empty($this->bookings[$index]['vehicle_id'])) {
            $vehicle = CarDetails::find($this->bookings[$index]['vehicle_id']);
            if ($vehicle) {
                $this->validatePassengerCapacity($index, $vehicle->seating_capacity);
            }
        }
    }

    /**
     * Validate passenger capacity
     */
    private function validatePassengerCapacity($index, $vehicleCapacity)
    {
        $totalPassengers = $this->bookings[$index]['total_passengers'];

        $this->resetErrorBag([
            "bookings.{$index}.no_of_adults",
            "bookings.{$index}.no_of_children",
            "bookings.{$index}.no_of_infants"
        ]);

        if ($totalPassengers > $vehicleCapacity) {
            $this->addError(
                "bookings.{$index}.no_of_adults",
                "Total passengers ({$totalPassengers}) exceed vehicle capacity ({$vehicleCapacity})."
            );
            return false;
        }

        if ($totalPassengers < 1) {
            $this->addError("bookings.{$index}.no_of_adults", 'At least one passenger is required.');
            return false;
        }

        return true;
    }

    /**
     * ========================================
     * FLIGHT INFORMATION METHODS
     * ========================================
     */

    /**
     * Clear flight information for specific booking
     */
    private function clearFlightInfo($index)
    {
        $this->bookings[$index]['airline_name'] = '';
        $this->bookings[$index]['flight_number'] = '';
        $this->bookings[$index]['flight_details'] = '';
        $this->bookings[$index]['arrival_departure_date'] = '';
        $this->bookings[$index]['arrival_departure_time'] = '';
    }

    /**
     * ========================================
     * PRICING AND CALCULATION METHODS
     * ========================================
     */

    /**
     * Watch for selected services changes
     */
    public function updatedSelectedServices()
    {
        $this->calculateTotalAmount();
    }

    /**
     * Watch for discount changes
     */
    public function updatedDiscountAmount()
    {
        $this->calculateTotalAmount();
    }

    /**
     * Calculate total amount including all bookings, services, and discounts
     */
    public function calculateTotalAmount()
    {
        $grandTotal = 0;
        
        foreach ($this->bookings as $booking) {
            $bookingPrice = (float) ($booking['price'] ?? 0);
            $bookingDiscount = (float) ($booking['discount_amount'] ?? 0);
            
            // Calculate services for THIS booking
            $bookingServicesTotal = 0;
            if (isset($booking['selected_services']) && is_array($booking['selected_services'])) {
                foreach ($booking['selected_services'] as $serviceId) {
                    $service = $this->additionalServicesList->firstWhere('id', $serviceId);
                    if ($service) {
                        $bookingServicesTotal += $this->calculateServiceCharge($service, $bookingPrice);
                    }
                }
            }
            
            // Add to grand total: (price - discount) + services
            $grandTotal += ($bookingPrice - $bookingDiscount + $bookingServicesTotal);
        }
        
        $this->totalAmount = max(0, $grandTotal);
    }

    /**
     * Calculate individual service charge
     */
    private function calculateServiceCharge($service, $basePrice)
    {
        if ($service->charges_type === 'percentage') {
            return ($basePrice * $service->charge_value / 100);
        }
        return $service->charge_value;
    }

    /**
     * ========================================
     * SAVE BOOKING METHODS
     * ========================================
     */

    /**
     * Save all bookings
     */
    public function saveBooking()
    {
        Log::info('Booking save started', ['bookings_count' => count($this->bookings)]);

        try {
            // Validate customer information
            $this->validate([
                'guest_name' => 'required|string|max:255',
                'guest_phone' => 'required|string|max:20|regex:/^[\+0-9\(\)\s]+$/',
                'guest_whatsapp' => 'nullable|string|max:20|regex:/^[\+0-9\(\)\s]+$/',
                'payment_type' => 'required|in:credit,cash',
                'total_received_payment' => 'required_if:payment_type,cash|nullable|numeric|min:0',
            ]);

            // Validate each booking
            foreach ($this->bookings as $index => $booking) {
                $this->validateBooking($index);
            }

            // Additional validations
            if (!$this->additionalValidations()) {
                return;
            }

            DB::beginTransaction();

            // Create all bookings
            $createdBookings = [];
            foreach ($this->bookings as $index => $bookingData) {
                // Distribute total received payment proportionally
                if ($this->payment_type === 'cash' && $this->total_received_payment > 0) {
                    $totalBookingAmount = array_sum(array_column($this->bookings, 'price'));
                    if ($totalBookingAmount > 0) {
                        $proportion = ($bookingData['price'] / $totalBookingAmount);
                        $bookingData['received_payment'] = $this->total_received_payment * $proportion;
                    }
                }
                
                $booking = $this->createSingleBooking($bookingData);
                $createdBookings[] = $booking;
                
                // Pass bookingData instead of just price
                $this->attachAdditionalServices($booking, $bookingData['price'], $bookingData);
            }

            DB::commit();

            // Reset form after successful save
            $this->total_received_payment = 0;

            session()->flash('message', 'Booking(s) created successfully!');
            return redirect()->route('booking.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            session()->flash('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Failed to create booking. Please try again.');
        }
    }

    /**
     * Validate individual booking
     */
    private function validateBooking($index)
    {
        $booking = $this->bookings[$index];

        $rules = [
            "bookings.{$index}.pickup_location_id" => 'required|exists:pick_up_locations,id',
            "bookings.{$index}.dropoff_location_id" => 'required|exists:drop_locations,id',
            "bookings.{$index}.vehicle_id" => 'required|exists:car_details,id',
            "bookings.{$index}.price" => 'required|numeric|min:0',
            "bookings.{$index}.no_of_adults" => 'required|integer|min:1|max:50',
            "bookings.{$index}.no_of_children" => 'required|integer|min:0|max:50',
            "bookings.{$index}.no_of_infants" => 'required|integer|min:0|max:50',
            "bookings.{$index}.pickup_date" => 'required|date|after_or_equal:today',
            "bookings.{$index}.pickup_time" => 'required|date_format:H:i',
            "bookings.{$index}.booking_status" => 'required|in:pending,pickup,dropoff,hold,cancelled,complete',
            "bookings.{$index}.received_payment" => 'nullable|numeric|min:0',
            "bookings.{$index}.discount_amount" => 'nullable|numeric|min:0',
            "bookings.{$index}.extra_information" => 'nullable|string|max:2000',
        ];

        // Add conditional validations
        if ($booking['pickup_location_type'] === 'Hotel') {
            $rules["bookings.{$index}.pickup_hotel_name"] = 'required|string|max:255';
        }

        if ($booking['dropoff_location_type'] === 'Hotel') {
            $rules["bookings.{$index}.dropoff_hotel_name"] = 'required|string|max:255';
        }

        $this->validate($rules);
    }

    /**
     * Additional validations before save
     */
    private function additionalValidations()
    {
        // Validate at least one booking
        if (empty($this->bookings)) {
            $this->addError('bookings', 'At least one booking is required.');
            return false;
        }

        // Validate total amount
        if ($this->totalAmount <= 0) {
            $this->addError('totalAmount', 'Total amount must be greater than zero.');
            return false;
        }

        // Validate each booking's received payment and discount
        foreach ($this->bookings as $index => $booking) {
            $price = (float) ($booking['price'] ?? 0);
            $discount = (float) ($booking['discount_amount'] ?? 0);
            $received = (float) ($booking['received_payment'] ?? 0);

            // Validate discount doesn't exceed price
            if ($discount > $price) {
                $this->addError("bookings.{$index}.discount_amount", 'Discount cannot exceed booking price.');
                return false;
            }

            // Validate received payment doesn't exceed net price
            $netPrice = $price - $discount;
            if ($received > $netPrice) {
                $this->addError("bookings.{$index}.received_payment", 'Received payment cannot exceed net price.');
                return false;
            }
        }

        return true;
    }

    /**
     * Create single booking record
     */
    private function createSingleBooking($bookingData)
    {
        // Format phone numbers
        $guestPhone = $this->formatPhoneNumber($this->guest_phone);
        $guestWhatsapp = $this->guest_whatsapp
            ? $this->formatPhoneNumber($this->guest_whatsapp)
            : $guestPhone;

        // Calculate net price (price - discount)
        $netPrice = ($bookingData['price'] ?? 0) - ($bookingData['discount_amount'] ?? 0);

        // Debug: Check visa_type value
        Log::info('Visa Type Debug', [
            'visa_type_value' => $this->visa_type,
            'visa_type_empty' => empty($this->visa_type),
            'visa_type_type' => gettype($this->visa_type)
        ]);

        $data = [
            'guest_name' => trim($this->guest_name),
            'guest_phone' => $guestPhone,
            'guest_whatsapp' => $guestWhatsapp,
            'payment_type' => $this->payment_type,
            'visa_type' => $this->visa_type ?: null,
            'booking_no' => $bookingData['booking_no'] ?? Bookings::generateSerial(),
            'airline_id' => $bookingData['airline_id'] ?: null,
            'airline_name' => $this->getAirlineName($bookingData['airline_id'] ?? null),
            'pickup_location_id' => $bookingData['pickup_location_id'],
            'pickup_location_name' => $bookingData['pickup_location_name'],
            'pickup_hotel_name' => $bookingData['pickup_hotel_name'] ?: null,
            'dropoff_location_id' => $bookingData['dropoff_location_id'],
            'dropoff_location_name' => $bookingData['dropoff_location_name'],
            'dropoff_hotel_name' => $bookingData['dropoff_hotel_name'] ?: null,
            'vehicle_id' => $bookingData['vehicle_id'],
            'vehicle_name' => $bookingData['vehicle_name'],
            'no_of_adults' => $bookingData['no_of_adults'],
            'no_of_children' => $bookingData['no_of_children'],
            'no_of_infants' => $bookingData['no_of_infants'],
            'total_passengers' => $bookingData['total_passengers'],
            'pickup_date' => $bookingData['pickup_date'],
            'pickup_time' => $bookingData['pickup_time'],
            'flight_number' => $bookingData['flight_number'] ?: null,
            'flight_details' => $bookingData['flight_details'] ?: null,
            'arrival_departure_date' => $bookingData['arrival_departure_date'] ?: null,
            'arrival_departure_time' => $bookingData['arrival_departure_time'] ?: null,
            'booking_status' => $bookingData['booking_status'] ?? 'pending',
            'price' => $bookingData['price'],
            'total_amount' => $netPrice,
            'discount_amount' => $bookingData['discount_amount'] ?? 0,
            'recived_paymnet' => $bookingData['received_payment'] ?? 0,
            'extra_information' => $bookingData['extra_information'] ?: null,
        ];

        return Bookings::create($data);
    }

    /**
     * Build a new booking row with a unique serial for the form
     */
    private function buildBookingRow()
    {
        if (empty($this->nextSerialNumber) || $this->nextSerialNumber < 1) {
            $this->nextSerialNumber = $this->getNextSerialSeed();
        }

        $serial = $this->formatBookingSerial($this->nextSerialNumber);
        $this->nextSerialNumber++;

        return [
            'booking_no' => $serial,
            'pickup_location_id' => '',
            'pickup_location_name' => '',
            'pickup_location_type' => '',
            'pickup_hotel_name' => '',
            'dropoff_location_id' => '',
            'dropoff_location_name' => '',
            'dropoff_location_type' => '',
            'dropoff_hotel_name' => '',
            'vehicle_id' => '',
            'vehicle_name' => '',
            'price' => 0,
            'no_of_adults' => 1,
            'no_of_children' => 0,
            'no_of_infants' => 0, // Field hidden but kept for compatibility
            'total_passengers' => 1,
            'pickup_date' => now()->addDay()->format('Y-m-d'),
            'pickup_time' => '09:00',
            'airline_id' => '',
            'flight_number' => '',
            'arrival_departure_date' => '',
            'arrival_departure_time' => '',
            'flight_details' => '',
            'booking_status' => 'pending',
            'received_payment' => 0,
            'discount_amount' => 0,
            'extra_information' => '',
            'selected_services' => [],
            'available_dropoffs' => [],
            'available_vehicles' => [],
            'available_hotels' => [],
            'available_dropoff_hotels' => [],
        ];
    }

    /**
     * Get the next serial seed from the database
     */
    private function getNextSerialSeed()
    {
        $lastId = Bookings::max('id');
        return $lastId ? $lastId + 1 : 1;
    }

    /**
     * Format booking serial number
     */
    private function formatBookingSerial($number)
    {
        return 'BK-' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }

   // Update attachAdditionalServices to use per-booking services
    private function attachAdditionalServices($booking, $bookingPrice, $bookingData)
    {
        if (empty($bookingData['selected_services'])) {
            return;
        }

        $servicesData = [];
        
        foreach ($bookingData['selected_services'] as $serviceId) {
            $service = $this->additionalServicesList->firstWhere('id', $serviceId);
            if ($service) {
                $amount = $this->calculateServiceCharge($service, $bookingPrice);
                $servicesData[$serviceId] = ['amount' => $amount];
            }
        }

        if (!empty($servicesData)) {
            $booking->additionalServices()->attach($servicesData);
        }
    }

    /**
     * Format phone number (ensure it has + prefix)
     */
    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return strpos($phone, '+') === 0 ? $phone : '+' . $phone;
    }

    /**
     * Cancel and redirect back
     */
    public function cancel()
    {
        return redirect()->route('booking.index');
    }

    /**
     * ========================================
     * RENDER METHOD
     * ========================================
     */

    public function render()
    {
        // Get active route fares
        $routeFares = RouteFares::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->with(['pickupLocation', 'dropoffLocation', 'vehicle'])
            ->get();

        // Get unique IDs from route fares
        $pickupLocationIds = $routeFares->pluck('pickup_id')->unique();
        $dropoffLocationIds = $routeFares->pluck('dropoff_id')->unique();
        $vehicleIds = $routeFares->pluck('vehicle_id')->unique();

        // Get filtered data
        $pickupLocations = PickUpLocation::whereIn('id', $pickupLocationIds)
            ->where('status', 'active')
            ->orderBy('pickup_location')
            ->get();

        $dropoffLocations = DropLocation::whereIn('id', $dropoffLocationIds)
            ->where('status', 'active')
            ->orderBy('drop_off_location')
            ->get();

        $vehicles = CarDetails::whereIn('id', $vehicleIds)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.booking.booking-create', [
            'routeFares' => $routeFares,
            'pickupLocations' => $pickupLocations,
            'dropoffLocations' => $dropoffLocations,
            'vehicles' => $vehicles,
        ]);
    }
}
