<?php
// Initialize Firebase
require 'vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

// Your Firebase service account credentials
$serviceAccount = ServiceAccount::fromJsonFile('google-services.json');
$firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
$firestore = $firebase->createFirestore();

// Get all payments from Firestore
$payments = $firestore->collection('payments')->documents();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="payment.css" />
    <title>Payment Admin Panel</title>
</head>
<body>
    <h1>Payment Requests</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Therapist ID</th>
                <th>User ID</th>
                <th>Amount Paid</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Display all payments in the table
            foreach ($payments as $payment) {
                $paymentData = $payment->data();
                ?>
                <tr>
                    <td><?php echo $payment->id(); ?></td>
                    <td><?php echo $paymentData['therapistId']; ?></td>
                    <td><?php echo $paymentData['userId']; ?></td>
                    <td><?php echo $paymentData['amountPaid']; ?></td>
                    <td><?php echo $paymentData['status']; ?></td>
                    <td>
                        <form method="POST" action="updatePaymentStatus.php">
                            <input type="hidden" name="paymentId" value="<?php echo $payment->id(); ?>" />
                            <input type="hidden" name="adminId" value="admin123" /> <!-- Example Admin ID -->
                            <select name="status" required>
                                <option value="Accepted" <?php echo ($paymentData['status'] == 'Accepted') ? 'selected' : ''; ?>>Accepted</option>
                                <option value="Declined" <?php echo ($paymentData['status'] == 'Declined') ? 'selected' : ''; ?>>Declined</option>
                            </select>
                            <button type="submit">Update Status</button>
                        </form>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</body>
</html>
