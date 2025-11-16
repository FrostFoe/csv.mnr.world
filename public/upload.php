<?php
require_once '../includes/session_check.php';
require_once '../includes/config.php';

$message = '';
$error = '';
$api_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $description = $_POST['description'] ?? '';
        
        // Sanitize the filename
        $filename = basename($_FILES['csv_file']['name']);
        $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '', $filename);

        $file_tmp = $_FILES['csv_file']['tmp_name'];
        $file_size = $_FILES['csv_file']['size'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Validate file type
        if ($file_ext !== 'csv') {
            $error = 'Invalid file type. Only CSV files are allowed.';
        }
        // Validate file size
        elseif ($file_size > 5 * 1024 * 1024) { // 5MB
            $error = 'File size exceeds 5MB limit.';
        } else {
            // Process CSV line-by-line to conserve memory
            $json_array = [];
            $headers = [];
            $row_count = 0;
            if (($handle = fopen($file_tmp, "r")) !== FALSE) {
                // Get headers
                if (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $headers = $data;
                }
                // Get rows
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($headers) === count($data)) {
                        $json_array[] = array_combine($headers, $data);
                    }
                }
                fclose($handle);
                $row_count = count($json_array);
            }

            if ($row_count > 0) {
                $json_text = json_encode($json_array, JSON_PRETTY_PRINT);
                $size_kb = round($file_size / 1024, 2);

                // Store in database
                $stmt = $mysqli->prepare("INSERT INTO csv_files (filename, description, json_text, row_count, size_kb) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssid", $filename, $description, $json_text, $row_count, $size_kb);

                if ($stmt->execute()) {
                    $last_id = $stmt->insert_id;
                    $message = 'File uploaded and processed successfully!';
                    $domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
                    // Do not expose API key to the client
                    $api_url = "{$domain}/public/api/get.php?id={$last_id}";
                } else {
                    $error = 'Database error: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'CSV file is empty, invalid, or could not be processed.';
            }
        }
    } else {
        $error = 'No file uploaded or an error occurred during upload.';
    }
    $mysqli->close();
}

$page_title = 'Upload CSV';
require_once '../templates/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Upload CSV File</h2>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
                <hr>
                <p>API Scrape URL (remember to add your API key):</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($api_url); ?>" id="apiUrl">
                    <button class="btn btn-outline-secondary" type="button" id="copyButton">Copy URL</button>
                </div>
                <a href="index.php" class="btn btn-primary">View Dashboard</a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!$message): ?>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="csv_file" class="form-label">CSV File</label>
                <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                <div class="form-text">Max file size: 5MB. Allowed type: .csv</div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="A brief description of the file content..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Upload and Process File</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('copyButton')) {
        document.getElementById('copyButton').addEventListener('click', function() {
            var copyText = document.getElementById('apiUrl');
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(copyText.value).then(function() {
                alert('Copied the URL. Remember to append your API key!');
            }, function(err) {
                try {
                    document.execCommand('copy');
                    alert('Copied the URL. Remember to append your API key!');
                } catch (e) {
                    alert('Failed to copy the URL.');
                }
            });
        });
    }
});
</script>

<?php
require_once '../templates/footer.php';
?>

