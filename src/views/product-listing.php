            // Get cart data from session via a new endpoint
            fetch('cart/info.php?t=' + new Date().getTime(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            }) 