<?php
require_once '../includes/session_check.php';
require_once '../includes/config.php';

$search = $_GET['search'] ?? '';

$sql = "SELECT id, filename, description, row_count, size_kb, uploaded_at FROM csv_files";
if ($search) {
    $sql .= " WHERE filename LIKE ? OR description LIKE ?";
}
$sql .= " ORDER BY uploaded_at DESC";

$stmt = $mysqli->prepare($sql);
if ($search) {
    $search_param = "%{$search}%";
    $stmt->bind_param("ss", $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$mysqli->close();

$page_title = 'Admin Dashboard';
require_once '../templates/header.php';
?>
<h2>Uploaded Files</h2>
<p class="text-muted">A list of all processed CSV files.</p>

<form action="index.php" method="get" class="mb-4">
    <div class="input-group">
        <input type="text" class="form-control" placeholder="Search by filename or description..." name="search" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Filename</th>
                <th>Description</th>
                <th>Rows</th>
                <th>Size (KB)</th>
                <th>Uploaded At</th>
                <th>Scrape URL</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($files) > 0): ?>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?php echo $file['id']; ?></td>
                        <td><?php echo htmlspecialchars($file['filename']); ?></td>
                        <td><?php echo htmlspecialchars($file['description']); ?></td>
                        <td><?php echo $file['row_count']; ?></td>
                        <td><?php echo $file['size_kb']; ?></td>
                        <td><?php echo $file['uploaded_at']; ?></td>
                        <td>
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars("{$domain}/public/api/get.php?id={$file['id']}"); ?>" id="apiUrl-<?php echo $file['id']; ?>">
                                <button class="btn btn-outline-secondary copy-btn" type="button" data-target-id="<?php echo $file['id']; ?>">Copy</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No files found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<p class="text-muted small mt-2">
    <strong>Note:</strong> To use the API URL, copy it and append your API key to the end (e.g., <code>&key=...</code>).
</p>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target-id');
            var copyText = document.getElementById('apiUrl-' + targetId);
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(copyText.value).then(function() {
                alert('Copied the URL: ' + copyText.value);
            }, function(err) {
                // Fallback for older browsers
                try {
                    document.execCommand('copy');
                    alert('Copied the URL: ' + copyText.value);
                } catch (e) {
                    alert('Failed to copy the URL.');
                }
            });
        });
    });
});
</script>

<?php
require_once '../templates/footer.php';
?>

