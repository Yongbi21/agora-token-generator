<?php
require 'vendor/autoload.php'; // Autoload Firebase SDK

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

// Initialize Firebase
$serviceAccount = ServiceAccount::fromJsonFile('google-services.json'); 
$firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
$firestore = $firebase->createFirestore();

// Get data from the POST request
$paymentId = $_POST['paymentId'];  // Payment ID from request
$status = $_POST['status'];        // 'Accepted' or 'Declined'
$adminId = $_POST['adminId'];      // Admin ID from request for validation

// Validate the status input
if (!in_array($status, ['Accepted', 'Declined'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Reference to the Firestore payments collection
$paymentsCollection = $firestore->collection('payments');

// Find the payment document by paymentId and update the status
$paymentRef = $paymentsCollection->document($paymentId);

try {
    $paymentRef->update([
        ['path' => 'status', 'value' => $status] // Update status field
    ]);
    echo json_encode(['success' => true, 'message' => 'Payment status updated']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating status: ' . $e->getMessage()]);
}
?>

