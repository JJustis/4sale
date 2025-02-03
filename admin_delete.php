<?php
// admin_delete.php
session_start();
require_once 'config.php';

// Check if already authenticated
if (!isset($_SESSION['admin_authenticated'])) {
    // Check for key submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['access_key'])) {
        $submitted_key = $_POST['access_key'];
        
        $stmt = $conn->prepare("SELECT access_key FROM admin_keys LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $stored_key = $result->fetch_assoc()['access_key'];
        
        if (password_verify($submitted_key, $stored_key)) {
            $_SESSION['admin_authenticated'] = true;
        } else {
            die("Invalid access key");
        }
    } else {
        // Show login form
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admin Access</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
        </head>
        <body class="bg-gray-100">
            <div class="container mx-auto px-4 py-8">
                <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow">
                    <h1 class="text-2xl font-bold mb-4">Admin Access</h1>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-gray-700">Access Key</label>
                            <input type="password" name="access_key" required class="w-full p-2 border rounded">
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-blue-500 text-white rounded">
                            Access Admin Panel
                        </button>
                    </form>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// If we get here, user is authenticated
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_sku'])) {
    $stmt = $conn->prepare("DELETE FROM items WHERE sku = ?");
    $stmt->bind_param("s", $_POST['delete_sku']);
    $stmt->execute();
    header("Location: admin_delete.php?message=Item+deleted");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Delete Listings</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-4">Delete Listings</h1>
            <?php if (isset($_GET['message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="space-y-4">
                <?php
                $stmt = $conn->prepare("SELECT * FROM items ORDER BY created_at DESC");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($item = $result->fetch_assoc()):
                ?>
                <div class="border p-4 rounded flex justify-between items-center">
                    <div>
                        <h3 class="font-bold"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="text-sm text-gray-600">SKU: <?php echo htmlspecialchars($item['sku']); ?></p>
                    </div>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
                        <input type="hidden" name="delete_sku" value="<?php echo htmlspecialchars($item['sku']); ?>">
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            Delete
                        </button>
                    </form>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>