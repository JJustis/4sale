<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Marketplace with PayPal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <h1 class="text-xl font-bold">Marketplace</h1>
            <div class="space-x-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button onclick="showModal('receiptModal')" class="px-4 py-2 bg-blue-500 text-white rounded">Receipts</button>
                    <button onclick="showModal('messageModal')" class="px-4 py-2 bg-green-500 text-white rounded">Messages</button>
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded">Logout</a>
                <?php else: ?>
                    <button onclick="showModal('loginModal')" class="px-4 py-2 bg-blue-500 text-white rounded">Login</button>
                    <button onclick="showModal('registerModal')" class="px-4 py-2 bg-green-500 text-white rounded">Register</button>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Existing modals remain the same -->
    
    <!-- New Receipt Modal -->
    <div id="receiptModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white p-8 rounded-lg w-3/4 max-h-[80vh] overflow-y-auto">
                <h2 class="text-2xl font-bold mb-4">Receipts & Messages</h2>
                
                <!-- Terminal-like interface -->
                <div class="bg-black text-green-400 p-4 rounded-lg font-mono mb-4">
                    <form id="terminalForm" onsubmit="handleTerminalCommand(event)">
                        <input type="text" id="terminalInput" 
                               placeholder="/msg username message" 
                               class="w-full bg-transparent border-none focus:outline-none text-green-400">
                    </form>
                </div>

                <!-- Receipts list -->
                <div id="receiptsList" class="space-y-4"></div>
                
                <button onclick="hideModal('receiptModal')" class="mt-4 text-gray-600">Close</button>
            </div>
        </div>
    </div>

    <!-- Messages Modal -->
    <div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white p-8 rounded-lg w-96">
                <h2 class="text-2xl font-bold mb-4">Messages</h2>
                <div id="messagesList" class="max-h-96 overflow-y-auto space-y-4"></div>
                <button onclick="hideModal('messageModal')" class="mt-4 text-gray-600">Close</button>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white p-8 rounded-lg w-96">
                <h2 class="text-2xl font-bold mb-4">Login</h2>
                <form id="loginForm" class="space-y-4">
                    <div>
                        <label class="block text-gray-700">Email</label>
                        <input type="email" name="email" required class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-gray-700">Password</label>
                        <input type="password" name="password" required class="w-full p-2 border rounded">
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-500 text-white rounded">Login</button>
                </form>
                <button onclick="hideModal('loginModal')" class="mt-4 text-gray-600">Close</button>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white p-8 rounded-lg w-96">
                <h2 class="text-2xl font-bold mb-4">Register</h2>
                <form id="registerForm" class="space-y-4">
                    <div>
                        <label class="block text-gray-700">Username</label>
                        <input type="text" name="username" required class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-gray-700">Email</label>
                        <input type="email" name="email" required class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-gray-700">Password</label>
                        <input type="password" name="password" required class="w-full p-2 border rounded">
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-green-500 text-white rounded">Register</button>
                </form>
                <button onclick="hideModal('registerModal')" class="mt-4 text-gray-600">Close</button>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Buy Panel -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold mb-4">Buy Items</h2>
                <div class="space-y-4">
                    <div class="flex space-x-2">
                        <input type="text" id="searchSku" placeholder="Enter SKU number" 
                               class="flex-1 p-2 border rounded">
                        <button onclick="searchItem()" 
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Search
                        </button>

                    </div>
                    <div id="searchResult" class="hidden border p-4 rounded">
                        <h3 id="itemTitle" class="font-bold"></h3>
                        <p id="itemDescription" class="text-gray-600"></p>
                        <p id="itemPrice" class="text-lg font-bold mt-2"></p>
                        
                        <!-- Traditional PayPal Form -->
                        <form id="paypalForm" action="https://www.paypal.com/cgi-bin/webscr" method="post" class="mt-4">
                            <input type="hidden" name="cmd" value="_xclick">
                            <input type="hidden" name="business" id="paypal_business">
                            <input type="hidden" name="item_name" id="paypal_item_name">
                            <input type="hidden" name="amount" id="paypal_amount">
                            <input type="hidden" name="currency_code" value="USD">
                            <input type="hidden" name="custom" id="paypal_sku">
                            <input type="hidden" name="return" value="http://jcmc.serveminecraft.net/4sale/success.php">
                            <input type="hidden" name="cancel_return" value="http://jcmc.serveminecraft.net/4sale/cancel.php">
                            <input type="hidden" name="notify_url" value="http://jcmc.serveminecraft.net/4sale/ipn_handler.php">
                            
                            <!-- Request shipping address -->
                            <input type="hidden" name="no_shipping" value="2">
                            
                            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" 
                                   name="submit" alt="PayPal - The safer, easier way to pay online!">
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sell Panel -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold mb-4">Sell Items</h2>
                <form id="sellForm" class="space-y-4">
                    <div>
                        <label class="block text-gray-700">Title</label>
                        <input type="text" name="title" required 
                               class="w-full p-2 border rounded">
                    </div>
					    <div>
        <label class="block text-gray-700">Item Image</label>
        <input type="file" name="image" accept="image/*" 
               class="w-full p-2 border rounded">
    </div>
                    <div>
                        <label class="block text-gray-700">Description</label>
                        <textarea name="description" class="w-full p-2 border rounded"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700">Price</label>
                        <input type="number" name="price" step="0.01" required 
                               class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-gray-700">SKU</label>
                        <input type="text" name="sku" required 
                               class="w-full p-2 border rounded">
                    </div>
					<div>
    <label class="block text-gray-700">Quantity Available</label>
    <input type="number" name="quantity" required min="1" 
           class="w-full p-2 border rounded">
</div>
                    <div>
                        <label class="block text-gray-700">PayPal Business Email</label>
                        <input type="email" name="paypal_email" required 
                               class="w-full p-2 border rounded">
                    </div>
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                        List Item
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Modal functions
        function showModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function hideModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

// Add this function to help with debugging
function handleFetchResponse(response) {
    return response.text().then(text => {
        try {
            // Try to parse the response as JSON
            const data = JSON.parse(text);
            return data;
        } catch (e) {
            // If parsing fails, throw an error with the raw response
            console.error('Raw server response:', text);
            throw new Error(`Failed to parse JSON. Raw response: ${text}`);
        }
    });
}

// Add this debug function
function logError(error, context) {
    console.error('Error Context:', context);
    console.error('Error Details:', error);
    
    if (error.response) {
        console.error('Response:', error.response);
    }
}
function updatePagination(currentPage, totalPages) {
    console.log(`Pagination Updated: Page ${currentPage} of ${totalPages}`);
}
// Update fetchListings function
function fetchListings() {
    fetch('get_listings.php')
        .then(response => response.json())
        .then(data => {
            console.log('Parsed data:', data);
            if (data.success) {
                displayListings(data.data.items);
                updatePagination(data.data.pagination.current_page, data.data.pagination.total_pages); // <== Check this line
            }
        })
        .catch(error => console.error('Fetch error:', error));
}


// Update registration handler
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    // Log the data being sent
    console.log('Sending registration data:', {
        username: formData.get('username'),
        email: formData.get('email')
    });

    fetch('register.php', {
        method: 'POST',
        body: formData
    })
    .then(handleFetchResponse)
    .then(data => {
        console.log('Registration response:', data);
        if (data.success) {
            alert(data.message);
            hideModal('registerModal');
            window.location.reload();
        } else {
            throw new Error(data.message || 'Registration failed');
        }
    })
    .catch(error => {
        logError(error, 'registration');
        alert('Registration failed: ' + error.message);
    });
});

// Similar update for login form
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(handleFetchResponse)
    .then(data => {
        if (data.success) {
            alert(data.message);
            hideModal('loginModal');
            window.location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Operation failed. Check console for details.');
    });
});

// Update the fetchListings function

document.getElementById('sellForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('add_item.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Item listed successfully! SKU: ${data.sku}`);
            this.reset();
            // Refresh listings if they're displayed on the same page
            if (typeof fetchListings === 'function') {
                fetchListings();
            }
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error listing item. Please try again.');
    });
});


function displayListings(items) {
    console.log('Displaying listings:', items);
    const listingsContainer = document.getElementById('listingsContainer');
    
    if (!listingsContainer) {
        console.error('Listings container not found!');
        return;
    }
    
    listingsContainer.innerHTML = '';

    if (!items || items.length === 0) {
        listingsContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No items available.</p>';
        return;
    }

    items.forEach(item => {
        const itemElement = document.createElement('div');
        itemElement.className = 'bg-white rounded-lg shadow-md p-4 mb-4';
        itemElement.innerHTML = `
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold">${escapeHtml(item.title || 'Untitled')}</h3>
                    <p class="text-gray-600">${escapeHtml(item.description || 'No description')}</p>
                    <p class="text-lg font-bold mt-2">$${parseFloat(item.price || 0).toFixed(2)}</p>
                    <p class="text-sm text-gray-500">Seller: ${escapeHtml(item.seller_username || 'Unknown')}</p>
                    <p class="text-sm text-gray-500">SKU: ${escapeHtml(item.sku || 'N/A')}</p>
                </div>
                <button onclick="searchItem('${escapeHtml(item.sku || '')}')" 
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Buy Now
                </button>
            </div>
        `;
        listingsContainer.appendChild(itemElement);
    });
}

// Helper function to prevent XSS
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Load listings when the page loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('Page loaded, fetching listings...');
    fetchListings();
});
    </script>
    <script>
	function handleTerminalCommand(event) {
        event.preventDefault();
        const input = document.getElementById('terminalInput').value;
        
        if (input.startsWith('/msg ')) {
            const parts = input.split(' ');
            const username = parts[1];
            const message = parts.slice(2).join(' ');
            
            sendMessage(username, message);
        }
        
        document.getElementById('terminalInput').value = '';
    }

    function sendMessage(username, message) {
        const formData = new FormData();
        formData.append('to_user', username);
        formData.append('message', message);
        
        fetch('send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Message sent!');
                loadMessages();
            } else {
                alert(data.message);
            }
        });
    }

    function loadReceipts() {
        fetch('get_receipts.php')
        .then(response => response.json())
        .then(data => {
            const receiptsList = document.getElementById('receiptsList');
            receiptsList.innerHTML = '';
            
            data.receipts.forEach(receipt => {
                const div = document.createElement('div');
                div.className = 'bg-white p-4 rounded shadow';
                div.innerHTML = `
                    <div class="flex justify-between">
                        <div>
                            <h3 class="font-bold">Order #${receipt.id}</h3>
                            <p>${receipt.title}</p>
                            <p>$${receipt.price} x ${receipt.quantity}</p>
                            <p class="text-sm text-gray-500">Ordered: ${receipt.created_at}</p>
                        </div>
                        <div>
                            <p class="text-sm">Tracking: ${receipt.tracking_number || 'Pending'}</p>
                        </div>
                    </div>
                `;
                receiptsList.appendChild(div);
            });
        });
    }

    function loadMessages() {
        fetch('get_messages.php')
        .then(response => response.json())
        .then(data => {
            const messagesList = document.getElementById('messagesList');
            messagesList.innerHTML = '';
            
            data.messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = 'bg-gray-100 p-4 rounded';
                div.innerHTML = `
                    <p class="font-bold">${msg.from_user}</p>
                    <p>${msg.message}</p>
                    <p class="text-sm text-gray-500">${msg.created_at}</p>
                `;
                messagesList.appendChild(div);
            });
        });
    }

    // Load messages and receipts when their modals are opened
    document.getElementById('receiptModal').addEventListener('show', loadReceipts);
    document.getElementById('messageModal').addEventListener('show', loadMessages);
function searchItem(sku) {
    if (!sku) {
        sku = document.getElementById('searchSku').value;
    }
    
    if (!sku) {
        alert('Please enter a SKU number');
        return;
    }

    console.log('Searching for SKU:', sku);

    const formData = new FormData();
    formData.append('sku', sku);

    fetch('search_item.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('Raw response:', text);
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse JSON:', text);
            throw new Error('Invalid server response');
        }
    })
     .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Items not found');
        }

        const searchResult = document.getElementById('searchResult');
        searchResult.classList.remove('hidden');
        searchResult.innerHTML = ''; // Clear previous results
        
        data.results.forEach((item, index) => {
            const price = (Math.round(parseFloat(item.price) * 100) / 100).toFixed(2);
            
            const resultDiv = document.createElement('div');
            resultDiv.className = 'mb-4 p-4 border rounded';
            
            // Add image display with fallback
            const imageHtml = item.image 
                ? `<img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.title)}" 
                     class="w-full h-48 object-cover rounded mb-4">`
                : '<div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500 rounded mb-4">No Image</div>';
            
            resultDiv.innerHTML = `
                ${imageHtml}
    <h3 class="font-bold text-lg">${escapeHtml(item.title)}</h3>
    <p class="text-gray-600">${escapeHtml(item.description)}</p>
    <p class="text-lg font-bold mt-2">$${price}</p>
    <p class="text-sm text-gray-500">SKU: ${escapeHtml(item.sku)}</p>
    <p class="text-sm text-gray-500">Available: ${escapeHtml(item.quantity)}</p>
    
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" class="mt-4">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="${escapeHtml(item.paypal_email)}">
    <input type="hidden" name="item_name" value="${escapeHtml(item.title)}">
    <input type="hidden" name="amount" value="${price}">
    <input type="hidden" name="currency_code" value="USD">
    <input type="hidden" name="item_number" value="${escapeHtml(item.sku)}">
    <input type="hidden" name="custom" value="${escapeHtml(item.sku)}">
    
    <!-- Request shipping address -->
    <input type="hidden" name="no_shipping" value="2">
    <input type="hidden" name="address_override" value="0">
    
    <!-- Return and IPN URLs -->
    <input type="hidden" name="return" value="http://jcmc.serveminecraft.net/4sale/success.php">
    <input type="hidden" name="cancel_return" value="http://jcmc.serveminecraft.net/4sale/cancel.php">
    <input type="hidden" name="notify_url" value="http://jcmc.serveminecraft.net/4sale/ipn_handler.php">
        
        <!-- Quantity selector -->
        <div class="flex items-center gap-4 mb-4">
            <label class="text-sm">Quantity:</label>
            <select name="quantity" class="w-20 p-2 border rounded">
                ${Array.from({length: Math.min(item.quantity, 10)}, (_, i) => i + 1)
                    .map(num => `<option value="${num}">${num}</option>`)
                    .join('')}
            </select>
        </div>
        
        <button class="btn"><input type="image" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-medium.png
" 
               border="0" name="submit" 
               alt="PayPal - The safer, easier way to pay online!"></button>
    </form>
`;
            
            searchResult.appendChild(resultDiv);
        });
    })
    .catch(error => {
        console.error('Search error:', error);
        alert(error.message || 'Error searching for item');
        
        const searchResult = document.getElementById('searchResult');
        searchResult.classList.add('hidden');
    });
}
    </script>
</body>
</html>