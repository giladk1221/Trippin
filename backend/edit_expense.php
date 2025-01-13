<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

header('Content-Type: application/json');

// Database connection
include 'db.php';

// Function to get the last few lines of the error log
function getErrorLogContents($logFile, $lines = 10) {
    if (!file_exists($logFile)) {
        return "Error log file not found.";
    }
    $logContent = array_slice(file($logFile), -$lines);
    return implode("", $logContent);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log POST data for debugging
        error_log("POST Data: " . print_r($_POST, true));

        // Log FILES data for debugging
        if (!empty($_FILES)) {
            error_log("FILES Data: " . print_r($_FILES, true));
        } else {
            error_log("No files uploaded.");
        }

        // Validate required fields
        if (!isset($_POST['id'], $_POST['reason'], $_POST['amount'], $_POST['date'], $_POST['currency'])) {
            $errorLog = getErrorLogContents('php_errors.log');
            echo json_encode(['status' => 'error', 'error' => 'Missing required fields', 'debug' => $errorLog]);
            exit();
        }

        // Get text fields
        $expense_id = $_POST['id'];
        $reason = $_POST['reason'];
        $amount = $_POST['amount'];
        $date = $_POST['date'];
        $currency = $_POST['currency'];

        // Check for file upload
        $isFileUploaded = isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK;
        $receiptFile = null;

        if ($isFileUploaded) {
            // Handle file upload in two steps
            $fileTmpPath = $_FILES['receipt']['tmp_name'];
            error_log("Temporary File Path: " . $fileTmpPath);

            $receiptFileContent = file_get_contents($fileTmpPath);
            if ($receiptFileContent === false) {
                error_log("Failed to read file contents.");
                echo json_encode(['status' => 'error', 'error' => 'Failed to read the receipt file content.']);
                exit();
            } else {
                $receiptFile = $receiptFileContent;
                error_log("File content successfully read. Size: " . strlen($receiptFile));
            }
        }

        // Prepare the SQL query
        if ($isFileUploaded && $receiptFile !== null) {
            // Include the receipt file if a new file is uploaded
            $query = "UPDATE expense SET reason = ?, amount = ?, date = ?, currency = ?, receipt_file = ? WHERE id = ?";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                $stmt->bind_param("sdsssi", $reason, $amount, $date, $currency, $receiptFile, $expense_id);
            } else {
                throw new Exception("Failed to prepare SQL statement with receipt.");
            }
        } else {
            // Exclude the receipt file if no new file is uploaded
            $query = "UPDATE expense SET reason = ?, amount = ?, date = ?, currency = ? WHERE id = ?";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                $stmt->bind_param("sdssi", $reason, $amount, $date, $currency, $expense_id);
            } else {
                throw new Exception("Failed to prepare SQL statement without receipt.");
            }
        }

        // Execute query
        if ($stmt->execute()) {
            $errorLog = getErrorLogContents('php_errors.log');
            echo json_encode(['status' => 'success', 'message' => 'Expense updated successfully', 'debug' => $errorLog]);
        } else {
            throw new Exception("Database query failed: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        // Log the exception for debugging
        error_log("Exception: " . $e->getMessage());
        $errorLog = getErrorLogContents('php_errors.log');
        echo json_encode(['status' => 'error', 'error' => $e->getMessage(), 'debug' => $errorLog]);
    }
} else {
    $errorLog = getErrorLogContents('php_errors.log');
    echo json_encode(['status' => 'error', 'error' => 'Invalid request method', 'debug' => $errorLog]);
}

$conn->close();
?>