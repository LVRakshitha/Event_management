<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "event_mgmt_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $eventDate = $_POST['eventDate'];  // Date field
    $eventChoice = $_POST['eventChoice'];
    $venue = $_POST['venue'];
    $people = $_POST['people'];
    $meals = isset($_POST['meals']) ? implode(", ", $_POST['meals']) : '';

    // Split the venue value to get the cost
    list($venueName, $venueCost) = explode(',', $venue);
    $venueCost = (int) $venueCost;

    // Calculate meal costs based on selected meals
    $mealCost = 0;
    if (strpos($meals, 'Breakfast') !== false) $mealCost += 150;
    if (strpos($meals, 'Lunch') !== false) $mealCost += 300;
    if (strpos($meals, 'Tea and Snacks') !== false) $mealCost += 100;
    if (strpos($meals, 'Dinner') !== false) $mealCost += 400;
    if (strpos($meals, 'Welcome Drink') !== false) $mealCost += 50;

    // Total meal cost
    $mealCostTotal = $mealCost * $people;

    // Total bill calculation
    $totalBill = $venueCost + $mealCostTotal;

    // Generate unique Event ID
    $eventID = 'EID' . uniqid();

    // Check if the date and venue are already booked
    $checkQuery = "SELECT * FROM registrations WHERE eventDate = '$eventDate' AND venue = '$venueName'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        // Venue is already booked
        echo "<div style='padding: 20px; background-color: #f44336; color: white; border-radius: 5px;'>
                <h2>Booking Conflict</h2>
                <p>The selected venue ($venueName) is already booked for the date $eventDate. Please choose another date or venue.</p>
              </div>";
    } else {
        // Insert the booking into the database
        $sql = "INSERT INTO registrations (eventID, name, phone, eventDate, eventChoice, venue, people, meals, totalBill)
                VALUES ('$eventID', '$name', '$phone', '$eventDate', '$eventChoice', '$venueName', '$people', '$meals', '$totalBill')";

        if ($conn->query($sql) === TRUE) {
            // Display the success message
            echo "<div style='padding: 20px; background-color: #4CAF50; color: white; border-radius: 5px;'>
                    <h2>Registration Successful!</h2>
                    <p>Thank you for registering. Your event details:</p>
                    <ul>
                        <li><strong>Name:</strong> $name</li>
                        <li><strong>Phone:</strong> $phone</li>
                        <li><strong>Event Date:</strong> $eventDate</li>
                        <li><strong>Event Choice:</strong> $eventChoice</li>
                        <li><strong>Venue:</strong> $venueName</li>
                        <li><strong>People Attending:</strong> $people</li>
                        <li><strong>Meals Selected:</strong> $meals</li>
                    </ul>
                    <h3>Total Bill: ₹" . number_format($totalBill, 2) . "</h3>
                  </div>";
        } else {
            echo "<div style='padding: 20px; background-color: #f44336; color: white; border-radius: 5px;'>
                    <h2>Error: Registration Failed</h2>
                    <p>Something went wrong. Please try again later.</p>
                  </div>";
        }
    }

    $conn->close();
}
?>
