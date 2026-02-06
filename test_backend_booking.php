<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Livewire\Admin\Booking\BookingCreate;

echo "Testing booking creation from backend...\n\n";

try {
    // Create an instance of the booking component
    $bookingComponent = new BookingCreate();
    
    // Set up test data similar to what was in the logs
    $bookingComponent->guest_name = 'Test User';
    $bookingComponent->guest_phone = '+9669892091234';
    $bookingComponent->guest_whatsapp = '+9669892091234';
    $bookingComponent->payment_type = 'credit';
    $bookingComponent->pickup_location_id = '2';
    $bookingComponent->pickup_location_name = 'Madina Hotel';
    $bookingComponent->pickup_location_type = 'Hotel';
    $bookingComponent->pickup_city_id = '2';
    $bookingComponent->pickup_hotel_name = 'Anwar Al Madinah';
    $bookingComponent->dropoff_location_id = '1';
    $bookingComponent->dropoff_location_name = 'Madina Hotel';
    $bookingComponent->dropoff_location_type = 'Hotel';
    $bookingComponent->dropoff_city_id = '5';
    $bookingComponent->dropoff_hotel_name = 'Dammam Sheraton';
    $bookingComponent->vehicle_id = '1';
    $bookingComponent->vehicle_name = 'Toyota Camry - LE 2023';
    $bookingComponent->price = '180.00';
    $bookingComponent->no_of_adults = '4';
    $bookingComponent->no_of_children = 0;
    $bookingComponent->no_of_infants = 0;
    $bookingComponent->total_passengers = 4;
    $bookingComponent->pickup_date = '2026-01-25';
    $bookingComponent->pickup_time = '09:00';
    $bookingComponent->airline_name = '';
    $bookingComponent->flight_number = '';
    $bookingComponent->arrival_departure_date = '';
    $bookingComponent->arrival_departure_time = '';
    $bookingComponent->flight_details = '';
    $bookingComponent->booking_status = 'pending';
    $bookingComponent->received_payment = '00.0';
    $bookingComponent->extra_information = '';
    $bookingComponent->selectedServices = [];
    $bookingComponent->totalAmount = 180;
    $bookingComponent->discountAmount = 0;
    $bookingComponent->same_as_contact = true;
    
    echo "Test data set up complete.\n";
    echo "Price: {$bookingComponent->price}\n";
    echo "Total Amount: {$bookingComponent->totalAmount}\n";
    echo "Pickup Location ID: {$bookingComponent->pickup_location_id}\n";
    echo "Dropoff Location ID: {$bookingComponent->dropoff_location_id}\n";
    echo "Vehicle ID: {$bookingComponent->vehicle_id}\n\n";
    
    // Try to save the booking
    echo "Attempting to save booking...\n";
    $bookingComponent->save();
    
    echo "Booking save method completed.\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
